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
import { faBuilding, faCircleCheck, faGauge, faShop, faUser, faWallet } from "@fortawesome/free-solid-svg-icons"

// Validation schema
const companyDetailsSchema = z.object({
  company_name: z.string().min(1, "Company name is required").max(255, "Company name must be less than 255 characters"),
  address_line1: z.string().min(1, "Address line 1 is required").max(255, "Address line 1 must be less than 255 characters"),
  address_line2: z.string().max(255, "Address line 2 must be less than 255 characters").optional(),
  city: z.string().min(1, "City is required").max(255, "City must be less than 255 characters"),
  country: z.string().max(255, "Country must be less than 255 characters").optional(),
  postcode: z.string().min(1, "Postcode is required").max(255, "Postcode must be less than 255 characters"),
})

type CompanyDetailsForm = z.infer<typeof companyDetailsSchema>

interface MobileCompanyDetailsProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account") => void
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
    <div className="w-full max-w-[1000px] mx-auto min-h-screen">
      {/* Header */}
      <div className="bg-white flex items-center border-b h-[50px]">
        {/* <button onClick={onBack} className="p-2 hover:bg-gray-100 hover:cursor-pointer rounded-full">
                <ArrowLeft className="w-5 h-5 text-gray-600" />
              </button> */}
        <div className="flex items-center">
          <div className="w-[66px] h-[25px] rounded-full flex items-center justify-center">
            <FontAwesomeIcon icon={faBuilding} className="text-green-600" style={{ width: "21px", height: "24px" }} />
          </div>
          <span onClick={onBack} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline">Account</span>
          &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
          &nbsp;<span className="text-[16px] font-semibold">My Company</span>
        </div>
      </div>

      {/* Banner */}
      <Banner />

      {/* Company Details Form */}
      <div className="bg-white p-[10px] mb-[82px]">
        {loading ? (
          <div className="p-4">
            <div className="animate-pulse space-y-4">
              <div className="h-4 bg-gray-200 rounded w-1/4"></div>
              <div className="h-10 bg-gray-200 rounded"></div>
              <div className="h-4 bg-gray-200 rounded w-1/3"></div>
              <div className="space-y-3">
                <div className="h-10 bg-gray-200 rounded"></div>
                <div className="h-10 bg-gray-200 rounded"></div>
                <div className="h-10 bg-gray-200 rounded"></div>
                <div className="h-10 bg-gray-200 rounded"></div>
                <div className="h-10 bg-gray-200 rounded"></div>
              </div>
            </div>
          </div>
        ) : (
          <>
            {/* Company Name Section */}
            <FloatingInput
              label="Company Name"
              placeholder="Please enter your company name..."
              {...form.register("company_name")}
              error={form.formState.errors.company_name?.message as string}
            />
            <hr className="my-[20px]" />
            {/* Invoice Address Section */}
            <p className="text-[16px] my-[16px] leading-[16px]">Invoice Address</p>

            {/* Line 1 */}
            <FloatingInput
              label="Line 1"
              placeholder="Please enter invoice address line 1.."
              {...form.register("address_line1")}
              error={form.formState.errors.address_line1?.message as string}
            />

            {/* Line 2 */}
            <FloatingInput
              label="Line 2"
              placeholder="Please enter invoice address line 2..."
              {...form.register("address_line2")}
              error={form.formState.errors.address_line2?.message as string}
            />

            {/* City */}
            <FloatingInput
              label="City"
              placeholder="Please enter invoice address city..."
              {...form.register("city")}
              error={form.formState.errors.city?.message as string}
            />

            {/* Country */}
            <FloatingInput
              label="Country"
              placeholder="Please enter invoice address county..."
              {...form.register("country")}
              error={form.formState.errors.country?.message as string}
            />
           

            {/* Postcode */}
            <FloatingInput
              label="Postcode"
              placeholder="Please enter invoice address postcode..."
              {...form.register("postcode")}
              error={form.formState.errors.postcode?.message as string}
            />
            <hr className="my-[20px]" />
            {/* Save Button */}
              <Button
                onClick={form.handleSubmit(saveCompanyDetails)}
                disabled={saving}
                className="w-full gap-0 cursor-pointer leading-[16px] h-[45px] bg-green-600 text-[16px] text-white !p-[14px] rounded font-medium flex items-center justify-center"
              >
                <FontAwesomeIcon className="mx-[4px] leading-[16px]" icon={faCircleCheck}  style={{ width: "16px", height: "16px" }} />
                <span className="mx-[4px] font-semibold leading-[16px]">
                  {saving ? "Saving..." : "Save"}
                  </span>
              </Button>
          </>
        )}
      </div>

       {/* Bottom Navigation */}
            <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 px-[18px]">
              <div className="flex flex-row items-center justify-between h-[72px] footer-nav-col">
                <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
                  <FontAwesomeIcon icon={faGauge} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
                  <span className="text-xs mt-[5px]">Dashboard</span>
                </button>
                <button onClick={() => onNavigate("shop", false)} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
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
  )
}
