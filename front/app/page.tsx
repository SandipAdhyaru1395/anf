"use client"

import { useMemo, useState } from "react"
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

export default function Home() {
  const [currentPage, setCurrentPage] = useState<"dashboard" | "shop" | "basket" | "checkout" | "wallet" | "account" | "rep-details" | "company-details" | "orders" | "order-details" | "branches">("dashboard")
  const [showFavorites, setShowFavorites] = useState(false)
  const [selectedOrderNumber, setSelectedOrderNumber] = useState<string | null>(null)
  const [cart, setCart] = useState<Record<number, { product: any; quantity: number }>>({})

  const parseMoney = (value?: string): number => {
    if (!value) return 0
    const match = value.replace(/[^0-9.\-]/g, "")
    const num = parseFloat(match)
    return Number.isFinite(num) ? num : 0
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

  const clearCart = () => {
    setCart({})
  }

  const handleNavigate = (page: "dashboard" | "shop" | "basket" | "checkout" | "wallet" | "account" | "rep-details" | "company-details" | "orders" | "order-details" | "branches", favorites = false) => {
    setCurrentPage(page)
    setShowFavorites(favorites)
  }

  const totals = useMemo(() => {
    const entries = Object.values(cart)
    const units = entries.reduce((sum, item) => sum + item.quantity, 0)
    const skus = entries.length
    const subtotal = entries.reduce((sum, item) => sum + parseMoney(item.product.price) * item.quantity, 0)
    const totalDiscount = entries.reduce((sum, item) => sum + parseMoney(item.product.discount) * item.quantity, 0)
    const total = Math.max(0, subtotal - totalDiscount)
    return { units, skus, subtotal, totalDiscount, total }
  }, [cart])

  if (currentPage === "shop") {
    return (
      <MobileShop
        onNavigate={handleNavigate}
        cart={cart}
        increment={increment}
        decrement={decrement}
        totals={totals}
        showFavorites={showFavorites}
      />
    )
  }
  if (currentPage === "basket") {
    return (
      <MobileBasket
        onNavigate={setCurrentPage}
        cart={cart}
        increment={increment}
        decrement={decrement}
        totals={totals}
        clearCart={clearCart}
        onBack={() => setCurrentPage("shop")}
      />
    )
  }

  if (currentPage === "checkout") {
    return (
      <MobileCheckout
        onNavigate={setCurrentPage}
        onBack={() => setCurrentPage("basket")}
        cart={cart}
        totals={totals}
        clearCart={clearCart}
      />
    )
  }

  if (currentPage === "wallet") {
    return (
      <MobileWallet
        onNavigate={setCurrentPage}
        cart={cart}
        increment={increment}
        decrement={decrement}
        totals={totals}
        clearCart={clearCart}
      />
    )
  }

  if(currentPage === "account") {
    return (
      <MobileAccount
        onNavigate={handleNavigate}
        cart={cart}
        increment={increment}
        decrement={decrement}
        totals={totals}
        clearCart={clearCart}
      />
    )
  }

  if(currentPage === "rep-details") {
    return (
      <MobileRepDetails
        onNavigate={handleNavigate}
        onBack={() => setCurrentPage("account")}
      />
    )
  }

  if(currentPage === "company-details") {
    return (
      <MobileCompanyDetails
        onNavigate={handleNavigate}
        onBack={() => setCurrentPage("account")}
      />
    )
  }

  if(currentPage === "branches") {
    return (
      <MobileBranches
        onNavigate={handleNavigate}
        onBack={() => setCurrentPage("account")}
      />
    )
  }

  if(currentPage === "orders") {
    const openOrder = (orderNumber: string) => {
    setSelectedOrderNumber(orderNumber)
    setCurrentPage("order-details")
  }
    return (
      <MobileOrders
        onNavigate={handleNavigate}
        onBack={() => setCurrentPage("dashboard")}
        onOpenOrder={openOrder}
      />
    )
  }

  if(currentPage === "order-details" && selectedOrderNumber) {
    return (
      <MobileOrderDetails
        orderNumber={selectedOrderNumber}
        onNavigate={handleNavigate}
        onBack={() => setCurrentPage("orders")}
        onReorder={(items) => {
          // Replace cart contents with reordered items, then go to basket
          setCart(() => {
            const next: Record<number, { product: any; quantity: number }> = {}
            for (const it of items) {
              if (!it?.product?.id) continue
              next[it.product.id] = { product: it.product, quantity: it.quantity }
            }
            return next
          })
          setCurrentPage('basket')
        }}
      />
    )
  }

  const openOrder = (orderNumber: string) => {
    setSelectedOrderNumber(orderNumber)
    setCurrentPage("order-details")
  }

  return <MobileDashboard onNavigate={handleNavigate} onOpenOrder={openOrder} />
}
