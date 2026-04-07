"use client";

import { useCustomer } from "@/components/customer-provider";
import { useRouter } from "next/navigation";
import { buildPath } from "@/lib/utils";
import { Banner } from "@/components/banner";
import { MobilePageHeader } from "@/components/mobile-page-header";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChartSimple, faHeart, faShop, faUser, faWallet } from "@fortawesome/free-solid-svg-icons";
import {
  ChevronRight,
  FileText,
  Home,
  LogOut,
  MessageCircle,
  ShoppingBag,
  User,
  Wallet,
  type LucideIcon,
} from "lucide-react";

interface MobileAccountProps {
  onNavigate: (page: any, favorites?: boolean) => void;
  cart: Record<number, { product: any; quantity: number }>;
  increment: (product: any) => void;
  decrement: (product: any) => void;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  clearCart: () => void;
}

export function MobileAccount(props: MobileAccountProps) {
  const { onNavigate } = props;
  const router = useRouter();
  const { customer } = useCustomer();

  const displayName = "Customer Name";
  const displayEmail = customer?.email?.trim() || "example@gmail.com";

  const handleLogout = async () => {
    try {
      try {
        await api.post("/logout");
      } catch { /* empty */ }
      try {
        window.localStorage.removeItem("auth_token");
      } catch { /* empty */ }
    } finally {
      try {
        router.replace(buildPath("/login"));
      } catch {
        window.location.href = buildPath("/login");
      }
    }
  };

  const menuItems: { label: string; icon: LucideIcon; action: () => void }[] = [
    { label: "My Details", icon: User, action: () => onNavigate("company-details") },
    { label: "My Branches", icon: Home, action: () => onNavigate("branches") },
    { label: "My Orders", icon: ShoppingBag, action: () => onNavigate("orders") },
    // { label: "Payment Methods", icon: CreditCard, action: () => onNavigate("PaymentResultHandler") },
    { label: "My Wallet", icon: Wallet, action: () => onNavigate("wallet") },
    { label: "Contact Us", icon: MessageCircle, action: () => onNavigate("contact-us") },
    // { label: "About Us", icon: CircleHelp, action: () => onNavigate("about-us") },
    // { label: "Authentication", icon: Lock, action: () => onNavigate("authentication") },
    // { label: "Settings", icon: Settings, action: () => onNavigate("settings") },
    { label: "Terms And Conditions", icon: FileText, action: () => onNavigate("terms-and-conditions") },
  ];
  const labelWidthMap: Record<string, string> = {
    "My Details": "w-[75px]",
    "My Branches": "w-[95px]",
  };

  return (
    <div className="flex h-[100dvh] w-full justify-center overflow-hidden bg-[#FFFFFF]">
      <div
        className="relative mx-auto h-full min-h-0 w-full max-w-[402px] overflow-hidden bg-[#FFFFFF] lg:max-h-[1024px] lg:max-w-[1000px]"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <MobilePageHeader title="Account" onBack={() => onNavigate("dashboard")} noTopPadding />

        <main className="scrollbar-hide absolute inset-x-0 bottom-[64px] top-[96px] w-full overflow-x-hidden overflow-y-auto bg-[#FFFFFF] pb-[80px] pt-0 lg:pb-[60px]">
          <div className="mx-auto flex w-full flex-col gap-[8px] px-[16px] pb-[16px] lg:gap-[3px]">
            <div className="shrink-0">
              <Banner className="mx-auto w-full rounded-[10px] border border-[#E2E2E2] bg-white lg:h-[242px] lg:max-w-[968px] lg:border-0 lg:!rounded-[10px]" />
            </div>

            <div className="flex h-[93px] w-full items-center gap-[16px] px-0 py-[20px]">
              <div className="flex h-[53px] w-[58px] shrink-0 items-center justify-center">
                <div className="flex h-[53px] w-[53px] items-center justify-center rounded-full bg-[#4A90E5]">
                  <User size={30} strokeWidth={1.75} className="text-white" aria-hidden />
                </div>
              </div>
              <div className="flex h-[41px] min-w-0 flex-1 flex-col justify-center">
                <span className="truncate text-[20px] font-bold leading-[18px] tracking-[0] text-[#181725]">{displayName}</span>
                <span className="mt-[5px] truncate text-[16px] font-normal leading-[18px] tracking-[0] text-[#7C7C7C]">{displayEmail}</span>
              </div>
            </div>
            <div className="mx-auto flex w-full flex-col bg-white px-0">
              {menuItems.map((item) => (
                <button
                  key={item.label}
                  type="button"
                  onClick={item.action}
                  className="mx-auto box-border ml-0 flex w-full items-center justify-between border-b border-[#E2E2E2] bg-white px-[13px] py-[18px] text-left transition-colors hover:bg-[#F7F9FC] active:bg-[#EEF3FB]"
                >
                  <div className="flex h-[18px] min-w-0 items-center gap-[15px]">
                    <item.icon
                      className="h-[23px] w-[23px] shrink-0 text-[#3D495E]"
                      strokeWidth={0.8}
                      aria-hidden
                    />
                    <span
                      className={`inline-flex h-[18px] items-center text-[15px] font-[700] capitalize leading-[100%] tracking-[0.03em] text-[#3D495E] ${labelWidthMap[item.label] ?? "w-auto"}`}
                    >
                      {item.label}
                    </span>
                  </div>
                  <div className="flex shrink-0 items-center justify-center pl-0 pr-[2px]">
                    <ChevronRight className="h-[18px] w-[18px] shrink-0 text-[#B0B0B0]" strokeWidth={2} aria-hidden />
                  </div>
                </button>
              ))}
            </div>

            <div className="flex w-full justify-center pt-[14px]">
              <button
                type="button"
                onClick={handleLogout}
                className="relative box-border flex h-[49.62px] w-full max-w-[386.6px] items-center justify-center gap-[11px] rounded-[19px] bg-[linear-gradient(0deg,_#2868C0_-107.69%,_#4C92E9_80.77%)] px-[26px] py-[15px] lg:w-[386.6px]"
              >
                <LogOut className="absolute left-[26px] h-[18px] w-[18px] text-white" strokeWidth={2} aria-hidden />
                <span className="text-center text-[18px] font-bold leading-[18px] tracking-[0px] text-white">
                  Log Out
                </span>
              </button>
            </div>
          </div>
        </main>

        <nav
          className="fixed bottom-0 left-1/2 z-50 box-border flex h-[64px] w-full max-w-[402px] -translate-x-1/2 flex-col rounded-tl-[10px] rounded-tr-[10px] bg-[#F6F4FA] px-[43px] pb-[16px] pt-[8px] shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)] lg:max-w-[1000px]"
          aria-label="Main navigation"
          style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
        >
          <div className="flex min-h-0 w-full flex-1 items-center justify-center">
            <div className="flex h-[40px] w-[316px] max-w-full items-center justify-between">
              <button type="button" onClick={() => onNavigate("dashboard")} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
                <FontAwesomeIcon icon={faChartSimple} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
                <span className="text-center text-[10px] font-bold leading-none tracking-normal">Dashboard</span>
              </button>
              <button type="button" onClick={() => onNavigate("shop", false)} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
                <FontAwesomeIcon icon={faShop} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
                <span className="text-center text-[10px] font-bold leading-none tracking-normal">Shop</span>
              </button>
              <button type="button" onClick={() => onNavigate("shop", true)} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
                <FontAwesomeIcon icon={faHeart} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
                <span className="text-center text-[10px] font-bold leading-none tracking-normal">Favourites</span>
              </button>
              <button type="button" onClick={() => onNavigate("wallet")} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
                <FontAwesomeIcon icon={faWallet} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
                <span className="text-center text-[10px] font-bold leading-none tracking-normal">Wallet</span>
              </button>
              <button type="button" onClick={() => onNavigate("account")} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 text-[#4A90E5]" aria-current="page">
                <FontAwesomeIcon icon={faUser} className="h-[23px] w-[23px] shrink-0 text-[23px] leading-none text-[#4A90E5]" aria-hidden />
                <span className="text-center text-[11px] font-medium leading-none tracking-normal text-[#4A90E5]">Account</span>
              </button>
            </div>
          </div>
        </nav>
      </div>
    </div>
  );
}
