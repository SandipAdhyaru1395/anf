"use client"

import { useEffect } from "react"
import { setFavicon } from "@/lib/utils"
import { useSettings } from "@/components/settings-provider"

export default function FaviconProvider() {
  const { settings } = useSettings()

  useEffect(() => {
    // Prefer context-provided settings; fall back to session cache.
    let url = settings?.company_logo_url || undefined
    if (!url) {
      try {
        const raw = sessionStorage.getItem('settings_cache')
        if (raw) {
          const parsed = JSON.parse(raw)
          if (parsed?.company_logo_url) url = parsed.company_logo_url as string
        }
      } catch {}
    }
    if (typeof url === 'string' && url.length > 0) setFavicon(url)
  }, [settings])
  return null
}


