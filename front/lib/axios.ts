import axios from "axios";
import { clearCustomerSessionAndRedirect, isAccountDisabledApiCode } from "./auth-session";

const rawBase = process.env.NEXT_PUBLIC_API_URL || process.env.NEXT_PUBLIC_API_BASE_URL || "http://localhost:8000";
const normalizedBase = rawBase.endsWith("/api") ? rawBase : `${rawBase.replace(/\/$/, "")}/api`;

const api = axios.create({
  baseURL: normalizedBase,
  headers: { "Content-Type": "application/json", "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" },
});

// Attach token from localStorage on browser
if (typeof window !== "undefined") {
  api.interceptors.request.use((config) => {
    const token = window.localStorage.getItem("auth_token");
    if (token) {
      config.headers = config.headers || {};
      (config.headers as any)["Authorization"] = `Bearer ${token}`;
    }
    return config;
  });

  api.interceptors.response.use(
    (response) => response,
    (error) => {
      const status = error?.response?.status as number | undefined;
      const code = error?.response?.data?.code;
      const reqUrl = String(error?.config?.url ?? "");
      const method = String(error?.config?.method ?? "").toLowerCase();

      if (status === 403 && isAccountDisabledApiCode(code)) {
        const reason =
          code === "account_inactive" ? "inactive" : code === "account_rejected" ? "rejected" : "pending";
        clearCustomerSessionAndRedirect(reason);
        return Promise.reject(error);
      }

      // Revoked or invalid token (e.g. admin cleared tokens)
      if (status === 401 && window.localStorage.getItem("auth_token")) {
        const isLoginAttempt = reqUrl.includes("/login") && method === "post";
        if (!isLoginAttempt) {
          clearCustomerSessionAndRedirect("session");
        }
      }

      return Promise.reject(error);
    },
  );
}

export default api;
