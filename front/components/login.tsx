"use client";

import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm } from "react-hook-form";
import Link from "next/link";
import { buildPath, publicAssetUrl } from "@/lib/utils";
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
  }, [toast]);

  useEffect(() => {
    const reason = searchParams.get("reason");
    if (!reason) return;
    const messages: Record<
      string,
      { title: string; description: string; variant: "default" | "destructive" }
    > = {
      inactive: {
        variant: "destructive",
        title: "Account deactivated",
        description:
          "Your account has been deactivated. Please contact support if you need access again.",
      },
      rejected: {
        variant: "destructive",
        title: "Registration not approved",
        description: "Your registration was not approved. Please contact support if you have questions.",
      },
      pending: {
        variant: "destructive",
        title: "Account pending approval",
        description: "Your account is not approved yet. Please wait for an administrator to approve your registration.",
      },
      session: {
        variant: "default",
        title: "Session ended",
        description: "Please sign in again to continue.",
      },
    };
    const m = messages[reason];
    if (m) {
      toast({ variant: m.variant, title: m.title, description: m.description });
    }
    router.replace(buildPath("/login"));
  }, [searchParams, router, toast]);

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
      const data = err?.response?.data;
      let message = data?.message || "Invalid credentials";
      if (data?.code === "not_approved") {
        message =
          typeof data?.message === "string" && data.message.trim()
            ? data.message
            : "Your account is not approved yet. Please wait for an administrator to approve your registration before signing in.";
      } else if (data?.code === "rejected") {
        message =
          typeof data?.message === "string" && data.message.trim()
            ? data.message
            : "Your registration was not approved. Please contact support if you have questions.";
      }
      setError(message);
    } finally {
      setLoading(false);
    }
  }

  return (
     <div className="flex h-[100dvh] w-full justify-center overflow-hidden bg-[#F2F5F9]">
      <div
        className="relative flex h-full min-h-0 w-full max-w-[1000px] flex-col overflow-hidden bg-[#FAFBFD] shadow-sm"
        style={{ backgroundColor: "#FAFBFD" }}
      >
        {/* Background watermark (Figma: 1004 x 812) */}
        <div
          className="pointer-events-none absolute left-[calc(50%+170px)] top-[95px] z-0 hidden h-[812px] w-[1004px] -translate-x-1/2 lg:block"
          style={{
            backgroundImage: "url('/background.svg')",
            backgroundRepeat: "no-repeat",
            backgroundSize: "1004px 812px",
            backgroundPosition: "center top",
          }}
        />

        <div className="relative z-10 flex min-h-0 flex-1 w-full flex-col items-center overflow-y-auto px-4 pb-[24px] pt-4 lg:px-[16px]">
          {/* Logo Section */}
          <div className="mb-8 flex w-full max-w-[477px] flex-col items-center lg:max-w-[700px]">
            <img
              className="block h-[151px] w-[214px] min-h-[151px] min-w-[214px] max-h-[151px] max-w-[214px] object-contain"
              src={settings?.company_logo_url || "/assets/img/logo.png"}
              alt={settings?.company_title || "Logo"}
            />
            <h2 className="login-welcome-text mt-3 w-full text-left text-[#3D495E]">
              Welcome
            </h2>
          </div>

          {/* Form Section */}
          <form onSubmit={handleSubmit(onSubmit)} noValidate className="w-full max-w-[477px] space-y-5 lg:max-w-[700px]">
            {/* Email Field */}
            <div className="mb-6 flex w-full flex-col gap-2 lg:max-w-[700px]">
              <FloatingInput
                type="email"
                label="Email Address"
                labelClassName="block w-full max-w-[700px] text-left text-[16px] font-[500] leading-[18px] tracking-[0px] text-[#3D495E] [font-family:Roboto]"
                inputClassName="box-border h-[50px] min-h-[50px] w-full max-w-[700px] rounded-[5px] !border !border-solid !border-[#4A90E5] bg-[#FFFFFF] !py-[16px] !px-[24px] text-[16px] leading-[18px] text-[#3D495E] outline-none transition focus:!border-[#4A90E5] focus:!ring-0"
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
              <p className="h-[18px] w-[354px] max-w-full text-[13px] font-[300] leading-[18px] tracking-[0px] text-[#3D495E] [font-family:Roboto]">
                Use your existing Aquavape login details.
              </p>
            </div>

            {/* Password Field */}
            <div className="flex w-full max-w-[700px] flex-col gap-2">
              <FloatingInput
                type="password"
                label="Password"
                labelClassName="text-[16px] font-medium text-[#3D495E] [font-family:Roboto]"
                inputClassName="box-border h-[50px] min-h-[50px] w-full max-w-[700px] rounded-[5px] !border !border-solid !border-[#4A90E5] bg-[#FFFFFF] !py-[16px] !pl-[24px] !pr-12 text-[16px] leading-[18px] text-[#3D495E] outline-none transition focus:!border-[#4A90E5] focus:!ring-0"
                placeholder="Please enter your password..."
                error={errors.password?.message}
                {...register("password", {
                  required: "Password is required",
                  minLength: { value: 6, message: "Password must be at least 6 characters" },
                })}
              />
            </div>

            {/* Legal Text */}
            <p className="my-5 w-full max-w-[700px] text-left text-[11px] font-normal leading-[18px] tracking-[0px] text-[#3D495E] [font-family:Roboto]">
              By selecting Login, you agree to our{" "}
              <a href="#" className="text-[11px] font-normal leading-[18px] text-[#4A90E5] underline decoration-solid underline-offset-0">
                Terms & Conditions
              </a>{" "}
              and{" "}
              <a href="#" className="text-[11px] font-normal leading-[18px] text-[#4A90E5] underline decoration-solid underline-offset-0">
                Privacy Policy
              </a>
              .
            </p>

            {/* Error Message */}
            {error && (
              <div className="text-center">
                <p className="text-xs font-semibold text-red-500">{error}</p>
              </div>
            )}

            {/* Buttons Group */}
            <div className="flex flex-col gap-4 py-[13px]">
              <button
                type="submit"
                disabled={loading || isSubmitting}
                suppressHydrationWarning
                className="mx-auto flex h-[48px] w-full max-w-[477px] items-center justify-center gap-[11px] rounded-[25px] px-[26px] py-[15px] text-center text-[20px] font-[500] leading-[18px] text-white shadow-lg transition-all active:scale-[0.98] disabled:opacity-50"
                style={{ background: "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)" }}
              >
                {loading ? "Signing in..." : "Log In"}
              </button>

              <button
                type="button"
                onClick={() => router.replace(buildPath("/landing"))}
                suppressHydrationWarning
                className="mx-auto flex h-[48px] w-full max-w-[477px] items-center justify-center gap-[11px] rounded-[25px] border border-[#4A90E5] bg-white px-[26px] py-[15px] text-center text-[20px] font-[500] leading-[18px] text-[#4A90E5] transition-all active:scale-[0.98]"
              >
                Back
              </button>
            </div>

            {/* Footer Links */}
            <div className="flex flex-col items-center gap-6 pt-6">
              <Link
                href={buildPath("/forgot-password")}
                className="w-[152px] text-center text-[13px] font-[700] leading-[18px] tracking-[0px] text-[#3D495E] underline decoration-solid underline-offset-0 [font-family:roboto]"
              >
                Forgotten your password?
              </Link>
{/* 
              <Link
                href={buildPath("/forgot-email")}
                className="w-[128px] text-center text-[13px] font-[700] leading-[18px] tracking-[0px] text-[#3D495E] underline decoration-solid underline-offset-0 [font-family:roboto]"
              >
                Forgotten your email?
              </Link> */}
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}