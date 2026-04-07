"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { buildPath, publicAssetUrl } from "@/lib/utils";
import api from "@/lib/axios";
import { useForm } from "react-hook-form";
import { useToast } from "@/hooks/use-toast";
import { Thumbnail } from "./thumbnail";

function contactSetValueAs(v: unknown): string {
  return String(v ?? "")
    .replace(/[^a-zA-Z0-9]/g, "")
    .slice(0, 20);
}

export default function Register() {
  const router = useRouter();
  const { toast } = useToast();

  const {
    register,
    handleSubmit,
    setError: setFormError,
    formState: { errors, isSubmitting },
  } = useForm({
    mode: "onSubmit",
    defaultValues: {
      company: "",
      invoice1: "",
      invoice2: "",
      city: "",
      state: "",
      country: "",
      postcode: "",
      mobile: "",
      vatNumber: "",
      eoriNumber: "",
      isPartOfGroup: "yes",
      businessType: "",
      averageMonthlySpendExVat: "",
      storesServicedCount: "",
      yourName: "",
      email: "",
      password: "",
      confirmPassword: "",
      repCode: "",
    },
  });

  const [loading, setLoading] = useState(false);

  // Inputs: 1px solid #4A90E5 (Figma); labels: Roboto 500, 16px / 18px, #3D495E
  const inputStyle =
    "register-field relative z-10 box-border block h-[48px] w-full min-h-[48px] rounded-[6px] border border-solid border-[#4A90E5] bg-white px-4 text-[16px] leading-[22px] text-[#1e293b] outline-none transition-[border-color] placeholder:text-[#94A3B8] focus:border-[#2868C0] focus:ring-0 focus:ring-offset-0 [font-family:Roboto]";

  /** Figma "Company Name" etc.: Medium 500, 16px, line 18px, #3D495E */
  const sectionTitleStyle =
    "relative z-10 block min-h-[18px] w-full text-left text-[16px] font-medium leading-[18px] tracking-normal text-[#3D495E] [font-family:Roboto]";

  const fieldLabelStyle =
    "block text-[14px] font-normal leading-[18px] text-[#3D495E] [font-family:Roboto]";

  const detailLabelStyle =
    "block w-full text-left text-[16px] font-medium leading-[18px] tracking-normal text-[#3D495E] [font-family:Roboto]";

  const errorTextClass = "text-[13px] text-red-500 [font-family:Roboto]";

  async function onSubmit(values: any) {
    setLoading(true);
    try {
      const { data } = await api.post("/register", {
        companyName: values.company,
        email: values.email,
        mobile: values.mobile,
        password: values.password,
        addressLine1: values.invoice1,
        addressLine2: values.invoice2,
        city: values.city,
        state: values.state || undefined,
        country: values.country || undefined,
        zip_code: values.postcode,
        vatNumber: values.vatNumber || undefined,
        eoriNumber: values.eoriNumber || undefined,
        isPartOfGroup: values.isPartOfGroup,
        businessType: values.businessType,
        averageMonthlySpendExVat: values.averageMonthlySpendExVat,
        storesServicedCount: values.storesServicedCount,
        yourName: values.yourName,
      });

      if (data?.success) {
        toast({
          title: "Thank you",
          description:
            "Your application has been received and we will email you once your account has been approved.",
        });
        setTimeout(() => router.replace(buildPath("/login")), 3200);
      } else {
        toast({
          variant: "destructive",
          title: "Error",
          description: data?.message || "Registration failed",
        });
      }
    } catch (err: any) {
      const resp = err?.response?.data;
      if (resp?.errors) {
        Object.entries(resp.errors).forEach(([field, msgs]: any) => {
          const msg = Array.isArray(msgs) ? msgs[0] : String(msgs);
          const map: Record<string, string> = {
            companyName: "company",
            addressLine1: "invoice1",
            addressLine2: "invoice2",
            zip_code: "postcode",
            isPartOfGroup: "isPartOfGroup",
            businessType: "businessType",
            averageMonthlySpendExVat: "averageMonthlySpendExVat",
            storesServicedCount: "storesServicedCount",
            yourName: "yourName",
            email: "email",
          };
          const target = map[field] || field;
          setFormError(target as any, { type: "server", message: msg });
        });
      }
      toast({
        variant: "destructive",
        title: "Error",
        description: resp?.message || "Failed",
      });
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="flex h-[100dvh] max-h-[100dvh] min-h-0 w-full flex-col items-center justify-center overflow-hidden bg-[#FAFBFD] sm:bg-[#F2F5F9] sm:px-4">
      {/* Mobile: 402 full-bleed #FAFBFD; sm+: Figma 1000×1024, gap 24, pad top 30 bottom 12 */}
      <div className="relative flex h-[min(874px,100dvh)] w-full max-w-[402px] flex-col overflow-hidden bg-[#FAFBFD] max-sm:shadow-none max-sm:rounded-none sm:h-[min(1024px,100dvh)] sm:max-w-[1000px] sm:gap-6 sm:rounded-lg sm:shadow-sm sm:pt-[calc(30px+env(safe-area-inset-top,0px))] sm:pb-3">
        {/* Watermark: inline positioning so it always applies (Tailwind arbitrary % can miss in some builds) */}
        <div
          className="pointer-events-none sm:hidden"
          style={{
            position: "absolute",
            zIndex: 0,
            top: "182px",
            left: "25%",
            right: "50%",
            bottom: "calc(158px + env(safe-area-inset-bottom, 0px))",
            backgroundImage: `url('${publicAssetUrl("background.svg")}')`,
            backgroundRepeat: "no-repeat",
            backgroundSize: "contain",
            backgroundPosition: "right center",
          }}
        />
        <div
          className="pointer-events-none hidden sm:block"
          style={{
            position: "absolute",
            zIndex: 0,
            top: "104px",
            left: "25%",
            right: 0,
            bottom: "calc(124px + env(safe-area-inset-bottom, 0px))",
            backgroundImage: `url('${publicAssetUrl("background.svg")}')`,
            backgroundRepeat: "no-repeat",
            backgroundSize: "contain",
            backgroundPosition: "right center",
          }}
        />

        {/* Fixed header — sm+: Figma 30px top; mobile: pela jevu spacing */}
        <header className="relative z-10 flex shrink-0 justify-center px-6 pb-8 pt-[calc(65px+env(safe-area-inset-top,0px))] max-sm:pb-6 sm:px-10 sm:pb-0 sm:pt-0">
          <div className="sm:hidden">
            <Thumbnail
              height={23.41}
              containerClassName="mx-auto !bg-transparent w-[206.47px] max-w-[206.47px]"
            />
          </div>
          <div className="hidden sm:block">
            <Thumbnail
              height={28}
              containerClassName="mx-auto !bg-transparent w-[240px] max-w-[240px]"
            />
          </div>
        </header>

        <form
          id="register-page-form"
          onSubmit={handleSubmit(onSubmit)}
          className="register-form-fields relative z-10 flex min-h-0 flex-1 flex-col overflow-hidden px-4 sm:px-10"
        >
          {/* Middle — Figma gap 24px between sections; only this scrolls */}
          <div className="no-scrollbar min-h-0 flex-1 overflow-x-hidden overflow-y-auto pb-2 pr-1">
            <div className="mx-auto flex w-full max-w-[700px] flex-col gap-6">
              <section className="flex flex-col gap-2">
                <h3 className={sectionTitleStyle}>Company Name</h3>
                <input
                  {...register("company", {
                    required: "Company name is required",
                  })}
                  placeholder="Please enter your company name"
                  className={inputStyle}
                />
                {errors.company && (
                  <p className={errorTextClass}>
                    {errors.company.message as string}
                  </p>
                )}
              </section>

              <section className="flex flex-col gap-2">
                <h3 className={sectionTitleStyle}>Company Address</h3>
                <input
                  {...register("invoice1", {
                    required: "Address line 1 is required",
                  })}
                  placeholder="Invoice address line 1"
                  className={inputStyle}
                />
                {errors.invoice1 && (
                  <p className={errorTextClass}>
                    {errors.invoice1.message as string}
                  </p>
                )}
                <input
                  {...register("invoice2")}
                  placeholder="Invoice address line 2"
                  className={inputStyle}
                />
                <input
                  {...register("city", { required: "City is required" })}
                  placeholder="Invoice address city"
                  className={inputStyle}
                />
                {errors.city && (
                  <p className={errorTextClass}>
                    {errors.city.message as string}
                  </p>
                )}
                <input
                  {...register("state")}
                  placeholder="Invoice address county"
                  className={inputStyle}
                />
                <input
                  {...register("postcode", {
                    required: "Postcode is required",
                  })}
                  placeholder="Invoice address postcode"
                  className={inputStyle}
                />
                {errors.postcode && (
                  <p className={errorTextClass}>
                    {errors.postcode.message as string}
                  </p>
                )}
                <input
                  {...register("country")}
                  placeholder="Country (if applicable)"
                  className={inputStyle}
                />
              </section>

              <section className="flex flex-col gap-2">
                <h3 className={sectionTitleStyle}>Contact Number</h3>
                <input
                  type="text"
                  inputMode="text"
                  autoComplete="tel"
                  maxLength={20}
                  placeholder="Please enter your contact number"
                  className={inputStyle}
                  onKeyDown={(e) => {
                    if (e.ctrlKey || e.metaKey || e.altKey) return;
                    if (e.key.length === 1 && !/^[a-zA-Z0-9]$/.test(e.key)) {
                      e.preventDefault();
                    }
                  }}
                  {...register("mobile", {
                    required: "Contact number is required",
                    setValueAs: contactSetValueAs,
                    minLength: {
                      value: 10,
                      message: "Contact must be at least 10 characters.",
                    },
                    maxLength: {
                      value: 20,
                      message: "Contact must be at most 20 characters.",
                    },
                    pattern: {
                      value: /^[a-zA-Z0-9]+$/,
                      message: "Contact must contain only letters and numbers.",
                    },
                  })}
                />
                {errors.mobile && (
                  <p className={errorTextClass}>
                    {errors.mobile.message as string}
                  </p>
                )}
              </section>

              <section className="flex flex-col gap-2">
                <h3 className={sectionTitleStyle}>
                  Additional company details
                </h3>
                <input
                  {...register("vatNumber")}
                  placeholder="VAT Number (if applicable)"
                  className={inputStyle}
                />
                {errors.vatNumber && (
                  <p className={errorTextClass}>
                    {errors.vatNumber.message as string}
                  </p>
                )}
                <input
                  {...register("eoriNumber")}
                  placeholder="EORI Number (if applicable)"
                  className={inputStyle}
                />
                <div>
                  <label className={detailLabelStyle}>
                    Are you part of a group? i.e. A symbol group or Industry
                    Body, The FED etc.
                  </label>
                  <div className="relative mt-1">
                    <select
                      {...register("isPartOfGroup")}
                      className={`${inputStyle} z-0 cursor-pointer appearance-none pr-12`}
                    >
                      <option value="yes">Yes</option>
                      <option value="no">No</option>
                    </select>
                    <span className="pointer-events-none absolute right-4 top-1/2 z-20 -translate-y-1/2 text-[#3D495E]">
                      <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                      >
                        <path
                          d="M6 9l6 6 6-6"
                          stroke="currentColor"
                          strokeWidth="2"
                          strokeLinecap="round"
                          strokeLinejoin="round"
                        />
                      </svg>
                    </span>
                  </div>
                </div>
                <label className={detailLabelStyle}>Type of business</label>
                <div className="relative">
                  <select
                    {...register("businessType")}
                    className={`${inputStyle} z-0 cursor-pointer appearance-none pr-12`}
                  >
                    {[
                      "Wholesaler",
                      "Distributor",
                      "Retailer",
                      "Online retailer",
                      "Vape shop",
                    ].map((t) => (
                      <option key={t} value={t}>
                        {t}
                      </option>
                    ))}
                  </select>
                  <span className="pointer-events-none absolute right-4 top-1/2 z-20 -translate-y-1/2 text-[#3D495E]">
                    <svg
                      width="14"
                      height="14"
                      viewBox="0 0 24 24"
                      fill="none"
                      aria-hidden="true"
                    >
                      <path
                        d="M6 9l6 6 6-6"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                      />
                    </svg>
                  </span>
                </div>
                {errors.businessType && (
                  <p className={errorTextClass}>
                    {errors.businessType.message as string}
                  </p>
                )}
                <label className={detailLabelStyle}>
                  What is you average monthly spend excluding vat ?
                </label>
                <input
                  type="text"
                  min={0}
                  step="0.01"
                  {...register("averageMonthlySpendExVat")}
                  className={inputStyle}
                />
                <label className={detailLabelStyle}>
                  How many stores do you have or how many stores do you service
                  ?
                </label>
                <input
                  type="number"
                  min={0}
                  step="1"
                  {...register("storesServicedCount")}
                  className={inputStyle}
                />
              </section>

              <section className="flex flex-col gap-2">
                <h3 className={sectionTitleStyle}>Login Details</h3>
                <input
                  {...register("yourName")}
                  placeholder="Your name"
                  className={inputStyle}
                />
                {errors.yourName && (
                  <p className={errorTextClass}>
                    {errors.yourName.message as string}
                  </p>
                )}
                <input
                  type="email"
                  {...register("email", { required: "Email is required" })}
                  placeholder="Please enter your email address"
                  className={inputStyle}
                />
                {errors.email && (
                  <p className={errorTextClass}>
                    {errors.email.message as string}
                  </p>
                )}
                <input
                  type="password"
                  {...register("password", {
                    required: "Password is required",
                    minLength: 6,
                  })}
                  placeholder="Please enter your password"
                  className={inputStyle}
                />
                {errors.password && (
                  <p className={errorTextClass}>
                    {errors.password.message as string}
                  </p>
                )}
                <input
                  type="password"
                  {...register("confirmPassword", {
                    required: "Confirm password is required",
                    validate: (v, formValues: any) =>
                      v === formValues.password || "Passwords do not match",
                  })}
                  placeholder="Please confirm your password"
                  className={inputStyle}
                />
                {errors.confirmPassword && (
                  <p className={errorTextClass}>
                    {errors.confirmPassword.message as string}
                  </p>
                )}
              </section>

              <section className="flex flex-col gap-2">
                <h3 className={sectionTitleStyle}>Rep Code</h3>
                <input
                  {...register("repCode")}
                  placeholder="Please enter your rep code"
                  className={inputStyle}
                />
              </section>

              <p className="text-left text-[11px] font-normal leading-[18px] text-[#3D495E] [font-family:Roboto]">
                By registering an account you agree to our{" "}
                <a
                  href="#"
                  className="text-[#4A90E5] underline underline-offset-2"
                >
                  Terms & Conditions
                </a>{" "}
                and{" "}
                <a
                  href="#"
                  className="text-[#4A90E5] underline underline-offset-2"
                >
                  Privacy Policy
                </a>
                .
              </p>
            </div>
          </div>

          {/* Fixed footer — Figma bottom area; sm+ 12px frame padding respected via pb-3 on frame */}
          <div className="relative z-10 shrink-0 bg-[#FAFBFD]/80 px-1 py-6 backdrop-blur-sm pb-[calc(24px+env(safe-area-inset-bottom,0px))] sm:bg-[#FAFBFD] sm:px-2 sm:py-4 sm:backdrop-blur-none sm:pb-[calc(12px+env(safe-area-inset-bottom,0px))]">
            <div className="mx-auto flex w-full max-w-[466px] flex-col gap-[11px]">
              <button
                type="submit"
                form="register-page-form"
                disabled={loading || isSubmitting}
                className="mx-auto box-border flex h-[48px] w-full max-w-[466px] items-center justify-center rounded-[25px] border-0 px-[26px] py-0 text-[16px] font-bold leading-none text-white transition-all [font-family:Roboto] active:scale-[0.98] disabled:opacity-70 sm:text-[18px]"
                style={{
                  background:
                    "linear-gradient(180deg, #4C92E9 0%, #2868C0 100%)",
                }}
              >
                {loading ? "Registering..." : "Agree & Sign Up"}
              </button>

              <button
                type="button"
                onClick={() => router.replace(buildPath("/landing"))}
                className="mx-auto box-border flex h-[48px] w-full max-w-[466px] items-center justify-center rounded-[25px] border border-solid border-[#4A90E5] bg-white px-[26px] py-0 text-[16px] font-bold leading-none text-[#4A90E5] transition-all [font-family:Roboto] active:scale-[0.98] sm:text-[17px]"
              >
                Back
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
}
