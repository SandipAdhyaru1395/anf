"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { buildPath } from "@/lib/utils";
import api from "@/lib/axios";
import { useForm } from "react-hook-form";
import { useSettings } from "@/components/settings-provider";
import { useToast } from "@/hooks/use-toast";

export default function Register() {
  const router = useRouter();
  const { settings } = useSettings();
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
const inputStyle =
"w-full h-[50px] border-[#a5c9f5] border-[1.5px] rounded-[5px] px-4 text-[14px] focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder:text-gray-400 text-gray-700 bg-white transition-all";

  const labelStyle = "block text-[#5b6b7a] font-bold text-[14px] mb-2 mt-6 text-left w-full";

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
    <div className="min-h-screen bg-[#f8f9fb] flex flex-col items-center">
      {/* Main Container */}
      <div className="register-container bg-white shadow-[0_4px_25px_rgba(0,0,0,0.05)]">
        
        {/* Logo Section */}
        <div className="flex justify-center">
          <img 
           className="register-logo"
            src={settings?.company_logo_url || "https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Logo_Aqua_Vape.png/640px-Logo_Aqua_Vape.png"} 
            alt="Aqua Vape" 
          />
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="register-form space-y-1">
          
          {/* Company Name */}
          <label className={labelStyle}>Company Name</label>
          <input 
            {...register("company", { required: "Company name is required" })} 
            placeholder="Please enter your company name" 
            className={inputStyle} 
          />
          {errors.company && <p className="text-red-500 text-[10px] text-left">{errors.company.message as string}</p>}

{/* Company Address Section */}
<label className={labelStyle}>Company Address</label>
<div className="flex flex-col gap-2"> {/* space-y ki jagah gap use karein behtar control ke liye */}
  
  {/* Address Line 1 */}
  <div className="flex flex-col">
    <input 
      {...register("invoice1", { required: "Address line 1 is required" })} 
      placeholder="Invoice address line 1" 
      className={inputStyle} 
    />
    {errors.invoice1 &&
    <div className="h-4"> {/* Fixed height wrapper for error */}
       <p className="text-red-500 text-[11px] ml-1">{errors.invoice1.message as string}</p>
    </div>
}
  </div>

  {/* Address Line 2 (No required check, but same wrapper for alignment) */}
  <div className="flex flex-col">
    <input {...register("invoice2")} placeholder="Invoice address line 2" className={inputStyle} />
  </div>

  {/* City */}
  <div className="flex flex-col">
    <input 
      {...register("city", { required: "City is required" })} 
      placeholder="Invoice address city" 
      className={inputStyle} 
    />
    {errors.city &&
    <div className="h-4">
       <p className="text-red-500 text-[11px] ml-1">{errors.city.message as string}</p>
    </div>
}
  </div>

  {/* County/State */}
  <div className="flex flex-col">
    <input {...register("state")} placeholder="Invoice address county" className={inputStyle} />
  </div>

  {/* Postcode */}
  <div className="flex flex-col">
    <input 
      {...register("postcode", { required: "Postcode is required" })} 
      placeholder="Invoice address postcode" 
      className={inputStyle} 
    />
    {errors.postcode &&
    <div className="h-4">
       <p className="text-red-500 text-[11px] ml-1">{errors.postcode.message as string}</p>
    </div>
}
  </div>
</div>

          {/* Login Details */}
        <label className={labelStyle}>Login Details</label>
<div className="space-y-3 mb-8">
  <input 
    type="email" 
    {...register("email", { required: "Email is required" })} 
    placeholder="Please enter your email address..." 
    className={inputStyle}
  />
  
  <input 
    type="password" 
    {...register("password", { required: "Password is required", minLength: 6 })} 
    placeholder="Create password" 
    className={inputStyle}
  />
</div>

          {/* Buttons */}
          <div className="flex flex-col gap-4 pt-4 px-[8px]">
            <button 
              type="submit" 
              disabled={loading || isSubmitting}
             className="w-full cursor-pointer bg-[#4c91e2] hover:bg-[#3b7bc4] text-white register-action-button-text py-4 rounded-full transition-all shadow-md active:scale-[0.98]"
            >
              {loading ? "Registering..." : "Agree & Sign Up"}
            </button>
            
          <button 
  type="button"
  onClick={() => router.replace(buildPath("/landing"))}
  className="w-full bg-white cursor-pointer border border-[#4c91e2] text-[#4c91e2] register-action-button-text py-3.5 rounded-full hover:bg-blue-50 transition-all"
>
  Back
</button>
          </div>

          {/* <p className="text-[12px] text-gray-500 text-center mt-6">
            By selecting Register, you agree to our{" "}
            <a className="text-blue-500 underline" href="#">Terms & Conditions</a> and{" "}
            <a className="text-blue-500 underline" href="#">Privacy Policy</a>.
          </p> */}
        </form>
      </div>
    </div>
  );
}