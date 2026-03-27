"use client"

import React, { createContext, useContext, useEffect, useMemo, useState } from "react"
import api from "@/lib/axios"
import { resolveBackendAssetUrl } from "@/lib/utils"
import { fetchProductsAndStoreCache, shouldFetchProductsFromServer } from "@/lib/products-cache"

type Theme = {
  use_default?: boolean | null
  primary_bg_color?: string | null
  primary_font_color?: string | null
  secondary_bg_color?: string | null
  secondary_font_color?: string | null
  button_login?: string | null
}

type Settings = {
  company_title?: string | null
  company_logo_url?: string | null
  currency?: string | null
  currency_symbol?: string | null
  banner?: string | null
  thumbnail?: string | null
  maintenance_mode?: boolean | null
  payment_gateway_available?: boolean | null
  theme?: Theme | null
  leading_brands?: Array<{ id: number; name: string; image_url?: string | null }> | null
}

type RawSettings = Settings & {
  banner_url?: string | null
  banner_image?: string | null
  banner_image_url?: string | null
  company_thumbnail?: string | null
  company_thumbnail_url?: string | null
}

type Versions = {
  Product?: number
  Order?: number
  Customer?: number
}

type SettingsContextValue = {
  settings: Settings | null
  loading: boolean
  error: string | null
  refresh: () => Promise<void>
  serverMaintenance: boolean
  versions: Versions | null
}

/** Fix Laravel /storage URLs vs Next dev origin (NEXT_PUBLIC_API_URL). */
function normalizeSettingsMediaUrls(s: Settings): Settings {
  const raw = s as RawSettings
  const rawBanner =
    raw.banner ??
    raw.banner_url ??
    raw.banner_image ??
    raw.banner_image_url ??
    null
  const rawThumb =
    raw.thumbnail ??
    raw.company_thumbnail ??
    raw.company_thumbnail_url ??
    null
  const rawLogo = raw.company_logo_url ?? null

  const normalizedLogo = resolveBackendAssetUrl(rawLogo) ?? rawLogo ?? null
  const normalizedThumb = resolveBackendAssetUrl(rawThumb) ?? rawThumb ?? null
  const normalizedBanner = resolveBackendAssetUrl(rawBanner) ?? rawBanner ?? null

  return {
    ...raw,
    company_logo_url: normalizedLogo,
    // Fallback ensures promo image area is not empty when banner is missing.
    banner: normalizedBanner ?? normalizedThumb ?? normalizedLogo,
    thumbnail: normalizedThumb,
  }
}

const SettingsContext = createContext<SettingsContextValue | undefined>(undefined)

export function SettingsProvider({ children }: { children: React.ReactNode }) {
  const [settings, setSettings] = useState<Settings | null>(null)
  const [loading, setLoading] = useState<boolean>(true)
  const [error, setError] = useState<string | null>(null)
  const [serverMaintenance, setServerMaintenance] = useState<boolean>(false)
  const [versions, setVersions] = useState<Versions | null>(null)

  const fetchSettings = async (silent = false) => {
    if (!silent) setLoading(true)
    setError(null)
    try {
      const res = await api.get('/settings')
      const s = res?.data?.settings
      const v = res?.data?.versions
      const normalized: Settings = normalizeSettingsMediaUrls({
        company_title: s?.company_title ?? null,
        company_logo_url: s?.company_logo_url ?? null,
        currency: s?.currency ?? null,
        currency_symbol: s?.currency_symbol ?? null,
        banner: s?.banner ?? null,
        thumbnail: s?.thumbnail ?? null,
        leading_brands: Array.isArray(s?.leading_brands) ? s.leading_brands : null,
        maintenance_mode: typeof s?.maintenance_mode === 'boolean' ? s.maintenance_mode : null,
        payment_gateway_available: typeof s?.payment_gateway_available === 'boolean' ? s.payment_gateway_available : null,
        theme: s?.theme ? {
          use_default: typeof s.theme.use_default === 'boolean' ? s.theme.use_default : null,
          primary_bg_color: s.theme.primary_bg_color ?? null,
          primary_font_color: s.theme.primary_font_color ?? null,
          secondary_bg_color: s.theme.secondary_bg_color ?? null,
          secondary_font_color: s.theme.secondary_font_color ?? null,
          button_login: s.theme.button_login ?? null,
        } : null,
      })
      setSettings(normalized)
      setVersions({
        Product: typeof v?.Product === 'number' ? v.Product : undefined,
        Order: typeof v?.Order === 'number' ? v.Order : undefined,
        Customer: typeof v?.Customer === 'number' ? v.Customer : undefined,
      })
      setServerMaintenance(false)
      try { sessionStorage.setItem('settings_cache', JSON.stringify(normalized)) } catch {}

      // Background: reconcile caches by version (products/orders)
      try {
        const vers = {
          Product: typeof v?.Product === 'number' ? v.Product : 0,
          Order: typeof v?.Order === 'number' ? v.Order : 0,
          Customer: typeof v?.Customer === 'number' ? v.Customer : 0,
        }

        // Reconcile Products cache (only when settings Product version is newer than the cache)
        ;(async () => {
          try {
            if (shouldFetchProductsFromServer(vers.Product)) {
              await fetchProductsAndStoreCache(vers.Product)
            }
          } catch {}
        })()

        // Reconcile Orders cache (only if authenticated)
        ;(async () => {
          try {
            const token = typeof window !== 'undefined' ? window.localStorage.getItem('auth_token') : null
            if (!token) return
            const raw = sessionStorage.getItem('orders_cache')
            let cachedVersion = 0
            if (raw) {
              try {
                const parsed = JSON.parse(raw)
                if (typeof parsed?.version === 'number') cachedVersion = parsed.version
              } catch {}
            }
            if (vers.Order && vers.Order !== cachedVersion) {
              const res = await api.get('/orders')
              const dataOrders = res?.data
              if (dataOrders?.success && Array.isArray(dataOrders?.orders)) {
                try {
                  sessionStorage.setItem('orders_cache', JSON.stringify({ version: vers.Order, orders: dataOrders.orders }))
                  if (typeof window !== 'undefined') {
                    window.dispatchEvent(new CustomEvent('orders_cache_updated'))
                  }
                } catch {}
              }
            }
          } catch {}
        })()

        // Reconcile Customer cache (only if authenticated)
        ;(async () => {
          try {
            const token = typeof window !== 'undefined' ? window.localStorage.getItem('auth_token') : null
            if (!token) return
            const raw = sessionStorage.getItem('customer_cache')
            let cachedVersion = 0
            if (raw) {
              try {
                const parsed = JSON.parse(raw)
                if (typeof parsed?.version === 'number') cachedVersion = parsed.version
              } catch {}
            }
            if (vers.Customer && vers.Customer !== cachedVersion) {
              const [resCustomer, resFav] = await Promise.all([
                api.get('/customer'),
                api.get('/favorites')
              ])
              const c = resCustomer?.data?.customer
              const ids = Array.isArray(resFav?.data?.product_ids) ? resFav.data.product_ids.map((n: any) => Number(n)) : []
              if (c && typeof c.wallet_balance !== 'undefined') {
                const normalized = {
                  id: Number(c.id),
                  name: c.name ?? null,
                  email: c.email ?? null,
                  phone: c.phone ?? null,
                  wallet_balance: Number(c.wallet_balance) || 0,
                  company_name: c.company_name ?? null,
                  address_line1: c.address_line1 ?? null,
                  address_line2: c.address_line2 ?? null,
                  city: c.city ?? null,
                  country: c.country ?? null,
                  postcode: c.postcode ?? null,
                  rep_name: c.rep_name ?? null,
                  rep_email: c.rep_email ?? null,
                  rep_mobile: c.rep_mobile ?? null,
                  pay_later_allowed: !!c.pay_later_allowed,
                }
                try {
                  sessionStorage.setItem('customer_cache', JSON.stringify({ version: vers.Customer, customer: normalized as any, favoriteProductIds: ids }))
                  if (typeof window !== 'undefined') {
                    window.dispatchEvent(new CustomEvent('customer_cache_updated'))
                  }
                } catch {}
              }
            }
          } catch {}
        })()
      } catch {}
    } catch (e: any) {
      const status = e?.response?.status
      if (status === 503) {
        setServerMaintenance(true)
      }
      setError(e?.message || 'Failed to load settings')
      setSettings(null)
      setVersions(null)
      try { sessionStorage.removeItem('settings_cache') } catch {}
    } finally {
      if (!silent) setLoading(false)
    }
  }

  useEffect(() => {
    // Serve cached settings if available; otherwise fetch fresh data
    try {
      const raw = sessionStorage.getItem('settings_cache')
      if (raw) {
        const cachedSettings = JSON.parse(raw)
        // Ensure new fields exist in cached settings
        if (cachedSettings) {
          if (!cachedSettings.hasOwnProperty('banner')) {
            cachedSettings.banner = null
          }
          if (!cachedSettings.hasOwnProperty('thumbnail')) {
            cachedSettings.thumbnail = null
          }
          if (!cachedSettings.hasOwnProperty('payment_gateway_available')) {
            cachedSettings.payment_gateway_available = null
          }
          const fixed = normalizeSettingsMediaUrls(cachedSettings as Settings)
          try { sessionStorage.setItem('settings_cache', JSON.stringify(fixed)) } catch {}
          setSettings(fixed)
        } else {
          setSettings(cachedSettings)
        }
        setLoading(false)
        // Always revalidate in background so newly updated banner/logo/settings appear
        // without requiring users to clear session storage manually.
        fetchSettings(true)
        return
      }
    } catch {}
    
    // No cached data available, fetch fresh data
    fetchSettings()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const value = useMemo<SettingsContextValue>(() => ({
    settings,
    loading,
    error,
    refresh: fetchSettings,
    serverMaintenance,
    versions,
  }), [settings, loading, error, serverMaintenance, versions])

  return <SettingsContext.Provider value={value}>{children}</SettingsContext.Provider>
}

export function useSettings() {
  const ctx = useContext(SettingsContext)
  if (!ctx) throw new Error('useSettings must be used within SettingsProvider')
  return ctx
}


