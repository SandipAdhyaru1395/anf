import { buildPath } from "@/lib/utils"

const ACCOUNT_DISABLED_CODES = new Set([
  "account_inactive",
  "account_not_approved",
  "account_rejected",
])

/**
 * Clears customer auth storage and sends the user to the login page (full navigation).
 * Used when the API reports the account is no longer allowed to use the storefront.
 */
export function clearCustomerSessionAndRedirect(reason: string): void {
  if (typeof window === "undefined") return

  try {
    window.localStorage.removeItem("auth_token")
  } catch {
    /* ignore */
  }
  try {
    sessionStorage.removeItem("customer_cache")
  } catch {
    /* ignore */
  }
  try {
    sessionStorage.removeItem("customer_cache_version")
  } catch {
    /* ignore */
  }

  const loginPath = buildPath(`/login?reason=${encodeURIComponent(reason)}`)
  const onLogin = window.location.pathname.endsWith("/login") || window.location.pathname.includes("/login/")
  if (!onLogin) {
    window.location.replace(loginPath)
  }
}

export function isAccountDisabledApiCode(code: unknown): boolean {
  return typeof code === "string" && ACCOUNT_DISABLED_CODES.has(code)
}
