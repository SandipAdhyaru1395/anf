"use client";

import { Button } from "@/components/ui/button";
import { Banner } from "@/components/banner";
import { ChevronRight, User, Building, GitBranch, Lightbulb, BarChart3, FileText, Bell, Shield, Home, ShoppingBag, Wallet, LogOut } from "lucide-react";
import { useRouter } from "next/navigation";
import { buildPath } from "@/lib/utils";
import revoLogoImg from "@/assets/icons/revo-logo.png";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faBarChart, faBell, faBuilding, faChevronRight, faFileSignature, faGauge, faLock, faShop, faStore, faUser, faUserTie, faWallet } from "@fortawesome/free-solid-svg-icons";
import { JSX } from "react";

interface ProductItem {
  id: number;
  name: string;
  image: string;
  price: string;
  discount?: string;
}

interface MobileAccountProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account" | "rep-details" | "company-details" | "branches") => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  increment: (product: ProductItem) => void;
  decrement: (product: ProductItem) => void;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  clearCart: () => void;
}

export function MobileAccount({ onNavigate }: MobileAccountProps) {
  const router = useRouter();

  const revoLogo: JSX.Element = <img src={revoLogoImg.src} className="w-[20px] h-[20px] overflow-visible" alt="Revo Logo" />;

  const handleLogout = async () => {
    try {
      try {
        await api.post("/logout");
      } catch {}
      try {
        window.localStorage.removeItem("auth_token");
      } catch {}
    } finally {
      try {
        router.replace(buildPath("/login"));
      } catch {
        if (typeof window !== "undefined") {
          window.location.href = buildPath("/login");
        }
      }
    }
  };

  return (
    <div className="w-full max-w-[1000px] mx-auto bg-white min-h-screen">
      {/* Header */}
      <div className="h-[50px] bg-white flex items-center">
        <div className="w-[66px] h-8 rounded-full flex items-center justify-center">
          <FontAwesomeIcon icon={faUser} className="text-green-600" style={{ width: "30px", height: "24px" }} />
        </div>
        <h1 className="text-lg font-semibold text-black-600 text-[16px]">Account</h1>
      </div>

      {/* Banner */}
      <Banner />

      {/* Account Menu Items */}
      <div className="p-[10px] mb-[80px]">
        {/* Account Details Section */}
        <Button variant="outline" onClick={() => onNavigate("rep-details")} className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] flex items-center justify-center">
              <FontAwesomeIcon icon={faUserTie} className="text-green-600" style={{ width: "14px", height: "16px" }} />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">My Rep Details</span>
          </div>
          <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
        </Button>

        <Button variant="outline" onClick={() => onNavigate("company-details")} className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] rounded-full flex items-center justify-center">
              <FontAwesomeIcon icon={faBuilding} className="text-green-600" style={{ width: "12px", height: "16px" }} />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">My Company</span>
          </div>
          <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
        </Button>

        <Button variant="outline" onClick={() => onNavigate("branches")} className="h-[42px] w-full p-[12px] justify-between text-left border border-green-600 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] rounded-full flex items-center justify-center">
              <FontAwesomeIcon icon={faStore} className="text-green-600" style={{ width: "18px", height: "16px" }} />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">My Branches</span>
          </div>
          <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
        </Button>

        <hr className="mt-[20px] mb-[20px]"></hr>

        {/* Utilities Section */}
        <Button variant="outline" className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[20px] rounded-full flex items-center justify-center ">{revoLogo}</div>
            <span className="text-[14px] font-semibold ml-[10px]">Revo Utilities - guaranteed to reduce your bills!</span>
          </div>
          <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
        </Button>

        {/* Services Section */}
        <Button variant="outline" className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] rounded-full flex items-center justify-center">
              <FontAwesomeIcon icon={faBarChart} className="text-green-600" style={{ width: "20px", height: "16px" }} />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">Services & Display Solutions</span>
          </div>
          <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
        </Button>

        <Button variant="outline" className="h-[42px] w-full p-[12px] justify-between text-left border border-green-600 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] rounded-full flex items-center justify-center">
              <FontAwesomeIcon icon={faFileSignature} className="text-green-600" style={{ width: "16px", height: "16px" }} />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">Contracts</span>
          </div>
          <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
        </Button>

        <hr className="mt-[20px] mb-[20px]"></hr>

        {/* Settings Section */}

        <Button variant="outline" className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] rounded-full flex items-center justify-center">
              <FontAwesomeIcon icon={faBell} className="text-green-600" style={{ width: "16px", height: "16px" }} />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">My Notification Preferences</span>
          </div>
          <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
        </Button>

        <Button variant="outline" className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] rounded-full flex items-center justify-center">
              <FontAwesomeIcon icon={faLock} className="text-green-600" style={{ width: "16px", height: "16px" }} />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">My Authentication Settings</span>
          </div>
          <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
        </Button>

        <Button variant="outline" className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 bg-transparent hover:cursor-pointer" onClick={handleLogout}>
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] rounded-full flex items-center justify-center">
              <LogOut className="w-4 h-4 text-green-600" />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">Logout</span>
          </div>
          <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
        </Button>
      </div>

      {/* Bottom Navigation */}
      {/* <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t footer-nav">
        <div className="grid grid-cols-4 py-3 footer-nav-col">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center text-gray-400 hover:cursor-pointer">
            <Home className="w-7 h-7 mb-1" />
            <span className="text-xs">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center text-gray-400 hover:cursor-pointer">
            <ShoppingBag className="w-7 h-7 mb-1" />
            <span className="text-xs">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center text-gray-400 hover:cursor-pointer">
            <Wallet className="w-7 h-7 mb-1" />
            <span className="text-xs">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center text-green-600 hover:cursor-pointer">
            <User className="w-7 h-7 mb-1" />
            <span className="text-xs">Account</span>
          </button>
        </div>
      </nav> */}

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
