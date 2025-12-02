"use client"

import React, { useEffect, useMemo } from "react"
import { useSettings } from "@/components/settings-provider"

function MaintenanceScreen() {
  return (
    <div style={{ minHeight: '100dvh' }} className="flex items-center justify-center p-6 text-center">
      <div className="max-w-md space-y-3">
        <div className="text-3xl font-semibold">We’ll be right back</div>
        <div className="text-muted-foreground">Our store is under scheduled maintenance. Please check back soon.</div>
      </div>
    </div>
  )
}

export default function StoreMaintenanceGate({ children }: { children: React.ReactNode }) {
  const { settings, refresh, serverMaintenance } = useSettings()

  const storeMaint = Boolean(settings?.maintenance_mode) || serverMaintenance

  // Bypass if URL path contains the secret as the first segment
  const isBypassed = useMemo(() => {
    return !storeMaint
  }, [storeMaint])

  // Auto-refresh settings periodically and on tab visibility change
  useEffect(() => {
    let intervalId: any
    const startPolling = () => {
      // Poll every 5 seconds to detect changes from admin instantly
      intervalId = setInterval(() => {
        refresh().catch(() => {})
      }, 5000)
    }
    const handleVisibility = () => {
      if (document.visibilityState === 'visible') {
        refresh().catch(() => {})
      }
    }
    startPolling()
    document.addEventListener('visibilitychange', handleVisibility)
    return () => {
      if (intervalId) clearInterval(intervalId)
      document.removeEventListener('visibilitychange', handleVisibility)
    }
  }, [refresh])

  if (storeMaint && !isBypassed) {
    return <MaintenanceScreen />
  }

  return <>{children}</>
}




