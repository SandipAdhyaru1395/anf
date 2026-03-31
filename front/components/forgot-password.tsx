"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import Link from "next/link";
import { buildPath, publicAssetUrl } from "@/lib/utils";
import api from "@/lib/axios";
import { useSettings } from "@/components/settings-provider";
import FloatingInput from "@/components/ui/floating-input";
import { useToast } from "@/hooks/use-toast";

export default function ForgotPassword() {
  const router = useRouter();
  const { settings } = useSettings();
  const { toast } = useToast();
  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<{ email: string }>({
    mode: "onSubmit",
    reValidateMode: "onChange",
    defaultValues: { email: "" },
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(values: { email: string }) {
    setError(null);
    setLoading(true);
    try {
      const { data } = await api.post("/forgot-password", { email: values.email });
      if (data?.success) {
        toast({
          title: "Check your email",
          description: data?.message || "If an account exists, we sent reset instructions.",
        });
        router.replace(buildPath("/login"));
      } else {
        setError(data?.message || "Please try again.");
      }
    } catch (err: unknown) {
      const message =
        (err as { response?: { data?: { message?: string } } })?.response?.data?.message ||
        "Something went wrong.";
      setError(message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen flex justify-center">
      <div
        className="relative w-[402px] pr-[16px] pl-4 flex flex-col gap-8 overflow-hidden"
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
            Forgot password
          </h2>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} noValidate className="relative z-10 space-y-5">
          <div className="flex w-full flex-col gap-2 mb-6">
            <FloatingInput
              type="email"
              label="Email Address"
              labelClassName="text-[16px] font-medium text-[#3D495E] [font-family:Roboto]"
              inputClassName="h-[48px] rounded-[5px] border !border-[#4A90E5] bg-white px-[24px] focus:!ring-0"
              placeholder="Enter your account email..."
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
              We will send you a link to reset your password.
            </p>
          </div>

          {error && (
            <div className="text-center">
              <p className="text-red-500 text-xs font-semibold">{error}</p>
            </div>
          )}

          <div className="flex flex-col gap-4 py-[13px]">
            <button
              type="submit"
              disabled={loading || isSubmitting}
              className="w-full h-[48px] rounded-[25px] text-white font-[700] text-[20px] shadow-lg active:scale-[0.98] transition-all disabled:opacity-50"
              style={{ background: "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)" }}
            >
              {loading ? "Sending..." : "Send reset link"}
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
              href={buildPath("/register")}
              className="text-[#3D495E] text-[13px] font-medium underline [font-family:Roboto]"
              style={{ fontWeight: 500 }}
            >
              Create an account
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
