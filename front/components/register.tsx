"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { buildPath } from "@/lib/utils";
import api from "@/lib/axios";
import FloatingInput from "@/components/ui/floating-input";
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
    watch,
    setError: setFormError,
    formState: { errors, isSubmitting },
  } = useForm({
    mode: "onSubmit",
    reValidateMode: "onChange",
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
      password2: "",
    },
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  async function onSubmit(values: any) {
    setError(null);
    setSuccess(null);
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
        setSuccess("Registration submitted successfully. You can now log in.");
        toast({ title: "Registration successful", description: "You can now log in." });
        setTimeout(() => router.replace(buildPath("/login")), 1200);
      } else {
        const message = data?.message || "Registration failed";
        setError(message);
        toast({ variant: "destructive", title: "Registration failed", description: message });
      }
    } catch (err: any) {
      const resp = err?.response?.data;
      if (resp?.errors) {
        Object.entries(resp.errors).forEach(([field, msgs]: any) => {
          const msg = Array.isArray(msgs) ? msgs[0] : String(msgs);
          // map backend fields to our form fields
          const map: Record<string, string> = {
            companyName: "company",
            email: "email",
            password: "password",
            mobile: "mobile",
            addressLine1: "invoice1",
            addressLine2: "invoice2",
            city: "city",
            state: "state",
            country: "country",
            zip_code: "postcode",
          };
          const target = map[field] || field;
          // @ts-ignore
          setFormError(target as any, { type: "server", message: msg });
        });
      }
      const message = resp?.message || "Registration failed";
      setError(message);
      toast({ variant: "destructive", title: "Registration error", description: message });
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="w-full max-w-[1000px] mx-auto pb-50 register-main-wrap">
      {/* Logo */}
      <div className="flex items-center justify-center h-[50px] shadow-[0_6px_6px_-6px_#666]">
        <img className="h-[36px] w-[67.8px] my-[7px]" src={settings?.company_logo_url || "placeholder-logo.png"} alt={settings?.company_title || "Logo"} />
      </div>

      <div className="overflow-hidden wrapper-space">
        <div className="grid grid-cols-2 loginRegisterWrapper">
          <Link href={buildPath("/login")} className="text-gray-700 bg-gray-100 py-3 text-center font-medium">
            Login
          </Link>
          <button className="bg-green-500 text-white py-3 font-medium">Register</button>
        </div>

        <div className="border-t my-5"></div>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4 loginregisterform">
          {/* Contact & Company */}
          {/* <FloatingInput label="Name" placeholder="Please enter your name..." {...register("name", { required: "Name is required" })} error={(errors as any).name?.message as string} /> */}
          <FloatingInput label="Company name" placeholder="Please enter your company name..." {...register("company", { required: "Company name is required" })} error={(errors as any).company?.message as string} />
          <div className="border-t my-5"></div>
          {/* Invoice Address */}
          <div>
            <p className="font-medium mb-4">Invoice Address</p>
            <div className="space-y-3">
              <FloatingInput label="Address line 1" placeholder="Please enter invoice address line 1..." {...register("invoice1", { required: "Address line 1 is required" })} error={(errors as any).invoice1?.message as string} />
              <FloatingInput label="Address line 2" placeholder="Please enter invoice address line 2..." {...register("invoice2")} />
              <FloatingInput label="City" placeholder="Please enter invoice address city..." {...register("city", { required: "City is required" })} error={(errors as any).city?.message as string} />
              <FloatingInput label="Country" placeholder="Please enter country (optional)..." {...register("country")} />
              <FloatingInput label="Postcode" placeholder="Please enter invoice address postcode..." {...register("postcode", { required: "Postcode is required" })} error={(errors as any).postcode?.message as string} />
            </div>
          </div>
          <div className="border-t my-5"></div>
          {/* Mobile */}
          <div>
            <p className="font-medium mb-4">Mobile Number</p>
            <FloatingInput
              label="Mobile number"
              placeholder="Please enter your mobile phone number..."
              maxLength={10}
              onKeyPress={(e) => {
                if (!/[0-9]/i.test(e.key)) {
                  e.preventDefault();
                  return false;
                }
                return true;
              }}
              {...register("mobile", { required: "Mobile number is required" })}
              error={(errors as any).mobile?.message as string}
            />
          </div>
          <div className="border-t my-5"></div>
          {/* Login Details */}
          <div>
            <p className="font-medium mb-4">Login Details</p>
            <div className="space-y-3">
              <FloatingInput
                type="email"
                label="Email"
                placeholder="Please enter your email address..."
                {...register("email", { required: "Email is required", pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: "Enter a valid email address" } })}
                error={(errors as any).email?.message as string}
              />
              <FloatingInput type="password" label="Password" placeholder="Please enter your password..." {...register("password", { required: "Password is required", minLength: { value: 6, message: "Password must be at least 6 characters" } })} error={(errors as any).password?.message as string} />
              <FloatingInput
                type="password"
                label="Confirm password"
                placeholder="Please re-enter your password..."
                {...register("password2", { required: "Confirm your password", validate: (v) => v === watch("password") || "Passwords don't match" })}
                error={(errors as any).password2?.message as string}
              />
            </div>
          </div>
          <div className="border-t my-5"></div>
          <button type="submit" disabled={loading || isSubmitting} className="w-full bg-black text-white rounded py-3 disabled:opacity-60 hover:cursor-pointer">
            {loading ? "Registering..." : "Register"}
          </button>

          <p className="text-s text-black-600">
            By selecting Register, you agree to our{" "}
            <a className="text-blue-600" href="#">
              Terms & Conditions
            </a>{" "}
            and{" "}
            <a className="text-blue-600" href="#">
              Privacy Policy
            </a>
            .
          </p>
        </form>
      </div>
    </div>
  );
}
