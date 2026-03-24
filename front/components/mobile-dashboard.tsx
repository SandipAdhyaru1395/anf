
"use client";
import { Button } from "@/components/ui/button";
import { useCurrency } from "@/components/currency-provider";
import { Banner } from "@/components/banner";
import React, { useEffect, useMemo, useState } from "react";
import { Card } from "@/components/ui/card";
import { ChevronRight } from "lucide-react";
import Image from "next/image";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faChartSimple,
  faHeart,
  faShop,
  faStar,
  faWallet,
  faUser,
  faBell,
  faChevronRight,
  faCreditCard,
  faCoins,
  faGift,
} from "@fortawesome/free-solid-svg-icons";
import { useCustomer } from "@/components/customer-provider";
import { startLoading, stopLoading } from "@/lib/loading";
import { useSettings } from "@/components/settings-provider";
import { resolveBackendAssetUrl } from "@/lib/utils";
import { Thumbnail } from "@/components/thumbnail";

/** Primary actions — matches order-details / brand gradient */
const PRIMARY_BUTTON_GRADIENT: React.CSSProperties = {
  background: "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)",
};

interface MobileDashboardProps {
  onNavigate: (page: any, favorites?: boolean) => void;
  onOpenOrder?: (orderNumber: string) => void;
  cart: Record<number, { product: any; quantity: number }>;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
}

export function MobileDashboard({
  onNavigate,
  onOpenOrder,
  cart,
  totals,
}: MobileDashboardProps) {
  const { symbol } = useCurrency();
  const { customer } = useCustomer();
  const { settings } = useSettings();

  const walletBalance = Number(customer?.wallet_balance || 0);
  const cartWalletCredit = useMemo(() => {
    return Object.values(cart).reduce((sum, item) => {
      const credit = Number(item?.product?.wallet_credit ?? 0);
      const qty = Number(item?.quantity ?? 0);
      return sum + (Number.isFinite(credit) ? credit : 0) * (Number.isFinite(qty) ? qty : 0);
    }, 0);
  }, [cart]);
  const logoSrc =
    resolveBackendAssetUrl(settings?.company_logo_url) ??
    settings?.company_logo_url ??
    null;
  const [orders, setOrders] = useState<any[]>([]);

  useEffect(() => {
    let isMounted = true;
    const fetchOrders = async () => {
      try {
        if (typeof window !== "undefined") {
          startLoading();
        }
        const res = await api.get("/orders");
        const json = res.data;
        if (!isMounted) return;
        if (json?.success && Array.isArray(json.orders)) {
          setOrders(json.orders);
          try {
            sessionStorage.setItem("orders_cache", JSON.stringify(json.orders));
          } catch { }
        }
      } catch (e) {
      } finally {
        if (typeof window !== "undefined") {
          stopLoading();
        }
        try {
          sessionStorage.removeItem("orders_needs_refresh");
        } catch { }
      }
    };

    let refreshed = false;
    try {
      const needs = sessionStorage.getItem("orders_needs_refresh");
      if (needs === "1") {
        refreshed = true;
        fetchOrders();
      }
    } catch { }

    if (!refreshed) {
      try {
        const raw = sessionStorage.getItem("orders_cache");
        if (raw) {
          const parsed = JSON.parse(raw);
          if (Array.isArray(parsed)) {
            setOrders(parsed);
          } else if (Array.isArray(parsed?.orders)) {
            setOrders(parsed.orders);
          } else {
            fetchOrders();
          }
        } else {
          fetchOrders();
        }
      } catch {
        fetchOrders();
      }
    }

    const onRefresh = () => fetchOrders();
    const onOrdersCacheUpdated = () => {
      try {
        const raw = sessionStorage.getItem("orders_cache");
        if (!raw) return;
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) {
          setOrders(parsed);
        } else if (Array.isArray(parsed?.orders)) {
          setOrders(parsed.orders);
        }
      } catch { }
    };

    if (typeof window !== "undefined") {
      window.addEventListener("orders-refresh", onRefresh);
      window.addEventListener("orders_cache_updated", onOrdersCacheUpdated);
    }

    return () => {
      isMounted = false;
      if (typeof window !== "undefined") {
        window.removeEventListener("orders-refresh", onRefresh);
        window.removeEventListener(
          "orders_cache_updated",
          onOrdersCacheUpdated,
        );
      }
    };
  }, []);

  return (
    <div className="relative mx-auto flex h-[100dvh] min-h-0 w-full max-w-[402px] flex-col bg-white shadow-sm">
      {/* HEADER — does not scroll */}
      <header className="z-50 flex h-[70px] w-full shrink-0 items-center justify-center bg-white border-b border-[#E2E2E2] px-4">
        <Thumbnail
          height={22.00458335876465}
          containerClassName="max-w-[168.8212432861328px]"
        />
      </header>

      {/* BANNER — fixed under header; matches design spacing */}
      <div className="shrink-0 bg-white px-3 pb-2 pt-3">
        <Banner className="h-[94px] w-full max-w-[380px]" />
      </div>

      {/* SCROLLABLE: everything below banner */}
      <main className="min-h-0 w-full flex-1 overflow-x-hidden overflow-y-auto pb-[150px] bg-[#F5F6FA] pt-1">
        <div className="px-3 flex flex-col gap-2.5 pt-1">
          {/* REFERRAL */}
        <div className="w-full bg-[#4A90E5] text-white rounded-[6px] px-3.5 py-3 pr-[80px] relative overflow-hidden shadow-[0_2px_4px_0_#4A90E530]">
          <h2 className="font-bold text-[14px]">Referral Rewards</h2>
          <p className="text-[12px] opacity-90 mt-0.5 relative z-10 leading-tight">
            Refer a friend to earn Rewards
          </p>
          {/* Custom SVG Illustration Mock */}
          <div className="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 opacity-90 scale-90">
             <div className="relative text-[#1E293B]">
               <FontAwesomeIcon icon={faCreditCard} className="text-[28px] -rotate-[15deg] transform shadow-sm" />
             </div>
             <div className="absolute left-[-10px] bottom-0 text-[#F59E0B] z-10">
               <FontAwesomeIcon icon={faCoins} className="text-[18px]" />
             </div>
             <div className="text-[#10B981] z-0 -ml-1 mt-3">
               <FontAwesomeIcon icon={faGift} className="text-[20px]" />
             </div>
          </div>
        </div>

          {/* WALLET */}
          <button
            onClick={() => onNavigate("wallet")}
            className="w-full h-[38px] flex items-center justify-between px-3 border border-[#4A90E5] rounded-[6px] bg-white"
          >
            <div className="flex items-center gap-2">
              <FontAwesomeIcon icon={faWallet} className="text-[#4A90E5] text-[15px]" />
              <span className="text-[12.5px] font-bold text-[#4E5667]">
                {symbol}{walletBalance.toFixed(2)} credit in your wallet
              </span>
            </div>
            <FontAwesomeIcon icon={faChevronRight} className="text-[#4A90E5] text-[12px] opacity-80" />
          </button>

          {/* BUTTONS */}
          <div className="w-full flex gap-2">
            <button
              type="button"
              onClick={() => onNavigate("shop")}
              className="flex h-[36px] flex-1 cursor-pointer items-center justify-center gap-2 rounded-[6px] text-[13px] font-[700] leading-none text-white shadow-sm transition-opacity hover:opacity-95"
              style={PRIMARY_BUTTON_GRADIENT}
            >
              <FontAwesomeIcon icon={faShop} className="text-[14px]" />
              Shop
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", true)}
              className="flex h-[36px] flex-1 cursor-pointer items-center justify-center gap-2 rounded-[6px] text-[13px] font-[700] leading-none text-white shadow-sm transition-opacity hover:opacity-95"
              style={PRIMARY_BUTTON_GRADIENT}
            >
              <FontAwesomeIcon icon={faHeart} className="text-[14px] " />
              Favourites
            </button>
          </div>
        </div>

        {/* NOTIFICATIONS */}
        <div className="px-3 mt-6">
          <h3 className="text-[15.5px] font-bold text-[#4E5667] mb-2.5 ml-0.5">
            Recent Notifications
          </h3>
          <div className="flex flex-col gap-2">
            {[1, 2, 3].map((n) => (
              <button
                key={n}
                className="w-full h-[38px] flex items-center justify-between px-3 border border-[#A7C8F2] rounded-[6px] bg-white shadow-sm cursor-pointer"
              >
                <div className="flex items-center gap-2.5">
                  <FontAwesomeIcon icon={faBell} className="text-[#4A90E5] text-[14px]" />
                  <span className="text-[12.5px] font-bold text-[#4E5667]">
                    Notification
                  </span>
                </div>
                <FontAwesomeIcon icon={faChevronRight} className="text-[#4A90E5] text-[12px] opacity-80" />
              </button>
            ))}
          </div>
        </div>

        {/* BRANDS */}
        <div className="px-3 mt-6">
          <h3 className="text-[15.5px] font-bold text-[#4E5667] mb-2.5 ml-0.5">
            Leading Brands
          </h3>
          <div className="brand-scroll-wrapper w-full bg-[#4A90E5]/5 rounded-[8px] py-3 px-1 overflow-hidden">
            <div className="brand-scroll-inner flex items-center min-w-max px-2 gap-[14px]">
              {["Lost Mary", "Elfbar", "Ske", "IVG", "Oxva"].map((b, i) => {
                const textColors = ["text-[#6D3996]", "text-[#EC9BBB]", "text-[#3D495E]", "text-[#E61D24]", "text-[#EA2428]"];
                return (
                  <div key={i} className="flex flex-col items-center justify-center w-[56px] gap-1">
                    <div className="w-[56px] h-[56px] bg-white rounded-full shadow-[0_2px_8px_0_rgba(0,0,0,0.06)] flex items-center justify-center border border-white flex-shrink-0">
                      <span className={`text-[10px] font-black uppercase text-center leading-[1.0] px-[2px] ${textColors[i % textColors.length]}`} style={{ wordBreak: 'break-word', letterSpacing: '-0.02em' }}>
                        {b.split(" ").map(w => <span key={w} className="block">{w}</span>)}
                      </span>
                    </div>
                    <span className="text-[11.5px] font-bold text-[#8A94A6] text-center w-full truncate leading-tight">
                      {b}
                    </span>
                  </div>
                )
              })}
            </div>
          </div>
        </div>

        {/* ORDERS */}
        {orders.length > 0 && (
          <div className="px-3 mt-6">
            <h3 className="text-[16px] font-bold text-[#3D495E] mb-3 ml-0.5 tracking-tight">
              Recent Orders
            </h3>
            <div className="flex flex-col gap-2.5">
              {orders.map((o, idx) => (
                <div key={o.order_number + idx} onClick={() => onOpenOrder && onOpenOrder(o.order_number)} className="bg-white border border-[#E2E2E2] rounded-[6px] px-3 py-2.5 cursor-pointer hover:bg-gray-50 flex items-stretch shadow-sm">
                  <div className="flex-1 space-y-[5px] text-[13px] text-[#3D495E] pr-3 border-r border-[#E2E2E2]">
                    <div className="flex justify-between">
                      <span className="text-[#64748B]">Order No:</span>
                      <span>{o.order_number}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-[#64748B]">Ordered:</span>
                      <span>{o.ordered_at || "N/A"}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-[#64748B]">Payment Status:</span>
                      <span className="uppercase">{o.payment_status || "PENDING"}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-[#64748B]">Fulfillment Status:</span>
                      <span className="uppercase">{o.fulfillment_status || "PROCESSING"}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-[#64748B]">Units:</span>
                      <span>{o.units || "0"}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-[#64748B]">SKUs:</span>
                      <span>{o.skus || "0"}</span>
                    </div>
                    <div className="flex justify-between font-bold pt-1">
                      <span>Total Paid:</span>
                      <span>{o.currency_symbol}{(Number(o.total_paid) || 0).toFixed(2)}</span>
                    </div>
                  </div>
                  <div className="pl-3 flex items-center justify-center">
                    <FontAwesomeIcon icon={faChevronRight} className="text-[#4A90E5] text-[14px]" />
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </main>

      {/* BASKET BAR */}
      <div className="fixed bottom-[74px] left-1/2 w-full max-w-[402px] h-[60px] -translate-x-1/2 bg-[#F3F4F9] border-t border-[#DCE1EE] pt-[12px] pr-[8px] pb-[12px] pl-[8px] z-40">
        <div className="flex h-full items-center justify-between">
          <div className="flex flex-col">
            <div className="flex items-center gap-1.5 text-[13px] text-[#3E4A62] font-bold whitespace-nowrap tracking-tight">
              <span>{totals.units} Units</span>
              <span className="text-[#D0D7E6] font-normal px-[2px]">|</span>
              <span>{totals.skus} SKUs</span>
              <span className="text-[#D0D7E6] font-normal px-[2px]">|</span>
              <span>{symbol}{totals.total.toFixed(2)}</span>
              <span className="text-[#D0D7E6] font-normal px-[2px]">|</span>
              <span className="inline-flex items-center gap-[3px] text-[#4A90E5] font-bold">
                <FontAwesomeIcon icon={faWallet} className="text-[12px] opacity-90" />
                <span>+{symbol}{cartWalletCredit.toFixed(2)}</span>
              </span>
            </div>
            <div className="text-[12px] text-[#727C90] mt-[2px] font-medium text-center">
              Includes FREE delivery
            </div>
          </div>

          <button
            type="button"
            onClick={() => onNavigate("basket")}
            className="w-[116px] h-[35px] max-w-[300px] rounded-[5px] p-[8px] text-[15px] font-normal leading-none text-white shadow-sm transition-opacity hover:opacity-95 flex items-center justify-center"
            style={PRIMARY_BUTTON_GRADIENT}
          >
            View Basket
          </button>
        </div>
      </div>

      {/* BOTTOM NAV */}
      <nav className="fixed bottom-0 left-1/2 w-full max-w-[402px] -translate-x-1/2 h-[74px] px-2 pt-[8px] pb-[10px] grid grid-cols-5 items-center bg-[#F1F2F7] border-t border-[#E4E7F0] z-50">
        <button
          onClick={() => onNavigate("dashboard")}
          className="flex flex-col items-center gap-[4px] text-[#4A90E5] text-[11px] font-bold leading-none"
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
          className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none"
        >
          <FontAwesomeIcon icon={faUser} className="text-[23px]" />
          <span>Account</span>
        </button>
      </nav>
    </div>
  );
}
