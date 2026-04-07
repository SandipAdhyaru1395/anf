"use client";

import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import api from "@/lib/axios";
import { useToast } from "@/hooks/use-toast";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faChartSimple,
  faHeart,
  faShop,
  faUser,
  faWallet,
} from "@fortawesome/free-solid-svg-icons";
import { Banner } from "@/components/banner";
import { MobilePageHeader } from "@/components/mobile-page-header";

// Validation schema
const branchSchema = z.object({
    name: z.string().min(1, "Branch name is required"),
    contact_name: z.string().max(255, "Contact name must be at most 255 characters").optional(),
    line1: z.string().min(1, "Address line 1 is required"),
    line2: z.string().optional(),
    city: z.string().min(1, "City is required"),
    county: z.string().optional(),
    postcode: z.string().min(1, "Postcode is required"),
});

type BranchFormData = z.infer<typeof branchSchema>;

interface MobileNewBranchProps {
  onNavigate: (page: any, favorites?: boolean) => void;
  onBack: () => void;
  onBranchSaved?: () => void;
}

/** Same as My Details — overrides global `custom.css` inputs */
const inputClass =
  "box-border block !h-[50px] w-full min-h-[50px] !rounded-[5px] border border-solid !border-[#4A90E5] !bg-white !px-6 !py-0 !text-[14px] !leading-[48px] text-[#1E293B] shadow-none !outline-none transition-[box-shadow,border-color] placeholder:text-[#94A3B8] focus:!border-[#4A90E5] focus:!outline-none focus:ring-2 focus:ring-[#4A90E5]/25";

const labelClass = "mb-2 text-[16px] font-bold leading-[18px] tracking-normal text-[#3D495E]";

const saveButtonClass =
  "mx-auto flex h-[48px] w-[338px] max-w-full min-h-[48px] items-center justify-center gap-[11px] rounded-[25px] bg-gradient-to-b from-[#2868C0] to-[#4C92E9] px-[26px] py-[15px] text-[20px] font-bold leading-[18px] tracking-normal text-white shadow-[0_2px_10px_rgba(40,104,192,0.35)] transition-opacity hover:opacity-95 active:opacity-90 disabled:opacity-60 disabled:shadow-none";

export function MobileNewBranch({ onNavigate, onBack, onBranchSaved }: MobileNewBranchProps) {
  const [isLoading, setIsLoading] = useState(false);
  const { toast } = useToast();

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<BranchFormData>({
    resolver: zodResolver(branchSchema),
    defaultValues: {
      name: "",
      line1: "",
      line2: "",
      city: "",
      county: "",
      postcode: "",
    },
  });

  const onSubmit = async (data: BranchFormData) => {
    setIsLoading(true);
    try {
      const response = await api.post("/branches", {
        name: data.name,
        contact_name: data.contact_name?.trim() ? data.contact_name.trim() : null,
        address_line1: data.line1,
        address_line2: data.line2,
        city: data.city,
        country: data.county,
        zip_code: data.postcode,
        is_default: false,
      });

      if (response.data.success) {
        toast({
          title: "Success!",
          description: "Branch saved successfully",
        });
        if (onBranchSaved) onBranchSaved();
        onBack();
      } else {
        toast({
          title: "Error",
          description: "Failed to save branch",
          variant: "destructive",
        });
      }
    } catch (error: any) {
      if (error.response?.status === 422 && error.response?.data?.errors) {
        const validationErrors = error.response.data.errors;
        const firstError = Object.values(validationErrors)[0];
        toast({
          title: "Validation Error",
          description: Array.isArray(firstError) ? firstError[0] : firstError,
          variant: "destructive",
        });
      } else {
        toast({
          title: "Error",
          description: "Something went wrong. Please try again.",
          variant: "destructive",
        });
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div
      className="relative mx-auto flex h-[100dvh] min-h-0 w-full max-w-[402px] flex-col bg-[#FAFBFD] lg:h-[1024px] lg:max-w-[1000px]"
      style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
    >
      <div className="fixed top-0 left-1/2 -translate-x-1/2 z-[60] w-full max-w-[402px] lg:max-w-[1000px]">
        <MobilePageHeader title="New Branch" onBack={onBack} />
      </div>

      <main className="scrollbar-hide min-h-0 flex-1 overflow-x-hidden overflow-y-auto bg-[#FAFBFD] px-6 pb-[180px] pt-[112px] lg:px-4 lg:pb-[140px]">
        <div className="mx-auto flex w-full max-w-[354px] flex-col gap-6 lg:max-w-[968px]">
          <Banner className="h-[89px] max-w-[354px] rounded-[10px] border border-[#E2E2E2] lg:h-[242px] lg:max-w-[968px]" />

          <form id="new-branch-form" onSubmit={handleSubmit(onSubmit)} noValidate className="flex w-full flex-col gap-4 lg:mx-auto lg:max-w-[700px]">
            <div className="flex w-full flex-col gap-2">
              <label htmlFor="nb-name" className={labelClass}>
                Company Name
              </label>
              <input id="nb-name" type="text" placeholder="Company Name" {...register("name")} className={inputClass} />
              {errors.name?.message ? <p className="mt-1 text-[11px] text-red-600">{errors.name.message}</p> : null}
            </div>

            <div className="flex w-full flex-col gap-2">
              <label htmlFor="nb-contact-name" className={labelClass}>
                Contact Name
              </label>
              <input
                id="nb-contact-name"
                type="text"
                placeholder="Contact Name"
                {...register("contact_name")}
                className={inputClass}
              />
              {errors.contact_name?.message ? (
                <p className="mt-1 text-[11px] text-red-600">{errors.contact_name.message}</p>
              ) : null}
            </div>

            <div className="flex w-full flex-col gap-2">
              <span className={labelClass}>Address</span>
              <div className="flex flex-col gap-2">
                <input type="text" placeholder="Address line 1" {...register("line1")} className={inputClass} />
                <input type="text" placeholder="Address line 2" {...register("line2")} className={inputClass} />
                <input type="text" placeholder="Town / City" {...register("city")} className={inputClass} />
                <input type="text" placeholder="County" {...register("county")} className={inputClass} />
                <input type="text" placeholder="Postcode" {...register("postcode")} className={inputClass} />
              </div>
              {errors.line1?.message ? <p className="mt-1 text-[11px] text-red-600">{errors.line1.message}</p> : null}
              {errors.city?.message ? <p className="mt-1 text-[11px] text-red-600">{errors.city.message}</p> : null}
              {errors.postcode?.message ? <p className="mt-1 text-[11px] text-red-600">{errors.postcode.message}</p> : null}
            </div>

          </form>
        </div>
      </main>

      <div className="fixed bottom-[64px] left-1/2 z-40 w-full max-w-[402px] -translate-x-1/2 bg-[#FAFBFD] px-6 pb-3 pt-2 lg:max-w-[1000px] lg:px-4">
        <div className="mx-auto w-full max-w-[354px] lg:max-w-[700px]">
          <button form="new-branch-form" type="submit" disabled={isLoading} className={saveButtonClass}>
            {isLoading ? "Saving..." : "Save"}
          </button>
        </div>
      </div>

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
              className="flex h-full cursor-pointer min-w-0 flex-col items-center justify-center gap-1 text-[#4A90E5]"
              aria-current="page"
            >
              <FontAwesomeIcon icon={faUser} className="h-[23px] w-[23px] shrink-0 text-[23px] leading-none text-[#4A90E5]" aria-hidden />
              <span className="inline-flex h-[13px] min-w-[36px] items-center justify-center text-center text-[11px] font-medium leading-none tracking-normal text-[#4A90E5]">
                Account
              </span>
            </button>
          </div>
        </div>
      </nav>
    </div>
  );
}
