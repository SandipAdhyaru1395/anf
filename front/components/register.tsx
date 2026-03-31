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
      email: "",
      password: "",
    },
  });

  const [loading, setLoading] = useState(false);

  // Styles based on Figma
  const inputStyle =
    "w-full h-[50px] rounded-[5px] bg-[#FFFFFF] px-[16px] text-[14px] leading-[18px] text-[#3D495E] border border-[#4A90E5] focus:outline-none focus:ring-0 placeholder:text-[#A8AFBC] transition-all relative z-10";

  const sectionTitleStyle =
    "block w-full text-[#3D495E] font-semibold text-[16px] leading-[18px] text-left [font-family:Roboto] mb-2 relative z-10";

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
        style={{
          backgroundImage: `url('${publicAssetUrl("background.svg")}')`,
          backgroundRepeat: "no-repeat",
          backgroundSize: "807.18px 652.86px", // Figma Dimensions
          backgroundPosition: "center 200px", // ઈમેજને થોડી નીચે સેટ કરી છે
        }}
      >
        
        {/* Background Overlay Color (#4A90E50D) */}
        <div 
          className="absolute inset-0 pointer-events-none" 
          // style={{ backgroundColor: "rgba(74, 144, 229, 0.05)" }} 
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
            
            {/* Company Name */}
            <div className="flex flex-col gap-1">
              <label className={sectionTitleStyle} style={{fontWeight:"500"}}>Company Name</label>
              <input
                {...register("company", { required: "Company name is required" })}
                placeholder="Please enter your company name"
                className={inputStyle}
              />
              {errors.company && <p className="text-red-500 text-[10px]">{errors.company.message as string}</p>}
            </div>

            {/* Company Address */}
            <div className="flex flex-col gap-1">
              <label className={sectionTitleStyle} style={{fontWeight:"500"}}>Company Address</label>
              <div className="flex flex-col gap-3">
                <input
                  {...register("invoice1", { required: "Address line 1 is required" })}
                  placeholder="Invoice address line 1"
                  className={inputStyle}
                />
                {errors.invoice1 && <p className="text-red-500 text-[10px]">{errors.invoice1.message as string}</p>}
                <input {...register("invoice2")} placeholder="Invoice address line 2" className={inputStyle} />
                {errors.invoice2 && <p className="text-red-500 text-[10px]">{errors.invoice2.message as string}</p>}
                <input
                  {...register("city", { required: "City is required" })}
                  placeholder="Invoice address city"
                  className={inputStyle}
                />
                {errors.city && <p className="text-red-500 text-[10px]">{errors.city.message as string}</p>}
                <input {...register("state")} placeholder="Invoice address county" className={inputStyle} />
                {errors.state && <p className="text-red-500 text-[10px]">{errors.state.message as string}</p>}
                <input
                  {...register("postcode", { required: "Postcode is required" })}
                  placeholder="Invoice address postcode"
                  className={inputStyle}
                />
                {errors.postcode && <p className="text-red-500 text-[10px]">{errors.postcode.message as string}</p>}
              </div>
            </div>

            {/* Contact Number */}
            <div className="flex flex-col gap-1">
              <label className={sectionTitleStyle} style={{fontWeight:"500"}}>Contact Number</label>
              <input
                type="text"
                inputMode="text"
                autoComplete="tel"
                maxLength={20}
                placeholder="10–20 letters or numbers"
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
              {errors.mobile && <p className="text-red-500 text-[10px]">{errors.mobile.message as string}</p>}
            </div>

            {/* Login Details */}
<div className="flex flex-col gap-1">
  <label className={sectionTitleStyle} style={{ fontWeight: "500" }}>
    Login Details
  </label>
  <div className="flex flex-col gap-3">
    <input
      type="email"
      {...register("email", { required: "Email is required" })}
      placeholder="Please enter your email"
      className={`${inputStyle} border-[#4A90E5] focus:border-[#4A90E5] focus:ring-1 focus:ring-[#4A90E5]`}  style={{ border:"1px solid #4A90E5" }}
    />
    {errors.email && <p className="text-red-500 text-[10px]">{errors.email.message as string}</p>}
    <input
      type="password"
      {...register("password", { required: "Password is required", minLength: 6 })}
      placeholder="Create password"
      className={`${inputStyle} border-[#4A90E5] focus:border-[#4A90E5] focus:ring-1 focus:ring-[#4A90E5]`} style={{ border:"1px solid #4A90E5" }}
    />
    {errors.password && <p className="text-red-500 text-[10px]">{errors.password.message as string}</p>}
  </div>
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