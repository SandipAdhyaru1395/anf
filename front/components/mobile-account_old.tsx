"use client";

import { useSettings } from "@/components/settings-provider";
import { useCustomer } from "@/components/customer-provider";
import { useRouter } from "next/navigation";
import { buildPath, resolveBackendAssetUrl } from "@/lib/utils";
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
  Building2,
  ChevronRight,
  FileText,
  Home,
  MessageCircle,
  ShoppingBag,
  User,
  Wallet,
  type LucideIcon,
} from "lucide-react";
import { MobilePageHeader } from "@/components/mobile-page-header";

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
  const { settings } = useSettings();
  const { customer } = useCustomer();

  const bannerSrc = resolveBackendAssetUrl(settings?.banner) ?? settings?.banner ?? null;
  const displayName = customer?.name?.trim() || customer?.company_name?.trim() || "Customer Name";
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

  return (
    <div
      className="relative mx-auto flex h-[100dvh] min-h-0 w-full max-w-[402px] flex-col bg-[#FAFBFD]"
      style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
    >
      <MobilePageHeader variant="plain" title="Account" onBack={() => onNavigate("dashboard")} />

      {/* Figma frame 402×874 area: #FAFBFD; content column 370 fill; px-4 (16) sides; pb clears nav */}
      <main className="scrollbar-hide min-h-0 w-full flex-1 overflow-x-hidden overflow-y-auto bg-[#FAFBFD] px-4 pb-[72px] pt-0">
        <div className="mx-auto flex w-full max-w-[370px] flex-col gap-3">
          {bannerSrc ? (
            <div className="shrink-0 pt-2">
              <div className="h-[93px] w-full overflow-hidden rounded-[2px] border border-[#E2E2E2] bg-white">
                <img src={bannerSrc} alt="Promotional banner" className="h-full w-full object-cover object-center" />
              </div>
            </div>
          ) : null}

          {/* Frame 56: py 20, gap 16 avatar–text */}
          <div className="flex w-full items-center gap-4 py-5">
            <div className="flex h-[54px] w-[54px] shrink-0 items-center justify-center rounded-full bg-[#4A90E5] text-white">
              <User size={28} strokeWidth={1.75} aria-hidden />
            </div>
            <div className="flex min-w-0 flex-col justify-center">
              <span className="truncate text-[20px] font-bold leading-[18px] tracking-normal text-[#181725]">{displayName}</span>
              <span className="mt-0.5 truncate text-[16px] font-normal leading-[18px] tracking-normal text-[#7C7C7C]">{displayEmail}</span>
            </div>
          </div>

          {/*
            List: fill 370, space-between rows; generous vertical padding (~14px) for tap targets.
            Label: 15px / 600 / 3% / #3D495E. Icons: outline style, dark grey #3D495E, stroke 1.5.
          */}
          <div className="flex w-full flex-col">
            {menuItems.map((item) => (
              <button
                key={item.label}
                type="button"
                onClick={item.action}
                className="box-border flex min-h-[52px] w-full items-center justify-between border-b border-[#E2E2E2] py-3.5 text-left transition-colors hover:bg-white/60 active:bg-white/80"
              >
                <div className="flex min-w-0 items-center gap-3.5">
                  <item.icon
                    className="h-[20px] w-[20px] shrink-0 text-[#3D495E]"
                    strokeWidth={1.5}
                    aria-hidden
                  />
                  <span className="truncate text-[15px] font-[700] capitalize leading-none tracking-[0.03em] text-[#3D495E]">
                    {item.label}
                  </span>
                </div>
                <div className="flex shrink-0 items-center gap-2.5 pl-2">
                  <ChevronRight className="h-[18px] w-[18px] shrink-0 text-[#B0B0B0]" strokeWidth={2} aria-hidden />
                </div>
              </button>
            ))}

            <button
              type="button"
              onClick={handleLogout}
              className="box-border flex min-h-[52px] w-full items-center justify-between border-b border-[#E2E2E2] py-3.5 text-left transition-colors hover:bg-white/60 active:bg-white/80"
            >
              <div className="flex min-w-0 items-center gap-3.5">
                <Building2 className="h-[20px] w-[20px] shrink-0 text-[#3D495E]" strokeWidth={1.5} aria-hidden />
                <span className="truncate text-[15px] font-[700] capitalize leading-none tracking-[0.03em] text-[#3D495E]">
                  Logout
                </span>
              </div>
              <ChevronRight className="h-[18px] w-[18px] shrink-0 text-[#B0B0B0]" strokeWidth={2} aria-hidden />
            </button>
          </div>
        </div>
      </main>

      <nav
        className="fixed bottom-0 left-1/2 z-50 box-border flex h-[64px] w-full max-w-[402px] -translate-x-1/2 flex-col rounded-t-[10px] bg-[#F6F4FA] px-[43px] pb-4 pt-2 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)]"
        aria-label="Main navigation"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <div className="flex min-h-0 w-full flex-1 items-center justify-center">
          <div className="flex h-[40px] w-[316px] max-w-full items-center justify-center gap-2">
            <button type="button" onClick={() => onNavigate("dashboard")} className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
              <FontAwesomeIcon icon={faChartSimple} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Dashboard</span>
            </button>
            <button type="button" onClick={() => onNavigate("shop", false)} className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
              <FontAwesomeIcon icon={faShop} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Shop</span>
            </button>
            <button type="button" onClick={() => onNavigate("shop", true)} className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
              <FontAwesomeIcon icon={faHeart} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Favourites</span>
            </button>
            <button type="button" onClick={() => onNavigate("wallet")} className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
              <FontAwesomeIcon icon={faWallet} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Wallet</span>
            </button>
            <button type="button" onClick={() => onNavigate("account")} className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 text-[#4A90E5]" aria-current="page">
              {/* Figma: user silhouette inside thick blue ring */}
              <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-[2.5px] border-[#4A90E5] bg-[#F6F4FA]">
                <FontAwesomeIcon icon={faUser} className="h-[18px] w-[18px] text-[18px] leading-none text-[#4A90E5]" aria-hidden />
              </span>
              <span className="text-center text-[11px] font-bold leading-none tracking-normal text-[#4A90E5]">Account</span>
            </button>
          </div>
        </div>
      </nav>
    </div>
  );
}
