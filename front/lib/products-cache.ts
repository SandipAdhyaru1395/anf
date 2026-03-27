import api from "@/lib/axios"
import { getSettingsSerialized } from "@/lib/settings-api"

/** Session key for product tree + version (settings `versions.Product`). */
export const PRODUCTS_CACHE_KEY = "products_cache"

export const PRODUCTS_CACHE_UPDATED_EVENT = "products_cache_updated"

/** Persisted alongside `settings_cache` when /settings returns (for version checks without extra calls). */
export const SETTINGS_VERSIONS_CACHE_KEY = "settings_versions_cache"

function hasAuthToken(): boolean {
  if (typeof window === "undefined") return false
  try {
    return !!window.localStorage.getItem("auth_token")
  } catch {
    return false
  }
}

/** Single in-flight /products; concurrent callers wait for the same request. */
let productsFetchInFlight: Promise<boolean> | null = null

/** Normalize /products response: filter empty branches, qty fields, dedupe by id. */
export function normalizeProductsCategoriesFromResponse(data: unknown): any[] | null {
  const d = data as { categories?: unknown } | null
  if (!d || !Array.isArray(d.categories)) return null

  const filterNodesWithProducts = (nodes: any[]): any[] => {
    return nodes
      .map((node: any) => {
        const filteredChildren = Array.isArray(node?.subcategories) ? filterNodesWithProducts(node.subcategories) : undefined
        const productsCount = Array.isArray(node?.products) ? node.products.length : 0
        const hasProductsHere = productsCount > 0
        const hasProductsInChildren = Array.isArray(filteredChildren) && filteredChildren.length > 0
        if (!hasProductsHere && !hasProductsInChildren) {
          return null as unknown as any
        }
        return { ...node, ...(filteredChildren ? { subcategories: filteredChildren } : {}) }
      })
      .filter((n: any) => Boolean(n))
  }
  const filtered = filterNodesWithProducts(d.categories as any[])

  const normalizeProductQuantities = (nodes: any[]): any[] => {
    return nodes.map((node: any) => {
      const withProducts = Array.isArray(node?.products)
        ? {
            products: node.products.map((p: any) => ({
              ...p,
              quantity: typeof p?.quantity === "number" ? p.quantity : (p?.available_qty ?? 0),
            })),
          }
        : {}
      const withChildren = Array.isArray(node?.subcategories)
        ? { subcategories: normalizeProductQuantities(node.subcategories) }
        : {}
      return { ...node, ...withProducts, ...withChildren }
    })
  }
  const filteredWithQuantities = normalizeProductQuantities(filtered)

  const dedupeProductsInTree = (nodes: any[]): any[] => {
    return nodes.map((node: any) => {
      let nextProducts = Array.isArray(node?.products) ? node.products : undefined
      if (Array.isArray(nextProducts)) {
        const seen = new Set<number>()
        nextProducts = nextProducts.filter((p: any) => {
          const id = Number(p?.id)
          if (!Number.isFinite(id)) return false
          if (seen.has(id)) return false
          seen.add(id)
          return true
        })
      }
      const nextChildren = Array.isArray(node?.subcategories) ? dedupeProductsInTree(node.subcategories) : undefined
      return { ...node, ...(nextProducts ? { products: nextProducts } : {}), ...(nextChildren ? { subcategories: nextChildren } : {}) }
    })
  }
  return dedupeProductsInTree(filteredWithQuantities)
}

export function getCachedProductsVersion(): number {
  try {
    const raw = sessionStorage.getItem(PRODUCTS_CACHE_KEY)
    if (!raw) return 0
    const parsed = JSON.parse(raw)
    return typeof parsed?.version === "number" ? parsed.version : 0
  } catch {
    return 0
  }
}

/**
 * Call /products only when:
 * - Authenticated user has no products cache yet (e.g. cleared storage), or
 * - settings `Product` version is newer than `products_cache.version` (compare with `settings_versions_cache` / API).
 * Guests never trigger a product fetch from settings.
 */
export function shouldFetchProductsFromServer(serverProductVersion: number): boolean {
  try {
    const raw = sessionStorage.getItem(PRODUCTS_CACHE_KEY)
    if (!raw) {
      return hasAuthToken() && serverProductVersion > 0
    }
    const cached = getCachedProductsVersion()
    if (!serverProductVersion) return false
    return serverProductVersion > cached
  } catch {
    return false
  }
}

export function storeProductsCache(version: number, categories: any[]): void {
  try {
    sessionStorage.setItem(PRODUCTS_CACHE_KEY, JSON.stringify({ version, categories }))
  } catch {
    return
  }
  if (typeof window !== "undefined") {
    try {
      window.dispatchEvent(new CustomEvent(PRODUCTS_CACHE_UPDATED_EVENT))
    } catch {
      /* ignore */
    }
  }
}

/** GET /products, normalize, write session cache + dispatch. Returns false if response unusable. */
export async function fetchProductsAndStoreCache(productVersion: number): Promise<boolean> {
  const res = await api.get("/products")
  const deduped = normalizeProductsCategoriesFromResponse(res?.data)
  if (!deduped) return false
  storeProductsCache(productVersion, deduped)
  return true
}

/**
 * Same as {@link fetchProductsAndStoreCache} but coalesces concurrent callers into one HTTP request.
 */
export async function fetchProductsAndStoreCacheDeduped(productVersion: number): Promise<boolean> {
  if (productsFetchInFlight) {
    return productsFetchInFlight
  }
  productsFetchInFlight = (async () => {
    try {
      return await fetchProductsAndStoreCache(productVersion)
    } finally {
      productsFetchInFlight = null
    }
  })()
  return productsFetchInFlight
}

/**
 * On login: call /products first, then resolve Product version (login payload, else /settings).
 */
export async function refreshProductsCacheAfterLogin(loginVersions: { Product?: number } | null | undefined): Promise<void> {
  const res = await api.get("/products")
  const deduped = normalizeProductsCategoriesFromResponse(res?.data)
  if (!deduped) return

  let v = 0
  const pv = loginVersions?.Product
  if (typeof pv === "number" && pv > 0) {
    v = pv
  } else if (pv != null) {
    const n = Number(pv)
    if (Number.isFinite(n) && n > 0) v = n
  }
  if (!v) {
    try {
      const sr = await getSettingsSerialized()
      const sp = sr?.data?.versions?.Product
      const n = typeof sp === "number" ? sp : Number(sp || 0)
      if (Number.isFinite(n) && n > 0) v = n
    } catch {
      /* keep v */
    }
  }
  storeProductsCache(v, deduped)
}
