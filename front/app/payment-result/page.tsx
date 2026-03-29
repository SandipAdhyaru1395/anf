"use client"

import { Suspense, useEffect } from "react"
import { useRouter, useSearchParams } from "next/navigation"
import { buildPath } from "@/lib/utils"
import api from "@/lib/axios"

function sleep(ms: number) {
  return new Promise((r) => setTimeout(r, ms))
}

function PaymentResultHandler() {
  const router = useRouter()
  const searchParams = useSearchParams()

  useEffect(() => {
    const run = async () => {
      const status = searchParams.get("status")
      try {
        sessionStorage.setItem("post_payment_clear_cart", "1")

        if (status === "success") {
          sessionStorage.setItem("orders_needs_refresh", "1")
          const inv = sessionStorage.getItem("dna_pending_invoice_id")
          if (inv) {
            sessionStorage.removeItem("dna_pending_invoice_id")
            let orderNum: string | null = null
            for (let i = 0; i < 20; i++) {
              try {
                const res = await api.get(`/checkout/dna-invoice/${encodeURIComponent(inv)}/order-number`)
                if (res.data?.success && res.data?.order_number != null && res.data.order_number !== "") {
                  orderNum = String(res.data.order_number)
                  break
                }
              } catch {
                // 404 until DNA callback creates the order
              }
              await sleep(400)
            }
            if (orderNum) {
              sessionStorage.setItem("post_payment_page", "order-details")
              sessionStorage.setItem("post_payment_order_number", orderNum)
            } else {
              sessionStorage.setItem("post_payment_page", "dashboard")
            }
          } else {
            sessionStorage.setItem("post_payment_page", "dashboard")
          }
        } else {
          sessionStorage.setItem("post_payment_page", "checkout")
        }
      } catch {
        // ignore storage errors
      }

      router.replace(buildPath("/"))
    }

    void run()
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
