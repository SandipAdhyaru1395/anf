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
    <div className="min-h-screen bg-[#F8F7FC] flex flex-col items-center p-4">
      {/* Container */}
      <div className="w-full max-w-[402px] bg-white p-8 rounded-[4px] shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col">
        {/* Logo Section */}
        <div className="flex justify-center mb-8">
          <img
            src="/assets/img/logo.png"
            alt="AQUAVAPE"
            className="w-48 h-auto object-contain"
          />
        </div>

        {/* Form Section */}
        <form
          onSubmit={form.handleSubmit(saveCompanyDetails)}
          noValidate
          className="flex flex-col"
        >
          {/* Company Name */}
          <div className="flex flex-col mb-6">
            <label className="text-[#5B6B7A] font-bold text-[14px] mb-2">
              Company Name
            </label>
            <input
              type="text"
              placeholder="Please enter your company name"
              {...form.register("company_name")}
              className="w-full h-[50px] border-[#A5C9F5] border-[1.5px] rounded-[5px] px-4 text-[14px] focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder:text-gray-400 text-gray-700 bg-white transition-all shadow-sm"
            />
            {form.formState.errors.company_name?.message && (
              <p className="text-red-500 text-[10px] mt-1 ml-1">
                {form.formState.errors.company_name.message}
              </p>
            )}
          </div>

          {/* Company Address Section */}
          <div className="flex flex-col mb-6">
            <label className="text-[#5B6B7A] font-bold text-[14px] mb-2">
              Company Address
            </label>
            <div className="flex flex-col gap-2">
              <input
                type="text"
                placeholder="Invoice address line 1"
                {...form.register("address_line1")}
                className="w-full h-[50px] border-[#A5C9F5] border-[1.5px] rounded-[5px] px-4 text-[14px] focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder:text-gray-400 text-gray-700 bg-white transition-all shadow-sm"
              />
              <input
                type="text"
                placeholder="Invoice address line 2"
                {...form.register("address_line2")}
                className="w-full h-[50px] border-[#A5C9F5] border-[1.5px] rounded-[5px] px-4 text-[14px] focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder:text-gray-400 text-gray-700 bg-white transition-all shadow-sm"
              />
              <input
                type="text"
                placeholder="Invoice address city"
                {...form.register("city")}
                className="w-full h-[50px] border-[#A5C9F5] border-[1.5px] rounded-[5px] px-4 text-[14px] focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder:text-gray-400 text-gray-700 bg-white transition-all shadow-sm"
              />
              <input
                type="text"
                placeholder="Invoice address county"
                {...form.register("country")}
                className="w-full h-[50px] border-[#A5C9F5] border-[1.5px] rounded-[5px] px-4 text-[14px] focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder:text-gray-400 text-gray-700 bg-white transition-all shadow-sm"
              />
              <input
                type="text"
                placeholder="Invoice address postcode"
                {...form.register("postcode")}
                className="w-full h-[50px] border-[#A5C9F5] border-[1.5px] rounded-[5px] px-4 text-[14px] focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder:text-gray-400 text-gray-700 bg-white transition-all shadow-sm"
              />
            </div>
          </div>

          {/* Contact Number Section */}
          <div className="flex flex-col mb-6">
            <label className="text-[#5B6B7A] font-bold text-[14px] mb-2">
              Contact Number
            </label>
            <input
              type="text"
              placeholder="Please enter your contact number"
              {...form.register("contact_number")}
              className="w-full h-[50px] border-[#A5C9F5] border-[1.5px] rounded-[5px] px-4 text-[14px] focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder:text-gray-400 text-gray-700 bg-white transition-all shadow-sm"
            />
          </div>

          {/* Login Details Section */}
          <div className="flex flex-col mb-10">
            <label className="text-[#5B6B7A] font-bold text-[14px] mb-2">
              Login Details
            </label>
            <input
              type="email"
              placeholder="Please enter your email"
              value={customer?.email || ""}
              readOnly
              className="w-full h-[50px] border-[#A5C9F5] border-[1.5px] rounded-[5px] px-4 text-[14px] focus:outline-none placeholder:text-gray-400 text-gray-500 bg-gray-50/50 transition-all shadow-sm cursor-not-allowed"
            />
          </div>

          {/* Action Buttons */}
          <div className="flex flex-col gap-4">
            <button
              type="submit"
              disabled={saving}
              className="w-full h-[54px] bg-[#4A90E5] text-white rounded-[27px] font-bold text-[17px] shadow-lg active:scale-[0.98] disabled:opacity-70 transition-all"
            >
              {saving ? "Signing in..." : "Agree & Sign Up"}
            </button>

            <button
              type="button"
              onClick={onBack}
              className="w-full h-[54px] bg-white border-2 border-[#4A90E5] text-[#4A90E5] rounded-[27px] font-bold text-[17px] active:scale-[0.98] transition-all"
            >
              Back
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
