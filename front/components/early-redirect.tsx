"use client";
 
import React from "react";
import { buildPath, getBasePath } from "@/lib/utils";
 
export default function EarlyRedirect() {
  React.useEffect(() => {
    try {
      const path = window.location.pathname || "/";
      const base = getBasePath();

      const publicPaths = new Set<string>([
        `${base}/`.replace(/\/+$/, "/"),
        buildPath("/landing"),
        buildPath("/login"),
        buildPath("/register"),
        buildPath("/forgot-password"),
        buildPath("/forgot-email"),
        buildPath("/payment-result"),
      ]);

      // Allow public routes (and anything under them) without forced redirect
      for (const p of publicPaths) {
        if (path === p || path.startsWith(p.replace(/\/+$/, "") + "/")) return;
      }

      const token = localStorage.getItem("auth_token");
      if (!token) window.location.replace(buildPath("/landing"));
    } catch {
      // ignore
    }
  }, []);
  return null;
}
 
 