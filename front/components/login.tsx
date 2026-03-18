"use client";

import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm } from "react-hook-form";
import Link from "next/link";
import { buildPath } from "@/lib/utils";
import api from "@/lib/axios";
import { useSettings } from "@/components/settings-provider";
import FloatingInput from "@/components/ui/floating-input";
import { useToast } from "@/hooks/use-toast";
import { useCustomer } from "@/components/customer-provider";

export default function Login() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { settings, refresh: refreshSettings } = useSettings();
  const { toast } = useToast();
  const { refresh } = useCustomer();
  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<{ email: string; password: string }>({
    mode: "onSubmit",
    reValidateMode: "onChange",
    defaultValues: { email: "", password: "" },
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

 useEffect(() => {
  const token = typeof window !== "undefined" ? window.localStorage.getItem("auth_token") : null;

  try {
    const deleted = sessionStorage.getItem("account_deleted");
    if (deleted === "1") {
      sessionStorage.removeItem("account_deleted");
      window.localStorage.removeItem("auth_token");
      toast({
        variant: "destructive",
        title: "Your account has been deleted",
        description: "Please contact support if you believe this is a mistake.",
      });
    }
  } catch {}
  if (token) {
    console.log("User is logged in, but staying on this page for manual navigation.");
  }
}, []);

  async function onSubmit(values: { email: string; password: string }) {
    setError(null);
    setLoading(true);
    try {
      const { data } = await api.post("/login", {
        email: values.email,
        password: values.password,
        device_name: "nextjs-web",
      });
      if (data?.success && data?.token) {
        window.localStorage.setItem("auth_token", data.token);
        // Stash latest Customer version from login response for CustomerProvider to persist in cache
        try {
          const ver = Number(data?.versions?.Customer || 0);
          if (!Number.isNaN(ver) && ver > 0) {
            sessionStorage.setItem("customer_cache_version", String(ver));
          }
        } catch {}
        // Clear any stale caches first
        try {
          sessionStorage.removeItem("orders_cache");
          sessionStorage.removeItem("products_cache");
        } catch {}

        // Pull versions from settings to tag caches
        let productVersion = 0;
        let orderVersion = 0;
        try {
          const settingsRes = await api.get("/settings");
          const vers = settingsRes?.data?.versions;
          if (vers) {
            productVersion = Number(vers?.Product || 0) || 0;
            orderVersion = Number(vers?.Order || 0) || 0;
          }
        } catch {}

        // Refresh customer; CustomerProvider will persist customer_cache (with version)
        await refresh();

        // Preload and cache orders and products after successful login
        try {
          const ordersRes = await api.get("/orders");
          if (
            ordersRes?.data?.success &&
            Array.isArray(ordersRes.data.orders)
          ) {
            try {
              sessionStorage.setItem(
                "orders_cache",
                JSON.stringify({
                  version: orderVersion,
                  orders: ordersRes.data.orders,
                }),
              );
              if (typeof window !== "undefined") {
                window.dispatchEvent(new CustomEvent("orders_cache_updated"));
              }
            } catch {}
          }
        } catch {}

        try {
          const productsRes = await api.get("/products");
          const dataP = productsRes?.data;
          if (Array.isArray(dataP?.categories)) {
            const filterNodesWithProducts = (nodes: any[]): any[] => {
              return nodes
                .map((node: any) => {
                  const filteredChildren = Array.isArray(node?.subcategories)
                    ? filterNodesWithProducts(node.subcategories)
                    : undefined;
                  const productsCount = Array.isArray(node?.products)
                    ? node.products.length
                    : 0;
                  const hasProductsHere = productsCount > 0;
                  const hasProductsInChildren =
                    Array.isArray(filteredChildren) &&
                    filteredChildren.length > 0;
                  if (!hasProductsHere && !hasProductsInChildren) {
                    return null as unknown as any;
                  }
                  return {
                    ...node,
                    ...(filteredChildren
                      ? { subcategories: filteredChildren }
                      : {}),
                  };
                })
                .filter((n: any) => Boolean(n));
            };
            const filtered = filterNodesWithProducts(dataP.categories as any[]);
            // Ensure each product carries a quantity field alongside available_qty for cache consumers
            const normalizeProductQuantities = (nodes: any[]): any[] => {
              return nodes.map((node: any) => {
                const withProducts = Array.isArray(node?.products)
                  ? {
                      products: node.products.map((p: any) => ({
                        ...p,
                        quantity:
                          typeof p?.quantity === "number"
                            ? p.quantity
                            : (p?.available_qty ?? 0),
                      })),
                    }
                  : {};
                const withChildren = Array.isArray(node?.subcategories)
                  ? {
                      subcategories: normalizeProductQuantities(
                        node.subcategories,
                      ),
                    }
                  : {};
                return { ...node, ...withProducts, ...withChildren };
              });
            };
            const filteredWithQuantities = normalizeProductQuantities(filtered);
            // Remove duplicate products by id within each category tree
            const dedupeProductsInTree = (nodes: any[]): any[] => {
              return nodes.map((node: any) => {
                let nextProducts = Array.isArray(node?.products)
                  ? node.products
                  : undefined;
                if (Array.isArray(nextProducts)) {
                  const seen = new Set<number>();
                  nextProducts = nextProducts.filter((p: any) => {
                    const id = Number(p?.id);
                    if (!Number.isFinite(id)) return false;
                    if (seen.has(id)) return false;
                    seen.add(id);
                    return true;
                  });
                }
                const nextChildren = Array.isArray(node?.subcategories)
                  ? dedupeProductsInTree(node.subcategories)
                  : undefined;
                return {
                  ...node,
                  ...(nextProducts ? { products: nextProducts } : {}),
                  ...(nextChildren ? { subcategories: nextChildren } : {}),
                };
              });
            };
            const deduped = dedupeProductsInTree(filteredWithQuantities);
            try {
              sessionStorage.setItem(
                "products_cache",
                JSON.stringify({
                  version: productVersion,
                  categories: deduped,
                }),
              );
              if (typeof window !== "undefined") {
                window.dispatchEvent(new CustomEvent("products_cache_updated"));
              }
            } catch {}
          }
        } catch {}

        // Refresh settings last
        try {
          await refreshSettings();
        } catch {}
        toast({
          title: "Hello there 👋",
          description: "You've logged in successfully.",
        });
        window.location.replace(buildPath("/"));
        // router.replace("/dashboard");
      } else {
        const message = data?.message || "Login failed";
        setError(message);
        toast({
          variant: "destructive",
          title: "Login failed",
          description: message,
        });
      }
    } catch (err: any) {
      const message = err?.response?.data?.message || "Invalid credentials";
      setError(message);
      toast({
        variant: "destructive",
        title: "Login error",
        description: message,
      });
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="app-container login-page-container bg-white shadow-md">
        {/* Logo */}
        <div className="flex flex-col items-center">
          <img
            className="app-logo-auth mb-2"
            src={settings?.company_logo_url || "placeholder-logo.png"}
            alt={settings?.company_title || "Logo"}
          />

          <h2 className="login-welcome-text text-gray-700 w-full text-left">
            Welcome
          </h2>
        </div>

        {/* Form */}

        <form
          onSubmit={handleSubmit(onSubmit)}
          noValidate
          className="space-y-1"
        >
          {/* Email Field */}
          <div className="flex flex-col">
            <FloatingInput
              type="email"
              label="Email Address"
              inputClassName="login-input-field"
              placeholder="Please enter your email address..."
              {...register("email", {
                required: "Email is required",
                pattern: {
                  value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                  message: "Enter a valid email address",
                },
              })}
            />
              {errors.email && (
            <div className="h-5">
                <p className="text-red-500 text-[11px] ml-1">
                  {errors.email?.message}
                </p>
            </div>
              )}
          </div>

          <p className="text-xs text-gray-400 mb-4">
            Use your existing Aquavape login details.
          </p>

          {/* Password Field */}
          <div className="flex flex-col">
            <FloatingInput
              type="password"
              label="Password"
              inputClassName="login-input-field"
              placeholder="Please enter your password..."
              {...register("password", {
                required: "Password is required",
                minLength: {
                  value: 6,
                  message: "Password must be at least 6 characters",
                },
              })}
            />
              {errors.password && (
            <div className="h-5">
                <p className="text-red-500 text-[11px] ml-1">
                  {errors.password?.message}
                </p>
            </div>
              )}
          </div>

          <p className="login-legal-text">
            By selecting Login, you agree to our{" "}
            <a href="#" className="default-link">
              Terms &amp; Conditions
            </a>{" "}
            and{" "}
            <a href="#" className="default-link">
              Privacy Policy
            </a>
            .
          </p>

          <div className="h-6 text-center">
            {error && (
              <p className="text-red-600 text-sm font-medium">{error}</p>
            )}
          </div>

          {/* Buttons Group */}
          <div className="mt-4 flex flex-col items-center gap-3">
            <button
              type="submit"
              disabled={loading || isSubmitting}
              className="login-action-button cursor-pointer login-primary-button login-primary-button-text text-white shadow-lg active:scale-95 disabled:opacity-60 transition-all"
            >
              {loading ? "Signing in..." : "Log In"}
            </button>

            <button
              type="button"
              onClick={() => router.replace(buildPath("/landing"))}
              className="login-action-button cursor-pointer login-back-button-text border border-blue-500 text-blue-600 hover:bg-blue-50 transition-all"
            >
              Back
            </button>
          </div>
          {/* Links */}
          <div className="text-center text-sm space-y-6 mt-[60px]">
            <Link
              href={buildPath("/forgot-password")}
              className="login-forgot-link text-gray-700 block"
            >
              Forgotten your password?
            </Link>

            <Link
              href={buildPath("/forgot-email")}
              className="login-forgot-link text-gray-700 block"
            >
              Forgotten your email?
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
