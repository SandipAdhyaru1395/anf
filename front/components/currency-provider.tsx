"use client"

import { createContext, useContext, useEffect, useMemo, useState } from "react"
import { useSettings } from "@/components/settings-provider"

type CurrencyContextValue = {
  symbol: string
  code?: string
  format: (n: number | string | undefined | null) => string
}

const CurrencyContext = createContext<CurrencyContextValue | undefined>(undefined)

export function CurrencyProvider({ children }: { children: React.ReactNode }) {
  const { settings } = useSettings()
  const [symbol, setSymbol] = useState<string>('')
  const [code, setCode] = useState<string | undefined>('')

  useEffect(() => {
    const s = settings?.currency_symbol
    const c = settings?.currency
    if (typeof s === 'string' && s.length > 0) setSymbol(s)
    if (typeof c === 'string' && c.length > 0) setCode(c)
  }, [settings])

  const value = useMemo<CurrencyContextValue>(() => ({
    symbol,
    code,
    format: (n: number | string | undefined | null) => {
      const num = Number(n);
      return `${symbol}${!isNaN(num) ? num.toFixed(2) : '0.00'}`;
    }
  }), [symbol, code])

  return (
    <CurrencyContext.Provider value={value}>{children}</CurrencyContext.Provider>
  )
}

export function useCurrency() {
  const ctx = useContext(CurrencyContext)
  if (!ctx) throw new Error('useCurrency must be used within CurrencyProvider')
  return ctx
}


