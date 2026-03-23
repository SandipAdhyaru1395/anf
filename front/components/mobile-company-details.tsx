"use client"

import { ArrowLeft, User, Home, ShoppingBag, Wallet, Building, Check } from "lucide-react"
import { useCustomer } from "@/components/customer-provider"
import { Banner } from "@/components/banner"
import FloatingInput from "@/components/ui/floating-input"
import { Button } from "@/components/ui/button"
import { useToast } from "@/hooks/use-toast"
import { useState, useEffect } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import api from "@/lib/axios"
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome"
import {
  faBuilding,
  faCircleCheck,
  faGauge,
  faShop,
  faUser,
  faWallet,
  faChevronLeft,
  faChartSimple,
  faHeart
} from "@fortawesome/free-solid-svg-icons";

// Validation schema
const companyDetailsSchema = z.object({
  company_name: z.string().min(1, "Company name is required").max(255, "Company name must be less than 255 characters"),
  address_line1: z.string().min(1, "Address line 1 is required").max(255, "Address line 1 must be less than 255 characters"),
  address_line2: z.string().max(255, "Address line 2 must be less than 255 characters").optional(),
  city: z.string().min(1, "City is required").max(255, "City must be less than 255 characters"),
  country: z.string().max(255, "Country must be less than 255 characters").optional(),
  postcode: z.string().min(1, "Postcode is required").max(255, "Postcode must be less than 255 characters"),
  contact_number: z.string().max(50, "Contact number must be less than 50 characters").optional(),
})

type CompanyDetailsForm = z.infer<typeof companyDetailsSchema>

interface MobileCompanyDetailsProps {
  onNavigate: (page: any, favorites?: boolean) => void
  onBack: () => void
}

export function MobileCompanyDetails({ onNavigate, onBack }: MobileCompanyDetailsProps) {
  const { customer, refresh } = useCustomer()
  const { toast } = useToast()

  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const inputFieldClass =
    "w-full h-[50px] !border-2 !border-[#7FB0EE] rounded-[6px] px-4 text-[14px] focus:ring-2 focus:ring-blue-100 focus:!border-[#4A90E5] focus:outline-none placeholder:text-gray-400 text-gray-700 bg-white transition-all"
  const readOnlyInputClass =
    "w-full h-[50px] !border-2 !border-[#7FB0EE] rounded-[6px] px-4 text-[14px] focus:outline-none placeholder:text-gray-400 text-gray-500 bg-gray-50/50 transition-all cursor-not-allowed"

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
    }
  })

  // Initialize form with customer data
  useEffect(() => {
    if (customer) {
      form.reset({
        company_name: customer.company_name || "",
        address_line1: customer.address_line1 || "",
        address_line2: customer.address_line2 || "",
        city: customer.city || "",
        country: customer.country || "",
        postcode: customer.postcode || "",
        contact_number: (customer as any).phone || "",
      })
      setLoading(false)
    }
  }, [customer, form])

  // Save company details to API (calls updateCompanyDetails function)
  const saveCompanyDetails = async (data: CompanyDetailsForm) => {
    setSaving(true)
    try {
      const response = await api.put('/customer', data)

      if (response.data?.success) {
        toast({
          title: "Success! 🎉",
          description: "Company details updated successfully",
        })
        // Immediately refresh the global customer state so other screens see latest data
        await refresh()
      }
    } catch (error: any) {
      console.error('Failed to save company details:', error)

      // Handle field-specific validation errors from API
      if (error.response?.data?.errors) {
        const apiErrors = error.response.data.errors
        Object.keys(apiErrors).forEach((field) => {
          form.setError(field as keyof CompanyDetailsForm, {
            type: "server",
            message: apiErrors[field][0]
          })
        })
      } else {
        // Generic error for network issues
        toast({
          title: "Error",
          description: "Failed to update company details. Please try again.",
          variant: "destructive",
        })
      }
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className="min-h-screen bg-[#F3F4F8] flex flex-col items-center">
      {/* Top Header */}
      <div className="w-full max-w-[402px] h-[58px] bg-[#EEF0F5] border-b border-[#E2E6EF] flex items-center justify-center relative">
        <button
          type="button"
          onClick={onBack}
          className="absolute left-3 inline-flex items-center gap-1 text-[#8A94A6] text-[13px] font-medium"
        >
          <FontAwesomeIcon icon={faChevronLeft} className="text-[12px]" />
          <span>Back</span>
        </button>
        <h1 className="text-[20px] font-semibold text-[#4E5667] leading-none">My Details</h1>
      </div>

      {/* Container */}
      <div className="w-full max-w-[402px] bg-white px-3 pt-2 pb-2 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col">
        <div className="flex w-full justify-center px-0 py-0 mx-auto">
          <Banner className="h-[80px] w-full max-w-[380px] rounded-[2px]" />
        </div>

        {/* Form Section */}
        <form
          onSubmit={form.handleSubmit(saveCompanyDetails)}
          noValidate
          className="flex flex-col pb-24 pt-3"
        >
          {/* Company Name */}
          <div className="flex flex-col mb-4">
            <label className="text-[#5B6B7A] font-bold text-[14px] mb-2">
              Company Name
            </label>
            <input
              type="text"
              placeholder="Please enter your company name"
              {...form.register("company_name")}
              className={inputFieldClass}
            />
            {form.formState.errors.company_name?.message && (
              <p className="text-red-500 text-[10px] mt-1 ml-1">
                {form.formState.errors.company_name.message}
              </p>
            )}
          </div>

          {/* Company Address Section */}
          <div className="flex flex-col mb-4">
            <label className="text-[#5B6B7A] font-bold text-[14px] mb-2">
              Company Address
            </label>
            <div className="flex flex-col gap-2">
              <input
                type="text"
                placeholder="Invoice address line 1"
                {...form.register("address_line1")}
                className={inputFieldClass}
              />
              <input
                type="text"
                placeholder="Invoice address line 2"
                {...form.register("address_line2")}
                className={inputFieldClass}
              />
              <input
                type="text"
                placeholder="Invoice address city"
                {...form.register("city")}
                className={inputFieldClass}
              />
              <input
                type="text"
                placeholder="Invoice address county"
                {...form.register("country")}
                className={inputFieldClass}
              />
              <input
                type="text"
                placeholder="Invoice address postcode"
                {...form.register("postcode")}
                className={inputFieldClass}
              />
            </div>
          </div>

          {/* Contact Number Section */}
          <div className="flex flex-col mb-4">
            <label className="text-[#5B6B7A] font-bold text-[14px] mb-2">
              Contact Number
            </label>
            <input
              type="text"
              placeholder="Please enter your contact number"
              {...form.register("contact_number")}
              className={inputFieldClass}
            />
          </div>

          {/* Login Details Section */}
          <div className="flex flex-col mb-4">
            <label className="text-[#5B6B7A] font-bold text-[14px] mb-2">
              Login Details
            </label>
            <input
              type="email"
              placeholder="Please enter your email"
              value={customer?.email || ""}
              readOnly
              className={readOnlyInputClass}
            />
          </div>

          {/* Action Buttons */}
          <div className="flex flex-col pt-2">
            <button
              type="submit"
              disabled={saving}
              className="w-full h-[40px] text-white rounded-[20px] font-bold text-[20px] leading-[18px] tracking-[0px] text-center shadow-lg active:scale-[0.98] disabled:opacity-70 transition-all bg-[linear-gradient(0deg,_#2868C0_-107.69%,_#4C92E9_80.77%)]"
            >
              {saving ? "Saving..." : "Save"}
            </button>
          </div>
        </form>
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 z-50 w-full max-w-[402px] -translate-x-1/2 bg-[#F1F2F7] shadow-[0_-4px_16px_-4px_rgba(15,23,42,0.12)]">
        <div className="grid h-[74px] grid-cols-5 items-center border-t border-[#E4E7F0] px-2 pb-[10px] pt-2">
          <button
            type="button"
            onClick={() => onNavigate("dashboard")}
            className="flex flex-col items-center gap-1 text-[11px] font-bold leading-none text-[#BDC7DE]"
          >
            <FontAwesomeIcon icon={faChartSimple} className="text-[23px]" />
            <span>Dashboard</span>
          </button>
          <button
            type="button"
            onClick={() => onNavigate("shop", false)}
            className="flex flex-col items-center gap-1 text-[11px] font-bold leading-none text-[#BDC7DE]"
          >
            <FontAwesomeIcon icon={faShop} className="text-[23px]" />
            <span>Shop</span>
          </button>
          <button
            type="button"
            onClick={() => onNavigate("shop", true)}
            className="flex flex-col items-center gap-1 text-[11px] font-bold leading-none text-[#BDC7DE]"
          >
            <FontAwesomeIcon icon={faHeart} className="text-[23px]" />
            <span>Favourites</span>
          </button>
          <button
            type="button"
            onClick={() => onNavigate("wallet")}
            className="flex flex-col items-center gap-1 text-[11px] font-bold leading-none text-[#BDC7DE]"
          >
            <FontAwesomeIcon icon={faWallet} className="text-[23px]" />
            <span>Wallet</span>
          </button>
          <button
            type="button"
            onClick={() => onNavigate("account")}
            className="flex flex-col items-center gap-1 text-[11px] font-bold leading-none text-[#4A90E5]"
          >
            <FontAwesomeIcon icon={faUser} className="text-[23px]" />
            <span>Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}
