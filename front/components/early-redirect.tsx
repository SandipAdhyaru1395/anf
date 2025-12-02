"use client";

import React from "react";

export default function EarlyRedirect() {
  React.useEffect(() => {
    try {
      const path = window.location.pathname || "/";
      const base = path.startsWith("/nwb2b/front") ? "/nwb2b/front" : "/";
      const login = (base.replace(/\/$/, "")) + "/login";
      if (!path.startsWith(login)) {
        const token = localStorage.getItem("auth_token");
        if (!token) {
          const q = new URLSearchParams({ redirect: path }).toString();
          const sep = login.indexOf("?") === -1 ? "?" : "&";
          window.location.replace(login + sep + q);
        }
      }
    } catch {
      // ignore
    }
  }, []);
  return null;
}


