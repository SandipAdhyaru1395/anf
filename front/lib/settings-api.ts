import type { AxiosResponse } from "axios"
import api from "@/lib/axios"

/**
 * Serializes every `/settings` HTTP call: the next request starts only after the
 * previous response has settled (success or failure).
 */
let settingsRequestChain: Promise<unknown> = Promise.resolve()

export function getSettingsSerialized(): Promise<AxiosResponse> {
  const next = settingsRequestChain.then(() => api.get("/settings"))
  settingsRequestChain = next.then(
    () => {},
    () => {}
  )
  return next as Promise<AxiosResponse>
}
