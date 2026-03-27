"use client";

import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm } from "react-hook-form";
import Link from "next/link";
import { buildPath } from "@/lib/utils";
import api from "@/lib/axios";
import { refreshProductsCacheAfterLogin } from "@/lib/products-cache";
import { useSettings } from "@/components/settings-provider";
import FloatingInput from "@/components/ui/floating-input";
import { useToast } from "@/hooks/use-toast";
import { useCustomer } from "@/components/customer-provider";

export default function Login() {
  const router = useRouter();
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
    } catch { }
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
        try {
          const ver = Number(data?.versions?.Customer || 0);
          if (!Number.isNaN(ver) && ver > 0) {
            sessionStorage.setItem("customer_cache_version", String(ver));
          }
        } catch { }

        try {
          await refreshProductsCacheAfterLogin(data?.versions);
        } catch { }

        await refresh();
        try { await refreshSettings(); } catch { }
        
        toast({
          title: "Hello there 👋",
          description: "You've logged in successfully.",
        });
        window.location.replace(buildPath("/"));
      } else {
        const message = data?.message || "Login failed";
        setError(message);
      }
    } catch (err: any) {
      const message = err?.response?.data?.message || "Invalid credentials";
      setError(message);
    } finally {
      setLoading(false);
    }
  }

  return (
    // <div className="min-h-screen flex justify-center bg-[#F2F5F9]">
      <div className="min-h-screen flex justify-center">
      <div 
        className="relative w-[402px] h-[750px] pr-[16px] pl-4 flex flex-col gap-8 overflow-hidden"
        style={{
          // backgroundColor: "#FAFBFD",
          backgroundImage: "url('./background.svg')",
          backgroundRepeat: "no-repeat",
          backgroundSize: "807px 652px", 
          backgroundPosition: "center 180px",
          backgroundBlendMode: "multiply",
        }}
      >
        
        {/* Figma Overlay Color (#6497EA0D) */}
        <div 
          className="absolute inset-0 z-0 pointer-events-none" 
          // style={{ backgroundColor: "rgba(100, 151, 234, 0.05)" }} 
        />

        {/* Logo Section */}
        <div className="relative z-10 flex flex-col items-center mb-4">
          <img
            className="app-logo-auth block mx-auto mb-8 h-[151px] w-[214px] object-contain"
            src={settings?.company_logo_url || "/assets/img/logo.png"}
            alt={settings?.company_title || "Logo"}
          />
          <h2 className="w-full text-left text-[24px] leading-[18px] font-medium text-[#3D495E] mt-2 [font-family:Roboto]">
            Welcome
          </h2>
        </div>

        {/* Form Section */}
        <form
          onSubmit={handleSubmit(onSubmit)}
          noValidate
          className="relative z-10 space-y-5"
        >
          {/* Email Field */}
          <div className="flex w-full flex-col gap-2 mb-6">
            <FloatingInput
              type="email"
              label="Email Address" 
              labelClassName="text-[16px] font-medium text-[#3D495E] [font-family:Roboto]"
              inputClassName="h-[48px] rounded-[5px] border !border-[#4A90E5] bg-white px-[24px] focus:!ring-0"
              placeholder="Please enter your email..."
              error={errors.email?.message}
              {...register("email", {
                required: "Email is required",
                pattern: {
                  value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                  message: "Enter a valid email address",
                },
              })}
            />
            <p className="text-[13px] text-[#3D495E] font-light [font-family:Roboto]">
              Use your existing Aquavape login details.
            </p>
          </div>

          {/* Password Field */}
          <div className="flex w-full flex-col gap-2 mb-1">
            <FloatingInput
              type="password"
              label="Password"
              labelClassName="text-[16px] font-medium text-[#3D495E] [font-family:Roboto]"
              inputClassName="h-[48px] rounded-[5px] border !border-[#4A90E5] bg-white px-[24px] focus:!ring-0"
              placeholder="Please enter your password..."
              error={errors.password?.message}
              {...register("password", {
                required: "Password is required",
                minLength: { value: 6, message: "Password must be at least 6 characters" },
              })}
            />
          </div>

          {/* Legal Text */}
          <p className="my-4 text-[11px] leading-[18px] text-[#3D495E] [font-family:Roboto]">
            By selecting Login, you agree to our{" "}
            <a href="#" className="text-[#4A90E5] underline">Terms & Conditions</a> and <a href="#" className="text-[#4A90E5] underline">Privacy Policy</a>.
          </p>

          {/* Error Message */}
          {error && (
            <div className="text-center">
              <p className="text-red-500 text-xs font-semibold">{error}</p>
            </div>
          )}

          {/* Buttons Group */}
          <div className="flex flex-col gap-4 py-[13px]">
            <button
              type="submit"
              disabled={loading || isSubmitting}
              className="w-full h-[48px] rounded-[25px] text-white font-[700] text-[20px] shadow-lg active:scale-[0.98] transition-all disabled:opacity-50"
              style={{ background: "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)" }}
            >
              {loading ? "Signing in..." : "Log In"}
            </button>

            <button
              type="button"
              onClick={() => router.replace(buildPath("/landing"))}
              className="w-full h-[48px] bg-white border border-[#4A90E5] text-[#4A90E5] rounded-[25px] font-[700] text-[17px] active:scale-[0.98] transition-all"
            >
              Back
            </button>
          </div>

          {/* Footer Links */}
         <div className="flex flex-col items-center gap-6 pt-6">
  <Link 
    href={buildPath("/forgot-password")} 
    className="text-[#3D495E] text-[13px] font-medium underline [font-family:Roboto]"
    style={{ fontWeight: 500 }} 
  >
    Forgotten your password?
  </Link>
  
  {/* <Link 
    href={buildPath("/forgot-email")} 
    className="text-[#3D495E] text-[13px] font-medium underline [font-family:Roboto]"
    style={{ fontWeight: 500 }} 
  >
    Forgotten your email?
  </Link> */}
</div>
        </form>
      </div>
    </div>
  );
}