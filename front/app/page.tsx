"use client"

import { useEffect, useMemo, useState } from "react"
import api from "@/lib/axios"
import { MobileDashboard } from "@/components/mobile-dashboard"
import { MobileShop } from "@/components/mobile-shop"
import { MobileBasket } from "@/components/mobile-basket"
import { MobileCheckout } from "@/components/mobile-checkout"
import { MobileWallet } from "@/components/mobile-wallet"
import { MobileAccount } from "@/components/mobile-account"
import { MobileRepDetails } from "@/components/mobile-rep-details"
import { MobileCompanyDetails } from "@/components/mobile-company-details"
import { MobileOrders } from "@/components/mobile-orders"
import { MobileOrderDetails } from "@/components/mobile-order-details"
import { MobileBranches } from "@/components/mobile-branches"
import { MobileContactUs } from "@/components/mobile-contact-us"
import { MobileTermsAndConditions } from "@/components/mobile-terms-and-conditions"
import SplashScreen from "./welcome-screen"
import {
  coerceVersion,
  fetchProductsAndStoreCacheDeduped,
  SETTINGS_VERSIONS_CACHE_KEY,
  shouldFetchProductsFromServer,
} from "@/lib/products-cache"

type PageKey =
  | "dashboard"
  | "shop"
  | "basket"
  | "checkout"
  | "wallet"
  | "account"
  | "rep-details"
  | "company-details"
  | "orders"
  | "order-details"
  | "branches"
  | "contact-us"
  | "terms-and-conditions"

function findProductInProductsCache(productId: number): any | null {
  try {
    const raw = sessionStorage.getItem("products_cache")
    if (!raw) return null
    const parsed = JSON.parse(raw)
    const categories = Array.isArray(parsed) ? parsed : parsed?.categories
    if (!Array.isArray(categories)) return null
    const walk = (nodes: any[]): any => {
      for (const n of nodes) {
        if (Array.isArray(n?.products)) {
          for (const p of n.products) {
            if (Number(p?.id) === productId) return p
          }
        }
        if (Array.isArray(n?.subcategories)) {
          const found = walk(n.subcategories)
          if (found) return found
        }
      }
      return null
    }
    return walk(categories)
  } catch {
    return null
  }
}

/** Align server cart with parent state so dashboard sticky totals work on first paint / refresh. */
function mapCartApiResponseToCartRecord(res: any): Record<number, { product: any; quantity: number }> {
  const items: any[] = res?.data?.cart?.items || []
  const next: Record<number, { product: any; quantity: number }> = {}
  for (const it of items) {
    const productId = Number(it?.product?.id ?? it?.product_id)
    if (!Number.isFinite(productId)) continue
    const p = it?.product ?? findProductInProductsCache(productId)
    if (!p) continue
    const baseUnit = Number(p?.price ?? it?.original_unit_price ?? it?.unit_price ?? 0)
    const effectiveUnit = Number(p?.effective_price ?? it?.unit_price ?? baseUnit)
    const stepQty = Number(p?.step_quantity) > 0 ? Number(p.step_quantity) : 1
    next[productId] = {
      product: {
        id: productId,
        name: String(p?.name ?? ""),
        image: String(p?.image ?? ""),
        price: String(Number.isFinite(effectiveUnit) ? effectiveUnit : baseUnit),
        discount: p?.discount != null ? String(p.discount) : undefined,
        wallet_credit: Number(p?.wallet_credit ?? 0),
        step_quantity: stepQty,
      },
      quantity: Number(it?.quantity) || 0,
    }
  }
  return next
}

export default function Home() {
  const [hasToken, setHasToken] = useState<boolean | null>(null)
  const [currentPage, setCurrentPage] = useState<PageKey>("dashboard")
  const [showFavorites, setShowFavorites] = useState(false)
  const [selectedOrderNumber, setSelectedOrderNumber] = useState<string | null>(null)
  const [cart, setCart] = useState<Record<number, { product: any; quantity: number }>>({})

  // Navigation and State Helpers
  const handleNavigate = (page: PageKey, favorites = false) => {
    setCurrentPage(page)
    setShowFavorites(favorites)
    if (page === "shop") {
      ;(async () => {
        try {
          const raw = sessionStorage.getItem(SETTINGS_VERSIONS_CACHE_KEY)
          if (!raw) return
          const parsed = JSON.parse(raw)
          const productVersion = coerceVersion(parsed?.Product)
          if (shouldFetchProductsFromServer(productVersion)) {
            await fetchProductsAndStoreCacheDeduped(productVersion)
          }
        } catch {
          // ignore cache refresh errors; shop still opens with current cache
        }
      })()
    }
  }

  const openOrder = (orderNumber: string) => {
    setSelectedOrderNumber(orderNumber)
    handleNavigate("order-details")
  }

  const clearCart = () => {
    setCart({})
  }

  const increment = (product: any) => {
    setCart((prev) => {
      const step = Number(product?.step_quantity) > 0 ? Number(product.step_quantity) : 1
      const current = prev[product.id]?.quantity ?? 0
      return { ...prev, [product.id]: { product, quantity: current + step } }
    })
  }

  const decrement = (product: any) => {
    setCart((prev) => {
      const step = Number(product?.step_quantity) > 0 ? Number(product.step_quantity) : 1
      const current = prev[product.id]?.quantity ?? 0
      const nextQty = Math.max(0, current - step)
      const next = { ...prev }
      if (nextQty === 0) {
        delete next[product.id]
        return { ...next }
      }
      next[product.id] = { product, quantity: nextQty }
      return next
    })
  }

  const parseMoney = (value?: string): number => {
    if (!value) return 0
    const match = value.replace(/[^0-9.\-]/g, "")
    const num = parseFloat(match)
    return Number.isFinite(num) ? num : 0
  }

  // Auth initialization
  useEffect(() => {
    try {
      const token = window.localStorage.getItem("auth_token")
      setHasToken(Boolean(token))
    } catch {
      setHasToken(false)
    }
  }, [])

  // Handle redirects from /payment-result based on stored flags
  useEffect(() => {
    try {
      const target = sessionStorage.getItem("post_payment_page")
      const shouldClearCart = sessionStorage.getItem("post_payment_clear_cart")

      if (shouldClearCart === "1") {
        setCart({})
        sessionStorage.removeItem("post_payment_clear_cart")
      }

      if (target === "order-details") {
        const orderNum = sessionStorage.getItem("post_payment_order_number")
        sessionStorage.removeItem("post_payment_page")
        if (orderNum) {
          sessionStorage.removeItem("post_payment_order_number")
          setSelectedOrderNumber(orderNum)
          handleNavigate("order-details")
        }
        return
      }

      if (target === "checkout" || target === "dashboard") {
        handleNavigate(target as PageKey)
        sessionStorage.removeItem("post_payment_page")
      }
    } catch {
      // ignore
    }
  }, [])

  // Deep link from emails: storefront base URL with ?order=ORDER_NUMBER
  useEffect(() => {
    if (hasToken !== true || typeof window === "undefined") return
    try {
      const params = new URLSearchParams(window.location.search)
      const orderParam = params.get("order")?.trim()
      if (!orderParam) return
      const url = new URL(window.location.href)
      url.searchParams.delete("order")
      const nextSearch = url.searchParams.toString()
      window.history.replaceState({}, "", nextSearch ? `${url.pathname}?${nextSearch}` : url.pathname)
      setSelectedOrderNumber(orderParam)
      handleNavigate("order-details")
    } catch {
      // ignore
    }
  }, [hasToken])

  // Hydrate cart from API so dashboard (and other pages) show correct sticky totals after refresh.
  useEffect(() => {
    if (hasToken !== true) return
    let cancelled = false
    const loadCart = async () => {
      try {
        const res = await api.get("/cart")
        if (cancelled) return
        setCart(mapCartApiResponseToCartRecord(res))
      } catch {
        /* unauthenticated or network */
      }
    }
    loadCart()
    const onProductsCacheUpdated = () => {
      loadCart()
    }
    if (typeof window !== "undefined") {
      window.addEventListener("products_cache_updated", onProductsCacheUpdated)
    }
    return () => {
      cancelled = true
      if (typeof window !== "undefined") {
        window.removeEventListener("products_cache_updated", onProductsCacheUpdated)
      }
    }
  }, [hasToken])

  const totals = useMemo(() => {
    const entries = Object.values(cart)
    const units = entries.reduce((sum, item) => sum + item.quantity, 0)
    const skus = entries.length
    const subtotal = entries.reduce((sum, item) => sum + parseMoney(item.product.price) * item.quantity, 0)
    const totalDiscount = entries.reduce((sum, item) => sum + parseMoney(item.product.discount) * item.quantity, 0)
    const total = Math.max(0, subtotal - totalDiscount)
    return { units, skus, subtotal, totalDiscount, total }
  }, [cart])

  // Entry gate
  if (hasToken === false) return <SplashScreen />
  if (hasToken === null) return <SplashScreen delayMs={0} />

  // Page Routing
  if (currentPage === "shop") {
    return (
      <div className="bg-[#F6F4FA]">
        <MobileShop
          onNavigate={handleNavigate}
          cart={cart}
          increment={increment}
          decrement={decrement}
          totals={totals}
          showFavorites={showFavorites}
        />
      </div>
    )
  }

  if (currentPage === "basket") {
    return (
      <MobileBasket
        onNavigate={handleNavigate}
        cart={cart}
        onCartSync={setCart}
        increment={increment}
        decrement={decrement}
        totals={totals}
        clearCart={clearCart}
        onBack={() => handleNavigate("shop")}
      />
    )
  }

  if (currentPage === "checkout") {
    return (
      <MobileCheckout
        onNavigate={handleNavigate}
        onBack={() => handleNavigate("basket")}
        cart={cart}
        totals={totals}
        clearCart={clearCart}
        onCheckoutSuccess={(orderNumber) => {
          setSelectedOrderNumber(orderNumber)
          handleNavigate("order-details")
        }}
      />
    )
  }

  if (currentPage === "wallet") {
    return (
      <MobileWallet
        onNavigate={handleNavigate}
        cart={cart}
        increment={increment}
        decrement={decrement}
        totals={totals}
        clearCart={clearCart}
      />
    )
  }

  if (currentPage === "account") {
    return (
      <div className="bg-[#F6F4FA]">
        <MobileAccount
          onNavigate={handleNavigate}
          cart={cart}
          increment={increment}
          decrement={decrement}
          totals={totals}
          clearCart={clearCart}
        />
      </div>
    )
  }

  if (currentPage === "rep-details") {
    return (
      <MobileRepDetails
        onNavigate={handleNavigate}
        onBack={() => handleNavigate("account")}
      />
    )
  }

  if (currentPage === "company-details") {
    return (
      <MobileCompanyDetails
        onNavigate={handleNavigate}
        onBack={() => handleNavigate("account")}
      />
    )
  }

  if (currentPage === "branches") {
    return (
      <MobileBranches
        onNavigate={handleNavigate}
        onBack={() => handleNavigate("account")}
      />
    )
  }

  if (currentPage === "contact-us") {
    return (
      <MobileContactUs
        onNavigate={handleNavigate}
      />
    )
  }

  if (currentPage === "terms-and-conditions") {
    return (
      <MobileTermsAndConditions onNavigate={handleNavigate} />
    )
  }

  if (currentPage === "orders") {
    return (
      <MobileOrders
        onNavigate={handleNavigate}
        onBack={() => handleNavigate("dashboard")}
        onOpenOrder={openOrder}
      />
    )
  }

  if (currentPage === "order-details" && selectedOrderNumber) {
    return (
      <MobileOrderDetails
        orderNumber={selectedOrderNumber || ""}
        onNavigate={handleNavigate}
        onBack={() => handleNavigate("orders")}
        onReorder={(items) => {
          setCart(() => {
            const next: Record<number, { product: any; quantity: number }> = {}
            for (const it of items) {
              if (!it?.product?.id) continue
              next[it.product.id] = { product: it.product, quantity: it.quantity }
            }
            return next
          })
          handleNavigate('basket')
        }}
      />
    )
  }

  // Dashboard is the default landing page for authenticated users
  return <MobileDashboard onNavigate={handleNavigate} onOpenOrder={openOrder} cart={cart} totals={totals} />
}