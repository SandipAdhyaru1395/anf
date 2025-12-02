"use client";

import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { Button } from "@/components/ui/button";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
    faChevronLeft,
    faCircleCheck,
    faCircleXmark,
    faGauge,
    faShop,
    faUser,
    faWallet,
} from "@fortawesome/free-solid-svg-icons";
import { Banner } from "./banner";
import FloatingInput from "./ui/floating-input";
import api from "@/lib/axios";
import { useToast } from "@/hooks/use-toast";

// Validation schema
const branchSchema = z.object({
    name: z.string().min(1, "Branch name is required"),
    line1: z.string().min(1, "Address line 1 is required"),
    line2: z.string().optional(),
    city: z.string().min(1, "City is required"),
    county: z.string().optional(),
    postcode: z.string().min(1, "Postcode is required"),
});

type BranchFormData = z.infer<typeof branchSchema>;

interface MobileNewBranchProps {
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
    onBranchSaved?: () => void;
}

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

                // Call the callback to refresh the branches list
                if (onBranchSaved) {
                    onBranchSaved();
                }

                onBack();
            } else {
                toast({
                    title: "Error",
                    description: "Failed to save branch",
                    variant: "destructive",
                });
            }
        } catch (error: any) {
            console.error("Error saving branch:", error);

            // Handle validation errors
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
        <div className="w-full max-w-[1000px] mx-auto bg-white min-h-screen">
            {/* Header */}
            <div className="bg-white flex items-center border-b h-[50px]">
                <div className="flex items-center">
                    <div className="w-[66px] h-[25px] rounded-full flex items-center justify-center">
                        <FontAwesomeIcon icon={faShop} className="text-green-600" style={{ width: "27px", height: "24px" }} />
                    </div>
                    <span onClick={() => onNavigate("account")} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline leading-[16px]">Account</span>
                    &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
                    &nbsp;<span onClick={onBack} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline leading-[16px]">My Branches</span>
                    &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
                    &nbsp;<span className="text-[16px] font-semibold">New Branch</span>
                </div>
            </div>
            {/* Banner */}
            <Banner />
            {/* Content */}
            <form onSubmit={handleSubmit(onSubmit)} className="p-[10px] mb-[82px]">
                <FloatingInput
                    label="Branch Name"
                    placeholder="Please enter your branch name..."
                    {...register("name")}
                    error={errors.name?.message}
                />
                <hr className="my-[20px]"></hr>
                <FloatingInput
                    label="Line 1"
                    placeholder="Please enter address line 1..."
                    {...register("line1")}
                    error={errors.line1?.message}
                />

                <FloatingInput
                    label="Line 2"
                    placeholder="Please enter address line 2..."
                    {...register("line2")}
                    error={errors.line2?.message}
                />

                <FloatingInput
                    label="City"
                    placeholder="Please enter address city..."
                    {...register("city")}
                    error={errors.city?.message}
                />

                <FloatingInput
                    label="Country"
                    placeholder="Please enter address country..."
                    {...register("county")}
                    error={errors.county?.message}
                />

                <FloatingInput
                    label="Postcode"
                    placeholder="Please enter address postcode..."
                    {...register("postcode")}
                    error={errors.postcode?.message}
                />
                <hr className="my-[20px]"></hr>
                {/* Buttons */}
                <Button
                    type="submit"
                    disabled={isLoading}
                    className="w-full cursor-pointer rounded bg-green-600 text-white font-semibold h-[45px] !leading-[13px] disabled:opacity-50"
                >
                    <div className="!leading-[13px]">
                        <FontAwesomeIcon icon={faCircleCheck} style={{ width: "16px", height: "16px" }} />
                    </div>
                    <span className="text-[16px]">{isLoading ? "Saving..." : "Save"}</span>
                </Button>
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
