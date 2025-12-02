"use client"

import React, { createContext, useContext, useEffect, useState } from "react"
import api from "@/lib/axios"

type Customer = {
  id: number
  name?: string | null
  email?: string | null
  phone?: string | null
  wallet_balance: number
  company_name?: string | null
  address_line1?: string | null
  address_line2?: string | null
  city?: string | null
  country?: string | null
  postcode?: string | null
  rep_name?: string | null
  rep_email?: string | null
  rep_mobile?: string | null
}

type CustomerContextValue = {
  customer: Customer | null
  loading: boolean
  error: string | null
  refresh: () => Promise<void>
  favoriteProductIds: number[]
  refreshFavorites: () => Promise<void>
  isFavorite: (productId: number) => boolean
  setFavorite: (productId: number, next: boolean) => Promise<void>
}

const CustomerContext = createContext<CustomerContextValue | undefined>(undefined)

export function CustomerProvider({ children }: { children: React.ReactNode }) {
  const [customer, setCustomer] = useState<Customer | null>(null)
  const [loading, setLoading] = useState<boolean>(true)
  const [error, setError] = useState<string | null>(null)
  const [favoriteProductIds, setFavoriteProductIds] = useState<number[]>([])

  const fetchCustomer = async () => {
    setLoading(true)
    setError(null)
    try {
      const [res, favRes] = await Promise.all([
        api.get("/customer"),
        api.get("/favorites")
      ])
      const c = res?.data?.customer
      if (c && typeof c.wallet_balance !== "undefined") {
        const normalized: Customer = {
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
          rep_name : c.rep_name ?? null,
          rep_email : c.rep_email ?? null,
          rep_mobile : c.rep_mobile ?? null
        }
        setCustomer(normalized)
        const ids: number[] = Array.isArray(favRes?.data?.product_ids) ? favRes.data.product_ids.map((n: any) => Number(n)) : []
        setFavoriteProductIds(ids)
        try {
          // Carry forward version set by login flow (if present)
          let version: number | undefined = undefined;
          try {
            const v = sessionStorage.getItem('customer_cache_version');
            if (v) {
              const num = Number(v);
              if (!Number.isNaN(num) && num >= 0) version = num;
            }
          } catch {}
          const payload: any = { customer: normalized, favoriteProductIds: ids };
          if (typeof version === 'number') payload.version = version;
          sessionStorage.setItem('customer_cache', JSON.stringify(payload));
          // Clear the temporary version flag once persisted into customer_cache
          try { sessionStorage.removeItem('customer_cache_version') } catch {}
        } catch {}
      } else {
        setCustomer(null)
        setFavoriteProductIds([])
        try { sessionStorage.removeItem('customer_cache') } catch {}
      }
    } catch (e: any) {
      const status = e?.response?.status
      const message = e?.response?.data?.message || e?.message
      // If customer is deleted or not found, force logout and inform user on login screen
      if (status === 404 || status === 410 || (typeof message === 'string' && message.toLowerCase().includes('deleted'))) {
        try { sessionStorage.setItem('account_deleted', '1') } catch {}
        try { window.localStorage.removeItem('auth_token') } catch {}
        // Avoid infinite loops if already on login
        try {
          if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/login')) {
            const q = new URLSearchParams({ reason: 'deleted' }).toString()
            window.location.replace(`/login?${q}`)
          }
        } catch {}
        setError('Your account has been deleted')
      } else {
        setError(message || "Failed to load customer")
      }
      setCustomer(null)
      setFavoriteProductIds([])
    } finally {
      setLoading(false)
    }
  }
  const refreshFavorites = async () => {
    try {
      const res = await api.get('/favorites')
      const ids: number[] = Array.isArray(res?.data?.product_ids) ? res.data.product_ids.map((n: any) => Number(n)) : []
      setFavoriteProductIds(ids)
    } catch {
      // ignore
    }
  }

  const isFavorite = (productId: number) => favoriteProductIds.includes(Number(productId))

  const setFavorite = async (productId: number, next: boolean) => {
    const id = Number(productId)
    // optimistic update
    setFavoriteProductIds((prev) => next ? (prev.includes(id) ? prev : [...prev, id]) : prev.filter((x) => x !== id))
    try {
      if (next) {
        await api.post('/favorites/add', { product_id: id })
      } else {
        await api.delete('/favorites/remove', { data: { product_id: id } })
      }
    } catch {
      // revert on error
      setFavoriteProductIds((prev) => !next ? (prev.includes(id) ? prev : [...prev, id]) : prev.filter((x) => x !== id))
    }
  }

  useEffect(() => {
    // Serve cached customer if present; otherwise fetch fresh data
    try {
      const raw = sessionStorage.getItem('customer_cache')
      if (raw) {
        const parsed = JSON.parse(raw)
        if (parsed?.customer) {
          setCustomer(parsed.customer as Customer)
          setFavoriteProductIds(Array.isArray(parsed?.favoriteProductIds) ? parsed.favoriteProductIds : [])
          // parsed.version is optional; retained in cache for consumers if needed
          setLoading(false)
          return
        }
      }
    } catch {}
    
    // No cached data available, fetch fresh data
    fetchCustomer()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  // Listen for external customer cache updates to re-render immediately
  useEffect(() => {
    const onCustomerCacheUpdated = () => {
      try {
        const raw = sessionStorage.getItem('customer_cache')
        if (!raw) return
        const parsed = JSON.parse(raw)
        if (parsed?.customer) {
          setCustomer(parsed.customer as Customer)
          setFavoriteProductIds(Array.isArray(parsed?.favoriteProductIds) ? parsed.favoriteProductIds : [])
        }
      } catch {}
    }
    if (typeof window !== 'undefined') {
      window.addEventListener('customer_cache_updated', onCustomerCacheUpdated)
    }
    return () => {
      if (typeof window !== 'undefined') {
        window.removeEventListener('customer_cache_updated', onCustomerCacheUpdated)
      }
    }
  }, [])

  const value: CustomerContextValue = {
    customer,
    loading,
    error,
    refresh: fetchCustomer,
    favoriteProductIds,
    refreshFavorites,
    isFavorite,
    setFavorite,
  }

  return <CustomerContext.Provider value={value}>{children}</CustomerContext.Provider>
}

export function useCustomer() {
  const ctx = useContext(CustomerContext)
  if (!ctx) throw new Error("useCustomer must be used within CustomerProvider")
  return ctx
}


