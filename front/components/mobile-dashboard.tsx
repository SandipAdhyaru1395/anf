
"use client";
import { useCurrency } from "@/components/currency-provider";
import { Banner } from "@/components/banner";
import React, { useEffect, useMemo, useState } from "react";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faChartSimple,
  faHeart,
  faShop,
  faWallet,
  faUser,
  faBell,
  faChevronRight,
  faGift,
  faBoxOpen,
  faCubesStacked,
  faCalendarDays,
  faClock,
  faTruckFast,
  faCircleCheck,
  faCreditCard,
} from "@fortawesome/free-solid-svg-icons";
import { useCustomer } from "@/components/customer-provider";
import { startLoading, stopLoading } from "@/lib/loading";
import { Thumbnail } from "@/components/thumbnail";

/** Primary actions — matches order-details / brand gradient */
const PRIMARY_BUTTON_GRADIENT: React.CSSProperties = {
  background: "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)",
};

function normalizeStatus(raw: any) {
  return String(raw || "")
    .trim()
    .replace(/[_-]+/g, " ")
    .replace(/\s+/g, " ")
    .toUpperCase();
}

function formatOrderDate(raw: any) {
  if (!raw) return "N/A";
  if (typeof raw === "string") {
    const trimmed = raw.trim();
    // If backend already sends a readable date, keep it.
    if (/^\d{2}\/\d{2}\/\d{4}$/.test(trimmed)) return trimmed;
    const d = new Date(trimmed);
    if (!Number.isNaN(d.getTime())) {
      const dd = String(d.getDate()).padStart(2, "0");
      const mm = String(d.getMonth() + 1).padStart(2, "0");
      const yyyy = d.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    }
    return trimmed;
  }
  const d = new Date(raw);
  if (!Number.isNaN(d.getTime())) {
    const dd = String(d.getDate()).padStart(2, "0");
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const yyyy = d.getFullYear();
    return `${dd}/${mm}/${yyyy}`;
  }
  return "N/A";
}

function pillClassForFulfillment(statusRaw: any) {
  const s = normalizeStatus(statusRaw);
  if (s.includes("DELIVER")) return "bg-[#E6F7EF] text-[#1F8A55]";
  if (s.includes("SHIP")) return "bg-[#E8F3FF] text-[#2868C0]";
  if (s.includes("CANCEL") || s.includes("FAIL"))
    return "bg-[#FDECEC] text-[#C81E1E]";
  // default "PENDING/PROCESSING"
  return "bg-[#FFF3E0] text-[#A16207]";
}

function pillClassForPayment(statusRaw: any) {
  const s = normalizeStatus(statusRaw);
  if (s.includes("PAID") || s.includes("CAPTURE") || s.includes("SUCCESS"))
    return "bg-[#E6F7EF] text-[#1F8A55]";
  if (s.includes("REFUND"))
    return "bg-[#EEF1F4] text-[#3D495E]";
  if (s.includes("FAIL") || s.includes("DECLIN") || s.includes("CANCEL"))
    return "bg-[#FDECEC] text-[#C81E1E]";
  return "bg-[#FFF3E0] text-[#A16207]";
}

function pillMetaForFulfillment(statusRaw: any) {
  const s = normalizeStatus(statusRaw);
  if (s.includes("DELIVER"))
    return {
      label: "Delivered",
      pillBg: "#EAF7EF",
      text: "#1F8A55",
      icon: faCircleCheck,
    };
  if (s.includes("SHIP"))
    return {
      label: "Shipped",
      pillBg: "#EAF3FF",
      text: "#2F80ED",
      icon: faTruckFast,
    };
  if (s.includes("CANCEL") || s.includes("FAIL"))
    return {
      label: "Cancelled",
      pillBg: "#FDECEC",
      text: "#C81E1E",
      icon: faCircleCheck,
    };
  return {
    label: "Pending",
    pillBg: "#FFF3E6",
    text: "#F2994A",
    icon: undefined,
  };
}

function pillMetaForPayment(statusRaw: any) {
  const s = normalizeStatus(statusRaw);
  if (s.includes("PAID") || s.includes("CAPTURE") || s.includes("SUCCESS"))
    return {
      label: "Paid",
      pillBg: "#EAF7EF",
      text: "#27AE60",
      icon: faCreditCard,
    };
  if (s.includes("REFUND"))
    return {
      label: "Refunded",
      pillBg: "#EEF1F4",
      text: "#3D495E",
      icon: faCreditCard,
    };
  if (s.includes("FAIL") || s.includes("DECLIN") || s.includes("CANCEL"))
    return {
      label: "Failed",
      pillBg: "#FDECEC",
      text: "#C81E1E",
      icon: faCreditCard,
    };
  return {
    label: "Pending",
    pillBg: "#FFF3E6",
    text: "#F2994A",
    icon: undefined,
  };
}

function StatusPill({
  label,
  color,
  bg,
  icon,
}: {
  label: string;
  color: string;
  bg: string;
  icon: any;
}) {
  const useFigmaPillSpec = true;
  return (
    <span
      className="inline-flex shrink-0 items-center text-[10px] font-semibold leading-none"
      style={{
        backgroundColor: bg,
        color,
        ...(useFigmaPillSpec
          ? {
              minWidth: "52.968px",
              height: "14.742px",
              paddingTop: "1.87px",
              paddingRight: "7.48px",
              paddingBottom: "1.87px",
              paddingLeft: "7.48px",
              gap: "9.36px",
              borderRadius: "23.39px",
              border: `0.23px solid ${color}`,
            }
          : {
              height: "18px",
              paddingLeft: "8px",
              paddingRight: "8px",
              gap: "6px",
              borderRadius: "9999px",
            }),
      }}
    >
      {icon ? (
        <span
          className="inline-flex items-center justify-center rounded-[3px]"
          style={{ backgroundColor: color }}
          aria-hidden
        >
          <FontAwesomeIcon
            icon={icon}
            className={useFigmaPillSpec ? "text-[7px] text-white" : "text-[8px] text-white"}
          />
        </span>
      ) : null}
      {label}
    </span>
  );
}

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

  const walletBalance = Number(customer?.wallet_balance || 0);
  const cartWalletCredit = useMemo(() => {
    return Object.values(cart).reduce((sum, item) => {
      const credit = Number(item?.product?.wallet_credit ?? 0);
      const qty = Number(item?.quantity ?? 0);
      return sum + (Number.isFinite(credit) ? credit : 0) * (Number.isFinite(qty) ? qty : 0);
    }, 0);
  }, [cart]);
  const [orders, setOrders] = useState<any[]>([]);

  useEffect(() => {
    let isMounted = true;
    const fetchOrders = async () => {
      try {
        if (typeof window !== "undefined") {
          startLoading();
        }
        const res = await api.get("/orders", {
          params: { limit: 500, _ts: Date.now() },
        });
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
    <div className="relative mx-auto flex h-[100dvh] max-h-[874px] min-h-0 w-full max-w-[402px] flex-col bg-[#FAFBFD] shadow-sm">
      {/* HEADER — 16px horizontal → 370px content */}
      <header className="z-50 flex w-full shrink-0 flex-col bg-[#F8F7FC] px-4 pb-4 pt-[65px] shadow-[0_5px_15px_0_rgba(85,94,88,0.09)]">
        <div className="flex min-h-[22.51px] w-full items-center justify-center pt-1 pr-[15px]">
          <Thumbnail
            height={22.51}
            containerClassName="max-w-[198.53px] w-full !bg-transparent"
          />
        </div>
      </header>

      <main className="min-h-0 w-full flex-1 overflow-x-hidden overflow-y-auto bg-[#FAFBFD] pb-[150px]">
        {/* BANNER — scrolls with content */}
        <div className="shrink-0 bg-[#FAFBFD] px-4 pb-2 pt-2">
          <Banner className="mx-auto h-[93px] w-full max-w-[370px] !rounded-[2px]" />
        </div>
        <div className="mx-auto flex w-full max-w-[402px] flex-col gap-[10px] px-4 pb-4 pt-4">
    
          <div className="flex w-full max-w-[370px] flex-col gap-2.5 self-center rounded-sm bg-[#EEF1F4] p-2.5">
            
            <div
              className="relative w-full overflow-hidden rounded-sm py-2.5 pl-2.5 pr-[84px] text-white shadow-sm"
              style={PRIMARY_BUTTON_GRADIENT}
            >
              <h2
                className="text-[13px] font-black leading-tight tracking-[0.02em] text-white"
                style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
              >
                Referral Rewards
              </h2>
              <p
                className="mt-1 text-[11px] font-medium leading-snug tracking-[0.02em] text-white/85"
                style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
              >
                Refer a friend to earn Rewards
              </p>
              <div
                className="pointer-events-none absolute inset-y-0 right-1.5 flex w-[76px] items-end justify-end pb-1.5"
                aria-hidden
              >
                <div className="relative h-[58px] w-[72px]">
                  <div className="absolute bottom-0 left-0 flex items-end gap-px">
                    <span className="h-[13px] w-[13px] rounded-full bg-gradient-to-br from-[#FCD34D] via-[#F59E0B] to-[#D97706] shadow-[0_1px_2px_rgba(0,0,0,0.2)]" />
                    <span className="mb-px h-[15px] w-[15px] rounded-full bg-gradient-to-br from-[#FDE68A] via-[#F59E0B] to-[#B45309] shadow-[0_1px_3px_rgba(0,0,0,0.22)]" />
                    <span className="h-[12px] w-[12px] rounded-full bg-gradient-to-br from-[#FBBF24] to-[#D97706] shadow-[0_1px_2px_rgba(0,0,0,0.2)]" />
                  </div>
                  <div className="absolute right-0 top-0 z-[1] w-[58px] rotate-[-10deg] rounded-sm bg-[#1E293B] px-1 py-1.5 shadow-md ring-1 ring-white/10">
                    <div className="h-0.5 w-full rounded-sm bg-slate-500/80" />
                    <p className="mt-1 text-center text-[5.5px] font-bold leading-none tracking-[0.1em] text-white">
                      EARN CREDIT
                    </p>
                  </div>
                  <div className="absolute bottom-0 right-0 z-[2] translate-x-0.5">
                    <FontAwesomeIcon
                      icon={faGift}
                      className="text-[20px] text-[#64748B] drop-shadow-sm"
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* WALLET — Figma: fill max 354px, fixed 34px row, px 16, 1px #4A90E5, radius 5px */}
            <button
              type="button"
              onClick={() => onNavigate("wallet")}
              className="box-border flex h-[34px] w-full max-w-[354px] shrink-0 items-center justify-between rounded-[5px] border border-[#4A90E5] bg-white px-4 transition-colors hover:bg-[#FAFBFD]"
            >
              <div className="flex min-w-0 flex-1 items-center gap-2 pr-2">
                <FontAwesomeIcon
                  icon={faWallet}
                  className="shrink-0 text-[14px] text-[#4A90E5]"
                />
                <span className="truncate text-left text-[12px] font-bold leading-none text-[#3D495E]">
                  {symbol}
                  {walletBalance.toFixed(2)} credit in your wallet
                </span>
              </div>
              <FontAwesomeIcon
                icon={faChevronRight}
                className="shrink-0 text-[12px] text-[#4A90E5]"
              />
            </button>

           
            <div className="flex w-full gap-2">
              <button
                type="button"
                onClick={() => onNavigate("shop")}
                className="flex h-[38px] min-h-[38px] flex-1 cursor-pointer items-center justify-center gap-2 rounded-sm text-[15px] font-bold leading-none text-white shadow-[0_2px_4px_rgba(74,144,229,0.3)] transition-opacity hover:opacity-95 active:opacity-90"
                style={PRIMARY_BUTTON_GRADIENT}
              >
                <FontAwesomeIcon icon={faShop} className="text-[14px]" />
                Shop
              </button>
              <button
                type="button"
                onClick={() => onNavigate("shop", true)}
                className="flex h-[38px] min-h-[38px] flex-1 cursor-pointer items-center justify-center gap-2 rounded-sm text-[13px] font-bold leading-none text-white shadow-[0_2px_4px_rgba(74,144,229,0.3)] transition-opacity hover:opacity-95 active:opacity-90"
                style={PRIMARY_BUTTON_GRADIENT}
              >
                <FontAwesomeIcon icon={faHeart} className="text-[14px]" />
                Favourites
              </button>
            </div>
          </div>

          {/* NOTIFICATIONS */}
          <section className="w-full max-w-[370px] self-center">
            <h3 className="mb-2 text-[15px] font-bold leading-tight text-[#FAFBFD]">
              Recent Notifications
            </h3>
            <div className="flex flex-col gap-2">
              {[1, 2, 3].map((n) => (
                <button
                  key={n}
                  type="button"
                  className="box-border flex min-h-[34px] w-full cursor-pointer items-center justify-between rounded-[5px] border border-[#4A90E5] bg-white py-[10px] pl-4 pr-4 transition-colors hover:bg-[#FAFBFD]"
                >
                  <div className="flex min-w-0 flex-1 items-center gap-2 pr-2">
                    <FontAwesomeIcon
                      icon={faBell}
                      className="shrink-0 text-[14px] text-[#4A90E5]"
                    />
                    <span className="truncate text-left text-[12px] font-bold leading-none text-[#4E5667]">
                      Notification
                    </span>
                  </div>
                  <FontAwesomeIcon
                    icon={faChevronRight}
                    className="shrink-0 text-[12px] text-[#4A90E5]"
                  />
                </button>
              ))}
            </div>
          </section>

          {/* BRANDS */}
          <section className="w-full max-w-[370px] self-center">
            <h3 className="mb-2 text-[15px] font-bold leading-tight text-[#4E5667]">
              Leading Brands
            </h3>
            <div className="brand-scroll-wrapper w-full overflow-hidden rounded-sm bg-[#4A90E5]/5 px-1 py-3">
              <div className="brand-scroll-inner flex min-w-max items-center gap-[14px] px-2">
                {["Lost Mary", "Elfbar", "Ske", "IVG", "Oxva"].map((b, i) => {
                  const textColors = [
                    "text-[#6D3996]",
                    "text-[#EC9BBB]",
                    "text-[#3D495E]",
                    "text-[#E61D24]",
                    "text-[#EA2428]",
                  ];
                  return (
                    <div
                      key={i}
                      className="flex w-[56px] shrink-0 flex-col items-center justify-center gap-1"
                    >
                      <div className="flex h-[56px] w-[56px] shrink-0 items-center justify-center rounded-full border border-white bg-white shadow-[0_2px_8px_0_rgba(0,0,0,0.06)]">
                        <span
                          className={`px-[2px] text-center text-[10px] font-black uppercase leading-none ${textColors[i % textColors.length]}`}
                          style={{
                            wordBreak: "break-word",
                            letterSpacing: "-0.02em",
                          }}
                        >
                          {b.split(" ").map((w) => (
                            <span key={w} className="block">
                              {w}
                            </span>
                          ))}
                        </span>
                      </div>
                      <span className="w-full truncate text-center text-[11.5px] font-bold leading-tight text-[#8A94A6]">
                        {b}
                      </span>
                    </div>
                  );
                })}
              </div>
            </div>
          </section>

          {/* ORDERS */}
          {orders.length > 0 && (
            <section className="w-full max-w-[370px] self-center">
              <h3 className="mb-2 text-[15px] font-bold leading-tight text-[#4E5667]">
                Recent Orders
              </h3>
              <div className="flex flex-col gap-2.5">
                {orders.map((o, idx) => (
                  <button
                    key={o.order_number + idx}
                    type="button"
                    onClick={() => onOpenOrder?.(o.order_number)}
                    className="flex h-[71.5224px] w-full items-center gap-4 rounded-[10px] border border-[#4A90E5] bg-white px-4 py-2 transition-colors hover:bg-[#FAFBFD] active:bg-[#F7FAFF]"
                  >
                    {/* left icon */}
                    <div className="flex h-[44px] w-[44px] shrink-0 items-center justify-center rounded-full bg-[#EAF3FF]">
                        <svg
                          width="18"
                          height="18"
                          viewBox="0 0 18 18"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                          aria-hidden="true"
                        >
                          <rect
                            x="4"
                            y="3.5"
                            width="10"
                            height="11"
                            rx="2"
                            stroke="#2F80ED"
                            strokeWidth="1.3"
                          />
                          <path
                            d="M6 6.5H12"
                            stroke="#2F80ED"
                            strokeWidth="1.3"
                            strokeLinecap="round"
                          />
                          <path
                            d="M6 9H10.5"
                            stroke="#2F80ED"
                            strokeWidth="1.3"
                            strokeLinecap="round"
                          />
                        </svg>
                    </div>

                    {/* body */}
                    <div className="min-w-0 flex-1">
                      <div className="flex items-center justify-between gap-2">
                        <div className="flex min-w-0 items-center gap-2">
                          {(() => {
                            const f = pillMetaForFulfillment(o.fulfillment_status || o.status);
                            const p = pillMetaForPayment(
                              o.payment_status || o.payment || o.financial_status,
                            );
                            return (
                              <>
                                <StatusPill
                                  label={f.label}
                                  color={f.text}
                                  bg={f.pillBg}
                                  icon={f.icon}
                                />
                                <StatusPill
                                  label={p.label}
                                  color={p.text}
                                  bg={p.pillBg}
                                  icon={p.icon}
                                />
                              </>
                            );
                          })()}
                        </div>
                      </div>

                      <div
                        className="mt-2 flex items-center justify-between gap-2 text-[13px] font-medium leading-none text-[#3D495E]"
                        style={{ fontFamily: "Roboto, system-ui, sans-serif", letterSpacing: "0.04em" }}
                      >
                        <div className="flex min-w-0 items-center gap-2">
                          <span className="shrink-0">Order:</span>
                          <span className="min-w-0 truncate">{o.order_number}</span>
                        </div>
                        <span className="shrink-0 px-1 text-[#6B7A9C]" aria-hidden>
                          •
                        </span>
                        <div className="flex shrink-0 items-center gap-2">
                          <span>Total:</span>
                          <span>
                            {o.currency_symbol}
                            {(Number(o.total_paid ?? o.total ?? o.grand_total) || 0).toFixed(2)}
                          </span>
                        </div>
                      </div>

                      <div
                        className="mt-2 flex items-center gap-2 text-[11px] font-medium leading-none text-[#6B7A9C]"
                        style={{ fontFamily: "Roboto, system-ui, sans-serif", letterSpacing: "0.04em" }}
                      >
                        <span className="inline-flex items-center gap-1">
                          {Number(o.skus ?? 0) || 0} SKUs
                        </span>
                        <span className="text-[#6B7A9C]" aria-hidden>
                          •
                        </span>
                        <span className="inline-flex items-center gap-1">
                          {Number(o.units ?? o.items ?? 0) || 0} Items
                        </span>
                        <span className="text-[#6B7A9C]" aria-hidden>
                          •
                        </span>
                        <span className="inline-flex items-center gap-1">
                          <FontAwesomeIcon
                            icon={faCalendarDays}
                            className="text-[11px] text-[#6B7A9C]"
                            aria-hidden
                          />
                          {formatOrderDate(o.ordered_at || o.created_at || o.date)}
                        </span>
                      </div>
                    </div>

                    {/* right arrow zone (|>) */}
                    <div className="ml-auto flex shrink-0 items-center justify-center bg-white">
                      <span
                        className="mr-2 h-[26px] w-px shrink-0 bg-[#4A90E5]"
                        aria-hidden
                      />
                      <FontAwesomeIcon
                        icon={faChevronRight}
                        className="text-[18px] text-[#2F80ED]"
                        aria-hidden
                      />
                    </div>
                  </button>
                ))}
              </div>

              <button
                type="button"
                onClick={() => onNavigate("orders")}
                className="mt-3 w-full rounded-b-[10px] py-3 text-center text-[14px] font-bold text-[#2F80ED]"
                style={{
                  background:
                    "linear-gradient(180deg, #F5F8FF 0%, #E9F1FF 100%)",
                  boxShadow:
                    "0 6px 16px rgba(0,0,0,0.06), inset 0 1px 0 rgba(255,255,255,0.95)",
                }}
              >
                View All Orders
              </button>
            </section>
          )}
        </div>
      </main>

      {/* BASKET BAR — Mobile Sticky Cart: stats Roboto 700 13px/100% #3D495E; dividers 1px #D2D0E1; wallet row gap 5px */}
      <div
        className="fixed bottom-[64px] left-1/2 z-40 box-border flex w-full max-w-[402px] -translate-x-1/2 items-center bg-white px-[12px] py-2.5 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)]"
        aria-label="Mobile sticky cart"
      >
        <div className="flex min-h-0 w-full items-center gap-2">
          <div className="flex min-w-0 flex-col justify-center">
            <div
              className="flex min-w-0 items-center whitespace-nowrap"
              style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
            >
              <span className="inline-flex h-[15px] items-center text-[13px] font-bold leading-none tracking-normal text-[#3D495E]">
                {totals.units} Units
              </span>
              <span
                className="mx-1 h-[11px] w-px shrink-0 self-center bg-[#D2D0E1]"
                aria-hidden
              />
              <span className="inline-flex h-[15px] items-center text-[13px] font-bold leading-none tracking-normal text-[#3D495E]">
                {totals.skus} SKUs
              </span>
              <span
                className="mx-1 h-[11px] w-px shrink-0 self-center bg-[#D2D0E1]"
                aria-hidden
              />
              <span className="inline-flex h-[15px] shrink-0 items-center text-[13px] font-bold leading-none tracking-normal text-[#3D495E]">
                {symbol}
                {totals.total.toFixed(2)}
              </span>
              <span
                className="mx-1 h-[11px] w-px shrink-0 self-center bg-[#D2D0E1]"
                aria-hidden
              />
              <span className="inline-flex h-[15px] shrink-0 items-center gap-[5px] text-[13px] font-bold leading-none tracking-normal text-[#4A90E5]">
                <FontAwesomeIcon
                  icon={faWallet}
                  className="text-[12px] opacity-90"
                  aria-hidden
                />
                <span>+{symbol}{cartWalletCredit.toFixed(2)}</span>
              </span>
            </div>
            <p
              className="mt-1 w-full text-center text-[12px] font-normal leading-none tracking-[0.05em] text-[#68676E]"
              style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
            >
              Includes FREE delivery
            </p>
          </div>

          <button
            type="button"
            onClick={() => onNavigate("basket")}
            className="ml-auto cursor-pointer box-border flex h-[35px] w-[116px] max-w-[300px] shrink-0 items-center justify-center rounded-[8px] p-[8px] text-center text-[16px] font-[700px] leading-none tracking-normal text-[#FFFFFF] shadow-sm transition-opacity hover:opacity-95 active:opacity-90"
            style={{ fontFamily: "Roboto, system-ui, sans-serif", ...PRIMARY_BUTTON_GRADIENT }}
          >
            View Basket
          </button>
        </div>
      </div>

      {/* BOTTOM NAV — Figma: 316×40 row; icon↔label gap 4px; inactive tabs 60% opacity; active icon 23px, inactive 20px */}
      <nav
        className="fixed bottom-0 left-1/2 z-50 box-border flex h-[64px] w-full max-w-[402px] -translate-x-1/2 flex-col rounded-t-[10px] bg-[#F6F4FA] px-[43px] pb-4 pt-2 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)]"
        aria-label="Main navigation"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <div className="flex min-h-0 w-full flex-1 items-center justify-center">
          <div className="flex h-[40px] w-[316px] max-w-full items-center justify-between">
            <button
              type="button"
              onClick={() => onNavigate("dashboard")}
              className="flex h-full min-w-0 flex-col items-center justify-center gap-1 text-[#4A90E5]"
              aria-current="page"
            >
              <FontAwesomeIcon
                icon={faChartSimple}
                className="h-[23px] w-[23px] shrink-0 text-[23px] leading-none text-[#4A90E5]"
                aria-hidden
              />
              <span className="inline-flex h-[13px] min-w-[52px] items-center justify-center text-center text-[11px] font-medium leading-none tracking-normal text-[#4A90E5]">
                Dashboard
              </span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop")}
              className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon
                icon={faShop}
                className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]"
                aria-hidden
              />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">
                Shop
              </span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", true)}
              className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon
                icon={faHeart}
                className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]"
                aria-hidden
              />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">
                Favourites
              </span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("wallet")}
              className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon
                icon={faWallet}
                className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]"
                aria-hidden
              />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">
                Wallet
              </span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("account")}
              className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon
                icon={faUser}
                className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]"
                aria-hidden
              />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">
                Account
              </span>
            </button>
          </div>
        </div>
      </nav>
    </div>
  );
}
