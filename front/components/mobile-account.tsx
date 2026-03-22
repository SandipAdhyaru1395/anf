"use client";

import { Banner } from "@/components/banner";
import { useRouter } from "next/navigation";
import { buildPath } from "@/lib/utils";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faChartSimple,
  faHeart,
  faShop,
  faUser,
  faWallet,
} from "@fortawesome/free-solid-svg-icons";
import {
  ChevronLeft,
  Building2,
  ChevronRight,
  CircleHelp,
  CreditCard,
  FileText,
  Home,
  Lock,
  MessageCircle,
  Settings,
  ShoppingBag,
  User,
  Wallet,
} from "lucide-react";

interface MobileAccountProps {
  onNavigate: (page: any, favorites?: boolean) => void;
  cart: Record<number, { product: any; quantity: number }>;
  increment: (product: any) => void;
  decrement: (product: any) => void;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  clearCart: () => void;
}

export function MobileAccount({ onNavigate, cart, increment, decrement, totals, clearCart }: MobileAccountProps) {
  const router = useRouter();

  const handleLogout = async () => {
    try {
      try {
        await api.post("/logout");
      } catch { }
      try {
        window.localStorage.removeItem("auth_token");
      } catch { }
    } finally {
      try {
        router.replace(buildPath("/login"));
      } catch {
        window.location.href = buildPath("/login");
      }
    }
  };

  const menuItems = [
    { label: "My Details", icon: User, action: () => onNavigate("company-details") },
    { label: "My Branches", icon: Home, action: () => onNavigate("branches") },
    { label: "My Orders", icon: ShoppingBag, action: () => onNavigate("orders") },
    { label: "Payment Methods", icon: CreditCard, action: () => onNavigate("PaymentResultHandler") },
    { label: "My Wallet", icon: Wallet, action: () => onNavigate("wallet") },
    { label: "Contact Us", icon: MessageCircle, action: () => onNavigate("contact-us") },
    { label: "About Us", icon: CircleHelp, action: () => onNavigate("about-us") },
    { label: "Authentication", icon: Lock, action: () => onNavigate("authentication") },
    { label: "Settings", icon: Settings, action: () => onNavigate("settings") },
    { label: "Terms And Conditions", icon: FileText, action: () => onNavigate("terms-and-conditions") },
  ];

  return (
    <div className="min-h-screen flex flex-col mx-auto w-full max-w-[402px] bg-white relative shadow-sm">
      {/* HEADER */}
      <header className="w-full h-[60px] bg-white flex items-center justify-center border-b border-[#F1F2F7] sticky top-0 z-50">
        <button
          onClick={() => onNavigate("dashboard")}
          className="absolute left-3 text-[#64748B] text-[13px] flex items-center font-medium"
          type="button"
        >
          <ChevronLeft size={18} strokeWidth={2} className="mr-0.5" />
          <span>Back</span>
        </button>
        <h1 className="text-[17px] font-bold text-[#3D495E]">Account</h1>
      </header>

      {/* SCROLLABLE CONTENT */}
      <main className="w-full flex-1 overflow-y-auto pb-[130px] bg-white">
        {/* Banner */}
        <div className="px-3 py-3 relative z-0">
          <Banner />
        </div>

        {/* Profile Info */}
        <div className="flex items-center gap-[14px] px-4 py-[6px] w-full bg-white mb-2">
          <div className="w-[54px] h-[54px] rounded-full bg-[#4A90E5] flex items-center justify-center text-white flex-shrink-0">
            <User size={30} strokeWidth={2} />
          </div>
          <div className="flex flex-col">
            <span className="text-[15.5px] font-bold text-[#3D495E] leading-tight">Customer Name</span>
            <span className="text-[13px] text-[#8A94A6]">example@gmail.com</span>
          </div>
        </div>

        {/* Menu Items */}
        <div className="bg-white w-full">
          {menuItems.map((item, i) => (
            <div
              key={i}
              onClick={item.action}
              className="flex items-center justify-between px-4 py-[14px] border-b border-[#F1F2F7] cursor-pointer hover:bg-gray-50 transition-colors"
            >
              <div className="flex items-center gap-[14px]">
                <item.icon size={19} strokeWidth={1.8} className="text-[#3D495E]" />
                <span className="text-[14.5px] font-bold text-[#3D495E]">{item.label}</span>
              </div>
              <ChevronRight size={18} strokeWidth={2} className="text-[#64748B] opacity-70" />
            </div>
          ))}

          {/* Logout Item */}
          <div
            onClick={handleLogout}
            className="flex items-center justify-between px-4 py-[14px] border-b border-[#F1F2F7] cursor-pointer hover:bg-gray-50 transition-colors"
          >
            <div className="flex items-center gap-[14px]">
              <Building2 size={19} strokeWidth={1.8} className="text-[#3D495E]" />
              <span className="text-[14.5px] font-bold text-[#3D495E]">Logout</span>
            </div>
            <ChevronRight size={18} strokeWidth={2} className="text-[#64748B] opacity-70" />
          </div>
        </div>
      </main>

      {/* Floating P Buttons */}
      <div className="fixed bottom-[140px] left-1/2 w-full max-w-[402px] -translate-x-1/2 pointer-events-none z-40">
        <div className="absolute right-[16px] pointer-events-auto w-[46px] h-[46px] bg-[#0B87E8] rounded-full flex items-center justify-center text-white text-[22px] font-bold shadow-[0_2px_10px_0_rgba(11,135,232,0.4)] border-[3px] border-white cursor-pointer">
          P
        </div>
      </div>
      <div className="fixed bottom-[84px] left-1/2 w-full max-w-[402px] -translate-x-1/2 pointer-events-none z-40">
        <div className="absolute right-[16px] pointer-events-auto w-[46px] h-[46px] bg-[#0B87E8] rounded-full flex items-center justify-center text-white text-[22px] font-bold shadow-[0_2px_10px_0_rgba(11,135,232,0.4)] border-[3px] border-white cursor-pointer">
          P
        </div>
      </div>

      {/* BOTTOM NAV */}
      <nav className="fixed bottom-0 left-1/2 w-full max-w-[402px] -translate-x-1/2 h-[74px] px-2 pt-[8px] pb-[10px] grid grid-cols-5 items-center bg-[#F1F2F7] border-t border-[#E4E7F0] shadow-[0px_-1px_8px_0px_#555E5814] z-50">
        <button
          onClick={() => onNavigate("dashboard")}
          className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none"
        >
          <FontAwesomeIcon icon={faChartSimple} className="text-[23px]" />
          <span>Dashboard</span>
        </button>
        <button
          onClick={() => onNavigate("shop")}
          className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none"
        >
          <FontAwesomeIcon icon={faShop} className="text-[23px]" />
          <span>Shop</span>
        </button>
        <button
          onClick={() => onNavigate("shop", true)}
          className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none"
        >
          <FontAwesomeIcon icon={faHeart} className="text-[23px]" />
          <span>Favourites</span>
        </button>

        <button
          onClick={() => onNavigate("wallet")}
          className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none"
        >
          <FontAwesomeIcon icon={faWallet} className="text-[23px]" />
          <span>Wallet</span>
        </button>
        <button
          onClick={() => onNavigate("account")}
          className="flex flex-col items-center gap-[4px] text-[#4A90E5] text-[11px] font-bold leading-none"
        >
          <FontAwesomeIcon icon={faUser} className="text-[23px]" />
          <span>Account</span>
        </button>
      </nav>
    </div>
  );
}
