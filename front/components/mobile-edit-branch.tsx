"use client";

import { useState, useEffect } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import api from "@/lib/axios";
import { useToast } from "@/hooks/use-toast";
import { Button } from "@/components/ui/button";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faChevronLeft,
  faCircleCheck,
  faCircleXmark,
  faGauge,
  faShop,
  faStore,
  faUser,
  faWallet,
} from "@fortawesome/free-solid-svg-icons";
import { Banner } from "./banner";
import FloatingInput from "./ui/floating-input";

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
  onNavigate: (
    page:
      | "dashboard"
      | "shop"
      | "basket"
      | "wallet"
      | "account"
      | "rep-details"
      | "company-details"
      | "branches"
  ) => void;
  onBack: () => void;
  onBranchUpdated?: () => void;
}

export function MobileEditBranch({ branchDetails, onNavigate, onBack, onBranchUpdated }: MobileEditBranchProps) {
  const [isSaving, setIsSaving] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [confirmOpen, setConfirmOpen] = useState(false);
  const { toast } = useToast();

  // Validation schema aligned with MobileNewBranch
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
    }
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
          try { await onBranchUpdated(); } catch {}
        }
        onBack();
      } else {
        toast({ title: "Error", description: "Failed to update branch", variant: "destructive" });
      }
    } catch (e: any) {
      if (e?.response?.status === 422 && e?.response?.data?.errors) {
        const validationErrors = e.response.data.errors;
        const firstError = Object.values(validationErrors)[0] as any;
        const message = Array.isArray(firstError) ? firstError[0] : (firstError || "Validation failed");
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
          try { await onBranchUpdated(); } catch {}
        }
        onBack();
      } else {
        toast({ title: "Error", description: "Failed to delete branch", variant: "destructive" });
      }
    } catch (e: any) {
      toast({ title: "Error", description: "Something went wrong. Please try again.", variant: "destructive" });
    } finally {
      setIsDeleting(false);
      setConfirmOpen(false);
    }
  };

  return (
    <div className="w-full max-w-[1000px] mx-auto bg-white min-h-screen">
      {/* Header */}
      <div className="bg-white flex items-center border-b h-[50px]">
        <div className="flex items-center">
          <div className="w-[66px] h-[25px] rounded-full flex items-center justify-center">
            <FontAwesomeIcon icon={faStore} className="text-green-600" style={{ width: "27px", height: "24px" }} />
          </div>
          <span onClick={onBack} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline">Account</span>
          &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
          &nbsp;<span className="text-[16px] font-semibold">Branch</span>
        </div>
      </div>

      {/* Banner */}
      <Banner />

      {/* Form */}
      <form onSubmit={handleSubmit(onSubmit)} className="p-[10px] mb-[82px]">
        <FloatingInput
          label="Branch Name"
          placeholder="Enter branch name..."
          {...register("name")}
          error={errors.name?.message}
        />
        <hr className="my-[20px]" />

        <FloatingInput
          label="Line 1"
          placeholder="Enter address line 1..."
          {...register("line1")}
          error={errors.line1?.message}
        />

        <FloatingInput
          label="Line 2"
          placeholder="Enter address line 2..."
          {...register("line2")}
          error={errors.line2?.message}
        />

        <FloatingInput
          label="City"
          placeholder="Enter city..."
          {...register("city")}
          error={errors.city?.message}
        />

        <FloatingInput
          label="Country"
          placeholder="Enter country..."
          {...register("county")}
          error={errors.county?.message}
        />

        <FloatingInput
          label="Postcode"
          placeholder="Enter postcode..."
          {...register("postcode")}
          error={errors.postcode?.message}
        />

        <hr className="my-[20px]" />

        {/* Buttons */}
        <Button
          type="submit"
          className="w-full cursor-pointer rounded bg-green-600 text-white font-semibold h-[45px] !leading-[13px]"
        >
          <div className="!leading-[13px]">
            <FontAwesomeIcon icon={faCircleCheck} style={{ width: "16px", height: "16px" }} />
          </div>
          <span className="text-[16px]">{isSaving ? "Saving..." : "Save"}</span>
        </Button>
        <hr className="my-[20px]"></hr>
        <Button
          type="button"
          onClick={() => setConfirmOpen(true)}
          className="w-full cursor-pointer rounded bg-red-600 text-white font-semibold h-[45px] !leading-[13px]"
        >
          <div className="!leading-[13px]">
            <FontAwesomeIcon icon={faCircleXmark} style={{ width: "16px", height: "16px" }} />
          </div>
          <span className="text-[16px]">{isDeleting ? "Deleting..." : "Delete"}</span>
        </Button>
      {confirmOpen && (
        <div className="fixed inset-0 z-[60] flex items-center justify-center">
          <div className="absolute inset-0 bg-black/40" onClick={() => !isDeleting && setConfirmOpen(false)}></div>
          <div className="relative bg-white w-[90%] max-w-[420px] rounded-md shadow-lg p-4">
            <h3 className="text-[16px] font-semibold mb-2">Delete branch?</h3>
            <p className="text-[14px] text-gray-600 mb-4">This action cannot be undone.</p>
            <div className="flex items-center justify-end gap-2">
              <Button
                type="button"
                onClick={() => setConfirmOpen(false)}
                className="cursor-pointer rounded bg-gray-200 text-black font-semibold h-[40px] px-4 !leading-[13px]"
                disabled={isDeleting}
              >
                Cancel
              </Button>
              <Button
                type="button"
                onClick={handleDeleteConfirmed}
                className="cursor-pointer rounded bg-red-600 text-white font-semibold h-[40px] px-4 !leading-[13px] disabled:opacity-50"
                disabled={isDeleting}
              >
                {isDeleting ? "Deleting..." : "Delete"}
              </Button>
            </div>
          </div>
        </div>
      )}
      </form>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 px-[18px]">
        <div className="flex flex-row items-center justify-between h-[72px] footer-nav-col">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faGauge} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faShop} className="text-[#607565]" style={{ width: "30px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faWallet} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faUser} className="text-[#607565]" style={{ width: "21px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}
