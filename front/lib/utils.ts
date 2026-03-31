import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function setFavicon(url: string) {
  if (typeof document === 'undefined') return
  const rels = ['icon', 'shortcut icon', 'apple-touch-icon']
  rels.forEach((rel) => {
    let link = document.querySelector(`link[rel="${rel}"]`) as HTMLLinkElement | null
    if (!link) {
      link = document.createElement('link')
      link.rel = rel
      document.head.appendChild(link)
    }
    link.href = url
    if (rel === 'icon') link.type = 'image/png'
  })
}

// Returns the base path this app is served from.
// Local dev: '/'
// Production (aidemo): '/anf/front'
export function getBasePath(): string {
  const configured = (process.env.BASE_PATH || '').trim()
  if (configured !== '') {
    return `/${configured.replace(/^\/+|\/+$/g, '')}`
  }

  if (typeof window === 'undefined') return '/'
  const pathname = window.location?.pathname || '/'
  if (pathname === '/anf/front' || pathname.startsWith('/anf/front/')) {
    return '/anf/front'
  }
  return '/'
}

// Joins the base path with a relative path segment safely.
export function buildPath(relativePath: string): string {
  const base = getBasePath()
  const rel = (relativePath || '').replace(/^\/+/, '')
  const joined = `${base}/${rel}`
  return joined.replace(/\/+/g, '/')
}

/** URL path for static files in `/public` (e.g. `/background.svg` vs `/anf/front/background.svg`). */
export function publicAssetUrl(assetPath: string): string {
  const p = (assetPath || "").replace(/^\/+/, "")
  return buildPath(`/${p}`)
}

/**
 * Laravel `asset('storage/...')` often returns `http://localhost/storage/...` while the app
 * actually lives under a subpath (e.g. XAMPP `/anf/admin/public`). The browser then 404s images.
 * Also prepends the API origin for relative paths like `/storage/...`.
 */
export function resolveBackendAssetUrl(url: string | null | undefined): string | null {
  if (url == null) return null
  const s = String(url).trim()
  if (!s) return null

  const rawBase =
    (typeof process !== "undefined" && process.env.NEXT_PUBLIC_API_URL) ||
    (typeof process !== "undefined" && process.env.NEXT_PUBLIC_API_BASE_URL) ||
    "http://localhost:8000"
  const apiOrigin = String(rawBase).replace(/\/api\/?$/i, "").replace(/\/$/, "")

  const withOrigin = (path: string) => {
    const p = path.startsWith("/") ? path : `/${path}`
    return `${apiOrigin}${p}`
  }

  if (s.startsWith("//")) {
    const proto = typeof window !== "undefined" ? window.location?.protocol || "https:" : "https:"
    return `${proto}${s}`
  }

  if (/^https?:\/\//i.test(s)) {
    try {
      const u = new URL(s)
      const host = u.hostname.toLowerCase()
      const isLocal = host === "localhost" || host === "127.0.0.1" || host === "[::1]"
      // Wrong base path: file is served from the Laravel `public` that hosts the API
      if (isLocal && u.pathname.startsWith("/storage/")) {
        return withOrigin(u.pathname + u.search + u.hash)
      }
    } catch {
      /* keep original */
    }
    return s
  }

  return withOrigin(s)
}
