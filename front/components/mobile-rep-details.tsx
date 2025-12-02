"use client";

import { ArrowLeft, User, Home, ShoppingBag, Wallet } from "lucide-react";
import { useCustomer } from "@/components/customer-provider";
import { Banner } from "@/components/banner";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faUser, faUserTie, faWallet } from "@fortawesome/free-solid-svg-icons";

interface MobileRepDetailsProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account") => void;
  onBack: () => void;
}

export function MobileRepDetails({ onNavigate, onBack }: MobileRepDetailsProps) {
  const { customer } = useCustomer();

  const getInitials = (name: string) => {
    return name
      .split(" ")
      .map((word) => word.charAt(0))
      .join("")
      .toUpperCase()
      .slice(0, 2);
  };

  return (
    <div className="w-full max-w-[1000px] mx-auto">
      {/* Header */}
      <div className="bg-white flex items-center border-b h-[50px]">
        {/* <button onClick={onBack} className="p-2 hover:bg-gray-100 hover:cursor-pointer rounded-full">
          <ArrowLeft className="w-5 h-5 text-gray-600" />
        </button> */}
        <div className="flex items-center">
          <div className="w-[66px] h-[25px] rounded-full flex items-center justify-center">
            <FontAwesomeIcon icon={faUserTie} className="text-green-600" style={{ width: "21px", height: "24px" }} />
          </div>
          <span onClick={onBack} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline">Account</span>
          &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
          &nbsp;<span className="text-[16px] font-semibold">My Rep Details</span>
        </div>
      </div>

      {/* Banner */}
      <Banner />

      {/* Rep Details Section */}
      <div className="bg-white p-[10px] pt-[20px] mb-[82px]">
        {/* Header with Avatar and Name */}
        <div className="border-b border-gray-100 mb-[24px]">
          <div className="flex items-center">
            <div className="mr-[8px] w-[72px]  h-[72px] bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
              <span className="text-white text-[16px]">{getInitials(customer?.rep_name || "RE")}</span>
            </div>
            <div>
              <h2 className="text-[1.5em] font-bold text-gray-900">{customer?.rep_name || "Your Representative"}</h2>
            </div>
          </div>
        </div>

        <hr className="my-[20px]"></hr>
        {/* Contact Information */}
        {/* Phone Number */}
        <div className="mb-[24px] leading-[16px] font-semibold">
          <p className="text-[16px] mb-[8px]">Phone Number</p>
          <p className="text-[16px] text-green-600 ">{customer?.rep_mobile || "Contact your representative"}</p>
        </div>

        {/* Email */}
        <div className="mb-[24px] leading-[16px] font-semibold">
          <p className="text-[16px] mb-[8px]">Email</p>
          <p className="text-[16px] text-green-600">{customer?.rep_email || "rep@company.com"}</p>
        </div>
        <hr className="my-[20px]"></hr>
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
  );
}
