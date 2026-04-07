"use client";

import { useCustomer } from "@/components/customer-provider";
import { Banner } from "@/components/banner";
import { useToast } from "@/hooks/use-toast";
import { useState, useEffect } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChartSimple, faHeart, faShop, faUser, faWallet } from "@fortawesome/free-solid-svg-icons";
import { MobilePageHeader } from "@/components/mobile-page-header";

const companyDetailsSchema = z.object({
  company_name: z.string().min(1, "Company name is required").max(255, "Company name must be less than 255 characters"),
  address_line1: z.string().min(1, "Address line 1 is required").max(255, "Address line 1 must be less than 255 characters"),
  address_line2: z.string().max(255, "Address line 2 must be less than 255 characters").optional(),
  city: z.string().min(1, "City is required").max(255, "City must be less than 255 characters"),
  country: z.string().max(255, "Country must be less than 255 characters").optional(),
  postcode: z.string().min(1, "Postcode is required").max(255, "Postcode must be less than 255 characters"),
  contact_number: z.string().max(50, "Contact number must be less than 50 characters").optional(),
  vat_number: z.string().max(100, "VAT number must be at most 100 characters").optional(),
  your_name: z.string().max(255, "Your name must be at most 255 characters").optional(),
});

type CompanyDetailsForm = z.infer<typeof companyDetailsSchema>;

interface MobileCompanyDetailsProps {
  onNavigate: (page: any, favorites?: boolean) => void;
  onBack: () => void;
}

/**
 * Figma Field Input: 354×50, r5, 1px #4A90E5, px 24.
 * `custom.css` applies unlayered `input[type="text"|"email"] { border/padding/radius/... }` after Tailwind,
 * so use `!` on overridden props (same pattern as `register.tsx` `inputStyle`).
 */
const inputClass =
  "box-border block !h-[50px] w-full min-h-[50px] !rounded-[5px] border border-solid !border-[#4A90E5] !bg-white !px-6 !py-4 !text-[14px] !leading-[18px] text-[#1E293B] shadow-none !outline-none transition-[box-shadow,border-color] placeholder:text-[#94A3B8] focus:!border-[#4A90E5] focus:!outline-none focus:ring-2 focus:ring-[#4A90E5]/25";

const readOnlyInputClass =
  "box-border block !h-[50px] w-full min-h-[50px] cursor-not-allowed !rounded-[5px] border border-solid !border-[#4A90E5] !bg-[#F8FAFC] !px-6 !py-4 !text-[14px] !leading-[18px] text-[#64748B] !outline-none placeholder:text-[#94A3B8]";

export function MobileCompanyDetails({ onNavigate, onBack }: MobileCompanyDetailsProps) {
  const { customer, refresh } = useCustomer();
  const { toast } = useToast();
  const [saving, setSaving] = useState(false);

  const form = useForm<CompanyDetailsForm>({
    resolver: zodResolver(companyDetailsSchema),
    defaultValues: {
      company_name: "",
      address_line1: "",
      address_line2: "",
      city: "",
      country: "",
      postcode: "",
      contact_number: "",
      vat_number: "",
      your_name: "",
    },
  });

  useEffect(() => {
    if (customer) {
      form.reset({
        company_name: customer.company_name || "",
        address_line1: customer.address_line1 || "",
        address_line2: customer.address_line2 || "",
        city: customer.city || "",
        country: customer.country || "",
        postcode: customer.postcode || "",
        contact_number: customer.phone || "",
        vat_number: customer.vat_number || "",
        your_name: customer.your_name || "",
      });
    }
  }, [customer, form]);

  const saveCompanyDetails = async (data: CompanyDetailsForm) => {
    setSaving(true);
    try {
      const response = await api.put("/customer", data);
      if (response.data?.success) {
        toast({ title: "Success! 🎉", description: "Company details updated successfully" });
        await refresh();
      }
    } catch (error: unknown) {
      const err = error as { response?: { data?: { errors?: Record<string, string[]> } } };
      if (err.response?.data?.errors) {
        const apiErrors = err.response.data.errors;
        Object.keys(apiErrors).forEach((field) => {
          form.setError(field as keyof CompanyDetailsForm, {
            type: "server",
            message: apiErrors[field][0],
          });
        });
      } else {
        toast({
          title: "Error",
          description: "Failed to update company details. Please try again.",
          variant: "destructive",
        });
      }
    } finally {
      setSaving(false);
    }
  };

  /** Section labels: Company Name, Address, Contact Number, Login Details — bold */
  const labelClass = "mb-2 block w-full text-[16px] font-medium leading-[18px] tracking-[0] text-[#3D495E] lg:mb-0";

  return (
    <div className="flex h-[100dvh] w-full justify-center overflow-hidden bg-[#FAFBFD]">
      <div
        className="relative mx-auto h-full min-h-0 w-full max-w-[402px] overflow-hidden bg-[#FAFBFD] lg:max-h-[1024px] lg:max-w-[1000px]"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <MobilePageHeader title="My Details" onBack={onBack} />

        <main className="scrollbar-hide absolute inset-x-0 bottom-[64px] top-[96px] w-full overflow-x-hidden overflow-y-auto bg-[#FAFBFD] px-6 pb-[80px] pt-4 lg:px-4 lg:pb-[60px]">
          <div className="mx-auto flex w-full max-w-full flex-col gap-6 lg:gap-3">
            <Banner className="mx-auto h-[89px] w-full max-w-[354px] rounded-[10px] border border-[#E2E2E2] lg:h-[242px] lg:max-w-[968px] lg:border-0 lg:!rounded-[10px]" />

            <form onSubmit={form.handleSubmit(saveCompanyDetails)} noValidate className="mx-auto flex w-full max-w-[354px] flex-col gap-6 lg:max-w-[700px]">
            <div className="flex w-full flex-col lg:gap-2">
              <label htmlFor="cd-company" className={labelClass}>
                Company Name
              </label>
              <input
                id="cd-company"
                type="text"
                placeholder="Company Name"
                {...form.register("company_name")}
                className={inputClass}
              />
              {form.formState.errors.company_name?.message ? (
                <p className="mt-1 text-[11px] text-red-600">{form.formState.errors.company_name.message}</p>
              ) : null}
            </div>

            <div className="flex w-full flex-col lg:gap-2">
              <span className={labelClass}>Company Address</span>
              <div className="flex flex-col gap-3 lg:gap-2">
                <input type="text" placeholder="Address Line 1" {...form.register("address_line1")} className={inputClass} />
                <input type="text" placeholder="Address Line 2" {...form.register("address_line2")} className={inputClass} />
                <input type="text" placeholder="Town / City" {...form.register("city")} className={inputClass} />
                <input type="text" placeholder="Country" {...form.register("country")} className={inputClass} />
                <input type="text" placeholder="Postcode" {...form.register("postcode")} className={inputClass} />
              </div>
              {form.formState.errors.address_line1?.message ? (
                <p className="mt-1 text-[11px] text-red-600">{form.formState.errors.address_line1.message}</p>
              ) : null}
            </div>

            <div className="flex w-full flex-col lg:gap-2">
              <label htmlFor="cd-contact" className={labelClass}>
                Phone Number
              </label>
              <input
                id="cd-contact"
                type="text"
                placeholder="Phone number"
                {...form.register("contact_number")}
                className={inputClass}
              />
            </div>

            <div className="flex w-full flex-col lg:gap-2">
              <label htmlFor="cd-vat" className={labelClass}>
                VAT Number (if applicable)
              </label>
              <input
                id="cd-vat"
                type="text"
                placeholder="VAT Number (if applicable)"
                {...form.register("vat_number")}
                className={inputClass}
              />
              {form.formState.errors.vat_number?.message ? (
                <p className="mt-1 text-[11px] text-red-600">{form.formState.errors.vat_number.message}</p>
              ) : null}
            </div>

            <div className="flex w-full flex-col lg:gap-2">
              <label htmlFor="cd-your-name" className={labelClass}>
                Your Name
              </label>
              <input
                id="cd-your-name"
                type="text"
                placeholder="Your Name"
                {...form.register("your_name")}
                className={inputClass}
              />
              {form.formState.errors.your_name?.message ? (
                <p className="mt-1 text-[11px] text-red-600">{form.formState.errors.your_name.message}</p>
              ) : null}
            </div>

            <div className="flex w-full flex-col lg:gap-2">
              <span className={labelClass}>Login Details</span>
              <input type="email" placeholder="Please enter your email" value={customer?.email || ""} readOnly className={readOnlyInputClass} />
            </div>

            <div className="pt-1">
              <button
                type="submit"
                disabled={saving}
                className="flex h-[48px] w-full min-h-[48px] items-center justify-center rounded-[25px] bg-gradient-to-b from-[#2868C0] to-[#4C92E9] px-[26px] text-[20px] font-bold leading-[18px] tracking-normal text-white shadow-[0_2px_10px_rgba(40,104,192,0.35)] transition-opacity hover:opacity-95 active:opacity-90 disabled:opacity-60 disabled:shadow-none lg:mx-auto lg:max-w-[306px]"
              >
                {saving ? "Saving..." : "Save"}
              </button>
            </div>
            </form>
          </div>
        </main>

        <nav
          className="fixed bottom-0 left-1/2 z-50 box-border flex h-[64px] w-full max-w-[402px] -translate-x-1/2 flex-col rounded-t-[10px] bg-[#F6F4FA] px-[43px] pb-4 pt-2 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)] lg:max-w-[1000px]"
          aria-label="Main navigation"
          style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
        >
          <div className="flex min-h-0 w-full flex-1 items-center justify-center">
            <div className="flex h-[40px] w-[316px] max-w-full items-center justify-between">
            <button
              type="button"
              onClick={() => onNavigate("dashboard")}
              className="flex h-full cursor-pointer min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faChartSimple} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Dashboard</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", false)}
              className="flex h-full cursor-pointer min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faShop} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Shop</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", true)}
              className="flex h-full cursor-pointer min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faHeart} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Favourites</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("wallet")}
              className="flex h-full cursor-pointer min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faWallet} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Wallet</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("account")}
              className="flex h-full cursor-pointer min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
              aria-current="page"
            >
              <FontAwesomeIcon icon={faUser} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Account</span>
            </button>
            </div>
          </div>
        </nav>
      </div>
    </div>
  );
}
