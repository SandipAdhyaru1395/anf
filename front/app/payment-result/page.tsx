"use client"

import { Suspense, useEffect } from "react"
import { useRouter, useSearchParams } from "next/navigation"
import { buildPath } from "@/lib/utils"

function PaymentResultHandler() {
  const router = useRouter()
  const searchParams = useSearchParams()

  useEffect(() => {
    const status = searchParams.get("status")
    try {
      // Always clear any existing cart client-side; backend has already cleared its cart on success
      sessionStorage.setItem("post_payment_clear_cart", "1")

      if (status === "success") {
        // Refresh orders list on dashboard and land on dashboard
        sessionStorage.setItem("orders_needs_refresh", "1")
        sessionStorage.setItem("post_payment_page", "dashboard")
      } else {
        // On failure, send user back to checkout
        sessionStorage.setItem("post_payment_page", "checkout")
      }
    } catch {
      // ignore storage errors
    }

    // Redirect back to main mobile shell
    router.replace(buildPath("/"))
  }, [router, searchParams])

  return null
}

export default function PaymentResultPage() {
  return (
    <Suspense fallback={null}>
      <PaymentResultHandler />
    </Suspense>
  )
}

