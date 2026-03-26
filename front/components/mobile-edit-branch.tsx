"use client";

import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import api from "@/lib/axios";
import { useToast } from "@/hooks/use-toast";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChartSimple, faHeart, faShop, faTrashCan, faUser, faWallet } from "@fortawesome/free-solid-svg-icons";
import { Banner } from "@/components/banner";
import { MobilePageHeader } from "@/components/mobile-page-header";

interface Branch {
  id: number;
  name: string;
  address_line1: string;
  address_line2: string;
  city: string;
  zip_code: string;
  country: string;
  is_default: boolean;
}

interface MobileEditBranchProps {
  branchDetails: Branch;
  onNavigate: (page: any, favorites?: boolean) => void;
  onBack: () => void;
  onBranchUpdated?: () => void;
}

/** Same as My Details — overrides global `custom.css` inputs */
const inputClass =
  "box-border block !h-[50px] w-full min-h-[50px] !rounded-[5px] border border-solid !border-[#4A90E5] !bg-white !px-6 !py-0 !text-[14px] !leading-[48px] text-[#1E293B] shadow-none !outline-none transition-[box-shadow,border-color] placeholder:text-[#94A3B8] focus:!border-[#4A90E5] focus:!outline-none focus:ring-2 focus:ring-[#4A90E5]/25";

const labelClass = "mb-2 text-[16px] font-bold leading-[18px] tracking-normal text-[#3D495E]";

const saveButtonClass =
  "flex h-[48px] w-full min-h-[48px] items-center justify-center rounded-[25px] bg-gradient-to-b from-[#2868C0] to-[#4C92E9] px-[26px] text-[20px] font-bold leading-[18px] tracking-normal text-white shadow-[0_2px_10px_rgba(40,104,192,0.35)] transition-opacity hover:opacity-95 active:opacity-90 disabled:opacity-60 disabled:shadow-none";

const deleteButtonClass =
  "mt-4 flex h-[48px] w-full min-h-[48px] items-center justify-center gap-2 rounded-[25px] border-2 border-[#EF4444] bg-white text-[16px] font-bold leading-[18px] text-[#EF4444] transition-opacity hover:bg-red-50 active:opacity-90 disabled:opacity-50";

export function MobileEditBranch({ branchDetails, onNavigate, onBack, onBranchUpdated }: MobileEditBranchProps) {
  const [isSaving, setIsSaving] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [confirmOpen, setConfirmOpen] = useState(false);
  const { toast } = useToast();

  const branchSchema = z.object({
    name: z.string().min(1, "Branch name is required"),
    line1: z.string().min(1, "Address line 1 is required"),
    line2: z.string().optional(),
    city: z.string().min(1, "City is required"),
    county: z.string().optional(),
    postcode: z.string().min(1, "Postcode is required"),
  });

  type BranchFormData = z.infer<typeof branchSchema>;

  const { register, handleSubmit, formState: { errors } } = useForm<BranchFormData>({
    resolver: zodResolver(branchSchema),
    defaultValues: {
      name: branchDetails.name || "",
      line1: branchDetails.address_line1 || "",
      line2: branchDetails.address_line2 || "",
      city: branchDetails.city || "",
      county: branchDetails.country || "",
      postcode: branchDetails.zip_code || "",
    },
  });

  const onSubmit = async (data: BranchFormData) => {
    if (!branchDetails?.id) {
      onBack();
      return;
    }
    try {
      setIsSaving(true);
      const payload = {
        name: data.name,
        address_line1: data.line1,
        address_line2: data.line2,
        city: data.city,
        country: data.county,
        zip_code: data.postcode,
      };
      const res = await api.put(`/branches/${branchDetails.id}`, payload);
      if (res?.data?.success) {
        toast({ title: "Success", description: "Branch updated successfully" });
        if (onBranchUpdated) {
          try {
            await onBranchUpdated();
          } catch {
            /* ignore */
          }
        }
        onBack();
      } else {
        toast({ title: "Error", description: "Failed to update branch", variant: "destructive" });
      }
    } catch (e: unknown) {
      const err = e as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } };
      if (err?.response?.status === 422 && err?.response?.data?.errors) {
        const validationErrors = err.response.data.errors;
        const firstError = Object.values(validationErrors)[0];
        const message = Array.isArray(firstError) ? firstError[0] : (firstError as string) || "Validation failed";
        toast({ title: "Validation Error", description: message, variant: "destructive" });
      } else {
        toast({ title: "Error", description: "Something went wrong. Please try again.", variant: "destructive" });
      }
    } finally {
      setIsSaving(false);
    }
  };

  const handleDeleteConfirmed = async () => {
    if (!branchDetails?.id) {
      onBack();
      return;
    }
    try {
      setIsDeleting(true);
      const res = await api.delete(`/branches/${branchDetails.id}`);
      if (res?.data?.success) {
        toast({ title: "Deleted", description: "Branch deleted successfully" });
        if (onBranchUpdated) {
          try {
            await onBranchUpdated();
          } catch {
            /* ignore */
          }
        }
        onBack();
      } else {
        toast({ title: "Error", description: "Failed to delete branch", variant: "destructive" });
      }
    } catch {
      toast({ title: "Error", description: "Something went wrong. Please try again.", variant: "destructive" });
    } finally {
      setIsDeleting(false);
      setConfirmOpen(false);
    }
  };

  return (
    <div
      className="relative mx-auto flex h-[100dvh] min-h-0 w-full max-w-[402px] flex-col bg-[#FAFBFD]"
      style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
    >
      <MobilePageHeader title="Edit Branch" onBack={onBack} />

      <main className="scrollbar-hide min-h-0 flex-1 overflow-x-hidden overflow-y-auto bg-[#FAFBFD] px-6 pb-[80px] pt-4">
        <div className="mx-auto flex w-full max-w-[354px] flex-col gap-6">
          <Banner className="h-[89px] max-w-[354px] rounded-[10px] border border-[#E2E2E2]" />

          <form onSubmit={handleSubmit(onSubmit)} noValidate className="flex flex-col gap-6">
            <div className="flex w-full flex-col">
              <label htmlFor="eb-name" className={labelClass}>
                Company Name
              </label>
              <input
                id="eb-name"
                type="text"
                placeholder="Please enter branch / company name"
                {...register("name")}
                className={inputClass}
              />
              {errors.name?.message ? <p className="mt-1 text-[11px] text-red-600">{errors.name.message}</p> : null}
            </div>

            <div className="flex w-full flex-col">
              <span className={labelClass}>Address</span>
              <div className="flex flex-col gap-2">
                <input type="text" placeholder="Address line 1" {...register("line1")} className={inputClass} />
                <input type="text" placeholder="Address line 2" {...register("line2")} className={inputClass} />
                <input type="text" placeholder="City" {...register("city")} className={inputClass} />
                <input type="text" placeholder="County" {...register("county")} className={inputClass} />
                <input type="text" placeholder="Postcode" {...register("postcode")} className={inputClass} />
              </div>
              {errors.line1?.message ? <p className="mt-1 text-[11px] text-red-600">{errors.line1.message}</p> : null}
              {errors.city?.message ? <p className="mt-1 text-[11px] text-red-600">{errors.city.message}</p> : null}
              {errors.postcode?.message ? <p className="mt-1 text-[11px] text-red-600">{errors.postcode.message}</p> : null}
            </div>

            <div className="pt-1">
              <button type="submit" disabled={isSaving} className={saveButtonClass}>
                {isSaving ? "Saving..." : "Save"}
              </button>
              <button type="button" onClick={() => setConfirmOpen(true)} disabled={isDeleting} className={deleteButtonClass}>
                <FontAwesomeIcon icon={faTrashCan} className="h-4 w-4 shrink-0" aria-hidden />
                {isDeleting ? "Deleting..." : "Delete branch"}
              </button>
            </div>
          </form>
        </div>
      </main>

      {confirmOpen ? (
        <div className="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="eb-delete-title">
          <button
            type="button"
            className="absolute inset-0 bg-black/40"
            aria-label="Close dialog"
            onClick={() => !isDeleting && setConfirmOpen(false)}
          />
          <div className="relative w-full max-w-[340px] rounded-[12px] border border-[#E2E2E2] bg-white p-5 shadow-lg">
            <h3 id="eb-delete-title" className="text-[16px] font-bold text-[#3D495E]">
              Delete branch?
            </h3>
            <p className="mt-2 text-[14px] leading-snug text-[#64748B]">This action cannot be undone.</p>
            <div className="mt-5 flex justify-end gap-2">
              <button
                type="button"
                onClick={() => setConfirmOpen(false)}
                disabled={isDeleting}
                className="h-10 rounded-[10px] border border-[#E2E2E2] bg-[#F8FAFC] px-4 text-[14px] font-semibold text-[#3D495E] disabled:opacity-50"
              >
                Cancel
              </button>
              <button
                type="button"
                onClick={handleDeleteConfirmed}
                disabled={isDeleting}
                className="h-10 rounded-[10px] bg-[#EF4444] px-4 text-[14px] font-semibold text-white disabled:opacity-50"
              >
                {isDeleting ? "Deleting..." : "Delete"}
              </button>
            </div>
          </div>
        </div>
      ) : null}

      <nav
        className="fixed bottom-0 left-1/2 z-50 box-border flex h-[64px] w-full max-w-[402px] -translate-x-1/2 flex-col rounded-t-[10px] bg-white px-[43px] pb-4 pt-2 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)]"
        aria-label="Main navigation"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <div className="flex min-h-0 w-full flex-1 items-center justify-center">
          <div className="flex h-[40px] w-[316px] max-w-full items-center justify-center gap-2">
            <button
              type="button"
              onClick={() => onNavigate("dashboard")}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faChartSimple} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Dashboard</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", false)}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faShop} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Shop</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", true)}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faHeart} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Favourites</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("wallet")}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faWallet} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Wallet</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("account")}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-0.5 text-[#4A90E5]"
              aria-current="page"
            >
              <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#4A90E5]">
                <FontAwesomeIcon icon={faUser} className="h-[18px] w-[18px] text-[18px] leading-none text-white" aria-hidden />
              </span>
              <span className="text-center text-[11px] font-bold leading-none text-[#4A90E5]">Account</span>
              <span className="h-0.5 w-6 shrink-0 rounded-full bg-[#4A90E5]" aria-hidden />
            </button>
          </div>
        </div>
      </nav>
    </div>
  );
}
