"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { buildPath } from "@/lib/utils";
import api from "@/lib/axios";
import { useForm } from "react-hook-form";
import { useToast } from "@/hooks/use-toast";
import { Thumbnail } from "./thumbnail";

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
  const inputStyle =
    "w-full h-[50px] !border-2 !border-[#4A90E5] rounded-[10px] px-4 text-[14px] focus:ring-2 focus:ring-blue-100 focus:!border-[#2F76D2] focus:outline-none placeholder:text-gray-500 text-gray-700 bg-white transition-all";

  const sectionTitleStyle = "block text-[#5b6b7a] font-bold text-[14px] text-left";

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
      <div className="register-container shadow-[0_4px_25px_rgba(0,0,0,0.05)]">

        {/* Fixed Header */}
        <div className="register-header flex justify-center items-center px-4">
          <Thumbnail
            height={22.00458335876465}
            containerClassName="max-w-[168.8212432861328px] mx-auto"
          />
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="flex min-h-0 flex-1 flex-col overflow-hidden">
          {/* Scrollable Middle Area */}
          <div className="register-scroll-area min-h-0">
            {/* Company Name */}
            <div className="flex flex-col gap-2">
              <label className={sectionTitleStyle}>Company Name</label>
              <input
                {...register("company", { required: "Company name is required" })}
                placeholder="Please enter your company name"
                className={inputStyle}
              />
              {errors.company && <p className="text-red-500 text-[10px] text-left">{errors.company.message as string}</p>}
            </div>

            {/* Company Address Section */}
            <div className="flex flex-col gap-2">
              <label className={sectionTitleStyle}>Company Address</label>
              <div className="flex flex-col gap-2">
                <input
                  {...register("invoice1", { required: "Address line 1 is required" })}
                  placeholder="Invoice address line 1"
                  className={inputStyle}
                />
                <input {...register("invoice2")} placeholder="Invoice address line 2" className={inputStyle} />
                <input
                  {...register("city", { required: "City is required" })}
                  placeholder="Invoice address city"
                  className={inputStyle}
                />
                <input {...register("state")} placeholder="Invoice address county" className={inputStyle} />
                <input
                  {...register("postcode", { required: "Postcode is required" })}
                  placeholder="Invoice address postcode"
                  className={inputStyle}
                />
              </div>
            </div>

            {/* Contact Number Section */}
            <div className="flex flex-col gap-2">
              <label className={sectionTitleStyle}>Contact Number</label>
              <input
                {...register("mobile", { required: "Contact number is required" })}
                placeholder="Please enter your contact number"
                className={inputStyle}
              />
              {errors.mobile && <p className="text-red-500 text-[10px] text-left">{errors.mobile.message as string}</p>}
            </div>

            {/* Login Details */}
            <div className="flex flex-col gap-2">
              <label className={sectionTitleStyle}>Login Details</label>
              <div className="flex flex-col gap-2 ">
                <input
                  type="email"
                  {...register("email", { required: "Email is required" })}
                  placeholder="Please enter your email"
                  className={inputStyle} 
                />
                <input
                  type="password"
                  {...register("password", { required: "Password is required", minLength: 6 })}
                  placeholder="Create password"
                  className={inputStyle}
                />
              </div>
            </div>
          </div>

          {/* Fixed Footer with Buttons */}
          <div className="register-footer">
            <button
              type="submit"
              disabled={loading || isSubmitting}
              className="register-action-button landing-primary-button text-white shadow-md active:scale-[0.98] transition-all"
            >
              {loading ? "Registering..." : "Agree & Sign Up"}
            </button>

            <button
              type="button"
              onClick={() => router.replace(buildPath("/landing"))}
              className="register-action-button border-2 border-[#4c91e2] bg-white text-[#4c91e2] hover:bg-blue-50 transition-all font-bold"
            >
              Back
            </button>
          </div>
        </form>
      </div>
    </div>
  );

}