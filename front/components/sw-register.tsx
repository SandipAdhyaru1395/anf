"use client";

import React from "react";
import { buildPath } from "@/lib/utils";

export default function SwRegister() {
  React.useEffect(() => {
    // console.log(process.env.NEXT_PUBLIC_API_URL);
    // Register SW only when running under the deployed base path (or when SW exists)
    if (!process.env.NEXT_PUBLIC_API_URL) return;
    if (!("serviceWorker" in navigator)) return;
    const register = async () => {
      try {
        // Use versioned SW to force fresh install and avoid stale precache caches
        const swUrl = buildPath("/sw.js");
        const scope = buildPath("/");
        const registration = await navigator.serviceWorker.register(swUrl, { scope });
        // Ensure the SW controls the page ASAP
        if (!navigator.serviceWorker.controller) {
          await navigator.serviceWorker.ready;
        }
        // If there is an old worker waiting, force activation
        if (registration.waiting) {
          registration.waiting.postMessage({ type: 'SKIP_WAITING' });
        }
      } catch {
        // noop
      }
    };
    register();
  }, []);
  return null;
}



