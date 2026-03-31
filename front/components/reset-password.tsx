"use client";

import { useEffect, useState, Suspense } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm } from "react-hook-form";
import Link from "next/link";
import { buildPath, publicAssetUrl } from "@/lib/utils";
import api from "@/lib/axios";
import { useSettings } from "@/components/settings-provider";
import FloatingInput from "@/components/ui/floating-input";
import { useToast } from "@/hooks/use-toast";

function ResetPasswordForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { settings } = useSettings();
  const { toast } = useToast();
  const token = searchParams.get("token") || "";
  const emailParam = searchParams.get("email") || "";

  const {
    register,
    handleSubmit,
    setValue,
    formState: { errors, isSubmitting },
  } = useForm<{ email: string; password: string; password_confirmation: string }>({
    mode: "onSubmit",
    reValidateMode: "onChange",
    defaultValues: { email: "", password: "", password_confirmation: "" },
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (emailParam) {
      setValue("email", decodeURIComponent(emailParam));
    }
  }, [emailParam, setValue]);

  useEffect(() => {
    if (!token || !emailParam) {
      setError("Invalid or expired reset link. Please request a new password reset.");
    }
  }, [token, emailParam]);

  async function onSubmit(values: {
    email: string;
    password: string;
    password_confirmation: string;
  }) {
    if (!token) {
      setError("Invalid reset link.");
      return;
    }
    setError(null);
    setLoading(true);
    try {
      const { data } = await api.post("/reset-password", {
        token,
        email: values.email,
        password: values.password,
        password_confirmation: values.password_confirmation,
      });
      if (data?.success) {
        toast({
          title: "Password updated",
          description: data?.message || "You can log in with your new password.",
        });
        router.replace(buildPath("/login"));
      } else {
        setError(data?.message || "Unable to reset password.");
      }
    } catch (err: unknown) {
      const res = (err as { response?: { data?: { message?: string } } })?.response?.data;
      setError(res?.message || "Unable to reset password. Please try again.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen flex justify-center">
      <div
        className="relative w-[402px] min-h-[750px] pr-[16px] pl-4 flex flex-col gap-8 overflow-hidden pb-8"
        style={{
          backgroundImage: `url('${publicAssetUrl("background.svg")}')`,
          backgroundRepeat: "no-repeat",
          backgroundSize: "807px 652px",
          backgroundPosition: "center 180px",
          backgroundBlendMode: "multiply",
        }}
      >
        <div className="absolute inset-0 z-0 pointer-events-none" />

        <div className="relative z-10 flex flex-col items-center mb-4">
          <img
            className="app-logo-auth block mx-auto mb-8 h-[151px] w-[214px] object-contain"
            src={settings?.company_logo_url || "/assets/img/logo.png"}
            alt={settings?.company_title || "Logo"}
          />
          <h2 className="w-full text-left text-[24px] leading-[18px] font-medium text-[#3D495E] mt-2 [font-family:Roboto]">
            Set new password
          </h2>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} noValidate className="relative z-10 space-y-5">
          <div className="flex w-full flex-col gap-2 mb-2">
            <FloatingInput
              type="email"
              label="Email Address"
              labelClassName="text-[16px] font-medium text-[#3D495E] [font-family:Roboto]"
              inputClassName="h-[48px] rounded-[5px] border !border-[#4A90E5] bg-white px-[24px] focus:!ring-0"
              placeholder="Your account email"
              error={errors.email?.message}
              {...register("email", {
                required: "Email is required",
                pattern: {
                  value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                  message: "Enter a valid email address",
                },
              })}
            />
          </div>

          <div className="flex w-full flex-col gap-2 mb-2">
            <FloatingInput
              type="password"
              label="New password"
              labelClassName="text-[16px] font-medium text-[#3D495E] [font-family:Roboto]"
              inputClassName="h-[48px] rounded-[5px] border !border-[#4A90E5] bg-white px-[24px] focus:!ring-0"
              placeholder="At least 6 characters"
              error={errors.password?.message}
              {...register("password", {
                required: "Password is required",
                minLength: { value: 6, message: "Password must be at least 6 characters" },
              })}
            />
          </div>

          <div className="flex w-full flex-col gap-2 mb-2">
            <FloatingInput
              type="password"
              label="Confirm new password"
              labelClassName="text-[16px] font-medium text-[#3D495E] [font-family:Roboto]"
              inputClassName="h-[48px] rounded-[5px] border !border-[#4A90E5] bg-white px-[24px] focus:!ring-0"
              placeholder="Repeat password"
              error={errors.password_confirmation?.message}
              {...register("password_confirmation", {
                required: "Please confirm your password",
                validate: (val, form) =>
                  val === form.password || "Passwords do not match",
              })}
            />
          </div>

          {error && (
            <div className="text-center">
              <p className="text-red-500 text-xs font-semibold">{error}</p>
            </div>
          )}

          <div className="flex flex-col gap-4 py-[13px]">
            <button
              type="submit"
              disabled={loading || isSubmitting || !token}
              className="w-full h-[48px] rounded-[25px] text-white font-[700] text-[20px] shadow-lg active:scale-[0.98] transition-all disabled:opacity-50"
              style={{ background: "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)" }}
            >
              {loading ? "Saving..." : "Update password"}
            </button>

            <button
              type="button"
              onClick={() => router.replace(buildPath("/login"))}
              className="w-full h-[48px] bg-white border border-[#4A90E5] text-[#4A90E5] rounded-[25px] font-[700] text-[17px] active:scale-[0.98] transition-all"
            >
              Back to login
            </button>
          </div>

          <div className="flex flex-col items-center gap-6 pt-6">
            <Link
              href={buildPath("/forgot-password")}
              className="text-[#3D495E] text-[13px] font-medium underline [font-family:Roboto]"
              style={{ fontWeight: 500 }}
            >
              Request a new link
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}

export default function ResetPassword() {
  return (
    <Suspense fallback={<div className="min-h-screen flex justify-center items-center">Loading…</div>}>
      <ResetPasswordForm />
    </Suspense>
  );
}
