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
  if (typeof window === 'undefined') return '/'
  const pathname = window.location?.pathname || '/'
  return pathname.startsWith('/anf/front') ? '/anf/front' : '/'
}

// Joins the base path with a relative path segment safely.
export function buildPath(relativePath: string): string {
  const base = getBasePath()
  const rel = (relativePath || '').replace(/^\/+/, '')
  const joined = `${base}/${rel}`
  return joined.replace(/\/+/g, '/')
}
