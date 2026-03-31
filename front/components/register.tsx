"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { buildPath, getBasePath, publicAssetUrl } from "@/lib/utils";
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
    },
  });

  const [loading, setLoading] = useState(false);

  // Styles based on Figma
  const inputStyle =
    "w-full h-[50px] rounded-[5px] bg-[#FFFFFF] px-[16px] text-[14px] leading-[18px] text-[#3D495E] border border-[#4A90E5] focus:outline-none focus:ring-0 placeholder:text-[#A8AFBC] transition-all relative z-10";

  const sectionTitleStyle =
    "block w-full text-[#3D495E] font-semibold text-[14px] leading-[18px] text-left [font-family:Roboto] mb-2 relative z-10";
  const fieldLabelStyle = "block text-[#3D495E] text-[14px] leading-[18px] mb-1 pt-2";

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
        toast({ title: "Registration successful", description: "You can now log in." });
        setTimeout(() => router.replace(buildPath("/login")), 1200);
      } else {
        toast({ variant: "destructive", title: "Error", description: data?.message || "Registration failed" });
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
          };
          const target = map[field] || field;
          setFormError(target as any, { type: "server", message: msg });
        });
      }
      toast({ variant: "destructive", title: "Error", description: resp?.message || "Failed" });
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen flex justify-center bg-[#F2F5F9]">
      {/* Main Container with Background Image */}
      <div 
        className="relative w-[402px] h-[874px] bg-[#FAFBFD] flex flex-col overflow-hidden shadow-sm"
      >
        <img
          src={buildPath("/background.svg")}
          alt=""
          aria-hidden="true"
          className="absolute left-0 right-0 bottom-0 w-full object-cover pointer-events-none z-0"
          style={{ top: "215px", height: "calc(100% - 215px)" }}
          onError={(e) => {
            e.currentTarget.onerror = null;
            e.currentTarget.src = "/background.svg";
          }}
        />
        
        {/* Background Overlay Color (#4A90E50D) */}
        <div 
          className="absolute inset-0 pointer-events-none z-[1]" 
          style={{ backgroundColor: "rgba(250, 251, 253, 0.12)" }} 
        />

        {/* Fixed Header */}
        <div className="relative z-10 pt-[65px] pb-8 flex justify-center items-center">
          <Thumbnail
            height={23.41}
            containerClassName="w-[206.47px] max-w-[206.47px] mx-auto !bg-transparent"
          />
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="relative z-10 flex flex-1 flex-col overflow-hidden px-4">
          
          {/* Scrollable Area */}
          <div className="flex-1 overflow-y-auto pr-1 space-y-6 pb-6 no-scrollbar">
            
            <div className="rounded-[6px] p-4 space-y-3">
              <h3 className="text-[14px] font-semibold text-[#3D495E]">1. Business Address</h3>
              <input
                {...register("company", { required: "Company name is required" })}
                placeholder="Company Name"
                className={inputStyle}
              />
              {errors.company && <p className="text-red-500 text-[14px]">{errors.company.message as string}</p>}
              <input
                {...register("invoice1", { required: "Address line 1 is required" })}
                placeholder="Address Line 1"
                className={inputStyle}
              />
              {errors.invoice1 && <p className="text-red-500 text-[14px]">{errors.invoice1.message as string}</p>}
              <input {...register("invoice2")} placeholder="Address Line 2" className={inputStyle} />
              {errors.invoice2 && <p className="text-red-500 text-[14px]">{errors.invoice2.message as string}</p>}
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <input
                    {...register("city", { required: "City is required" })}
                    placeholder="Town / City"
                    className={inputStyle}
                  />
                  {errors.city && <p className="text-red-500 text-[14px]">{errors.city.message as string}</p>}
                </div>
                <div>
                  <input
                    {...register("postcode", { required: "Postcode is required" })}
                    placeholder="Postcode"
                    className={inputStyle}
                  />
                  {errors.postcode && <p className="text-red-500 text-[14px]">{errors.postcode.message as string}</p>}
                </div>
              </div>
              <input {...register("country")} placeholder="Country" className={inputStyle} />
            </div>

            <div className="rounded-[6px] p-4 space-y-3">
              <h3 className="text-[14px] font-semibold text-[#3D495E]">2. Company Details</h3>
              <input
                type="text"
                inputMode="text"
                autoComplete="tel"
                maxLength={20}
                placeholder="Phone Number"
                className={`${inputStyle} border-[#4A90E5] focus:border-[#4A90E5]`}
                style={{ border: "1px solid #4A90E5" }}
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
              {errors.mobile && <p className="text-red-500 text-[14px]">{errors.mobile.message as string}</p>}
              <input
                {...register("vatNumber")}
                placeholder="VAT Number (if applicable)"
                className={inputStyle}
              />
              {errors.vatNumber && <p className="text-red-500 text-[14px]">{errors.vatNumber.message as string}</p>}
              <input
                {...register("eoriNumber")}
                placeholder="EORI Number (if applicable)"
                className={inputStyle}
              />
              {errors.eoriNumber && <p className="text-red-500 text-[14px]">{errors.eoriNumber.message as string}</p>}
              <div>
                <label className={fieldLabelStyle}>
                  Are you part of a group? i.e. A symbol group or Industry Body, The FED etc.
                </label>
                <div className="relative">
                  <select
                    {...register("isPartOfGroup")}
                    className={`${inputStyle} appearance-none pr-12 z-0 cursor-pointer`}
                  >
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                  </select>
                  <span className="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[#3D495E] z-20 cursor-pointer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M6 9l6 6 6-6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                  </span>
                </div>
                {errors.isPartOfGroup && <p className="text-red-500 text-[14px]">{errors.isPartOfGroup.message as string}</p>}
              </div>
              <label className={fieldLabelStyle} style={{ fontSize: "14px", lineHeight: "18px", fontWeight: 500 }}>
                Type of business
              </label>
              <div className="relative">
                <select
                  {...register("businessType")}
                  className={`${inputStyle} appearance-none pr-12 z-0 cursor-pointer`}
                >
                  {["Wholesaler", "Distributor", "Retailer", "Online retailer", "Vape shop"].map((t) => (
                    <option key={t} value={t}>{t}</option>
                  ))}
                </select>
                <span className="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[#3D495E] z-20 cursor-pointer">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 9l6 6 6-6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                </span>
              </div>
              {errors.businessType && <p className="text-red-500 text-[14px]">{errors.businessType.message as string}</p>}
              <label className={fieldLabelStyle} style={{ fontSize: "14px", lineHeight: "18px", fontWeight: 500 }}>
                What is you average monthly spend excluding vat ?
              </label>
              <input
                type="text"
                min={0}
                step="0.01"
                {...register("averageMonthlySpendExVat")}
                className={`${inputStyle} border-[#4A90E5] focus:border-[#4A90E5]`}
                style={{ border: "1px solid #4A90E5" }}
              />
              {errors.averageMonthlySpendExVat && <p className="text-red-500 text-[14px]">{errors.averageMonthlySpendExVat.message as string}</p>}
              <label
                className={fieldLabelStyle}
                style={{ fontSize: "14px", lineHeight: "18px", fontWeight: 400 }}
              >
                How many stores do you have or how many stores do you service ?
              </label>
              <input
                type="number"
                min={0}
                step="1"
                {...register("storesServicedCount")}
                className={`${inputStyle} border-[#4A90E5] focus:border-[#4A90E5]`}
                style={{ border: "1px solid #4A90E5" }}
              />
              {errors.storesServicedCount && <p className="text-red-500 text-[14px]">{errors.storesServicedCount.message as string}</p>}
            </div>

            <div className="rounded-[6px] p-4 space-y-3">
              <h3 className="text-[14px] font-semibold text-[#3D495E]">3. User Details</h3>
              <input
                {...register("yourName")}
                placeholder="Your Name"
                className={inputStyle}
              />
              {errors.yourName && <p className="text-red-500 text-[14px]">{errors.yourName.message as string}</p>}
              <input
                type="email"
                {...register("email", { required: "Email is required" })}
                placeholder="Email Address"
                className={`${inputStyle} border-[#4A90E5] focus:border-[#4A90E5] focus:ring-1 focus:ring-[#4A90E5]`} style={{ border: "1px solid #4A90E5" }}
              />
              {errors.email && <p className="text-red-500 text-[14px]">{errors.email.message as string}</p>}
              <input
                type="password"
                {...register("password", { required: "Password is required", minLength: 6 })}
                placeholder="New Password"
                className={`${inputStyle} border-[#4A90E5] focus:border-[#4A90E5] focus:ring-1 focus:ring-[#4A90E5]`} style={{ border: "1px solid #4A90E5" }}
              />
              {errors.password && <p className="text-red-500 text-[14px]">{errors.password.message as string}</p>}
              <input
                type="password"
                {...register("confirmPassword", {
                  required: "Confirm password is required",
                  validate: (v, formValues: any) => v === formValues.password || "Passwords do not match",
                })}
                placeholder="Confirm Password"
                className={`${inputStyle} border-[#4A90E5] focus:border-[#4A90E5] focus:ring-1 focus:ring-[#4A90E5]`} style={{ border: "1px solid #4A90E5" }}
              />
              {errors.confirmPassword && <p className="text-red-500 text-[14px]">{errors.confirmPassword.message as string}</p>}
            </div>
          </div>

          {/* Fixed Footer Buttons */}
          <div className="py-6 bg-[#FAFBFD]/80 backdrop-blur-sm">
            <div className="flex flex-col gap-4">
              <button
                type="submit"
                disabled={loading || isSubmitting}
                className="w-full h-[48px] rounded-[25px] text-white font-[700px] text-[18px] transition-all active:scale-[0.98] disabled:opacity-70"
                style={{ background: "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)" }}
              >
                {loading ? "Registering..." : "Agree & Sign Up"}
              </button>

              <button
                type="button"
                onClick={() => router.replace(buildPath("/landing"))}
                className="w-full h-[48px] rounded-[25px] border border-[#4A90E5] bg-white text-[#4A90E5] font-[700px] text-[17px] transition-all active:scale-[0.98]"
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