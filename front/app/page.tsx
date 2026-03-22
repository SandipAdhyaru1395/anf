"use client"

import { useEffect, useMemo, useState } from "react"
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
import SplashScreen from "./welcome-screen"

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

      if (target === "checkout" || target === "dashboard") {
        handleNavigate(target as PageKey)
        sessionStorage.removeItem("post_payment_page")
      }
    } catch {
      // ignore
    }
  }, [])

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