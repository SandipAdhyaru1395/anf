"use client";

import api from "@/lib/axios";
import { Banner } from "@/components/banner";
import { MobilePageHeader } from "@/components/mobile-page-header";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faBox,
  faCalendarDays,
  faChartSimple,
  faChevronRight,
  faCreditCard,
  faHeart,
  faShop,
  faUser,
  faWallet,
} from "@fortawesome/free-solid-svg-icons";
import { useEffect, useState } from "react";

interface MobileOrdersProps {
  onNavigate: (page: any, favorites?: boolean) => void;
  onBack: () => void;
  onOpenOrder?: (orderNumber: string) => void;
}

type OrderItem = {
  order_number: string;
  ordered_at: string;
  payment_status: string;
  fulfillment_status: string;
  units: number;
  skus: number;
  total_paid: number;
  currency_symbol: string;
};

/** Figma: title-case API status e.g. SHIPPED → Shipped */
function formatStatusLabel(status: string): string {
  return String(status || "")
    .replace(/_/g, " ")
    .toLowerCase()
    .replace(/\b\w/g, (c) => c.toUpperCase());
}

/** Figma list date e.g. 01/02/2026 */
function formatOrderListDate(apiValue: string): string {
  const s = String(apiValue || "").trim();
  const d = new Date(s);
  if (!Number.isNaN(d.getTime())) {
    return d.toLocaleDateString("en-GB", { day: "2-digit", month: "2-digit", year: "numeric" });
  }
  const m = s.match(/(\d{1,2}[/-]\d{1,2}[/-]\d{2,4})/);
  if (m) return m[1].replace(/-/g, "/");
  return s;
}

export function MobileOrders({ onNavigate, onBack, onOpenOrder }: MobileOrdersProps) {
  const [orders, setOrders] = useState<OrderItem[]>([]);
  const [loading, setLoading] = useState<boolean>(true);

  useEffect(() => {
    const fetchAll = async () => {
      setLoading(true);
      try {
        const res = await api.get("/orders", {
          params: { limit: 500, _ts: Date.now() },
        });
        const json = res?.data;
        if (json?.success && Array.isArray(json.orders)) {
          setOrders(json.orders);
          try {
            sessionStorage.setItem("orders_cache", JSON.stringify(json.orders));
          } catch {
            /* ignore */
          }
        } else {
          setOrders([]);
        }
      } catch {
        setOrders([]);
      } finally {
        setLoading(false);
      }
    };

    try {
      const raw = sessionStorage.getItem("orders_cache");
      if (raw) {
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) {
          setOrders(parsed);
        } else if (Array.isArray(parsed?.orders)) {
          setOrders(parsed.orders);
        }
      }
    } catch {
      /* ignore */
    }
    fetchAll();

    const onOrdersRefresh = () => {
      fetchAll();
    };

    const onVisibility = () => {
      if (typeof document !== "undefined" && document.visibilityState === "visible") {
        fetchAll();
      }
    };

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
      } catch {
        /* ignore */
      }
    };

    if (typeof window !== "undefined") {
      window.addEventListener("orders-refresh", onOrdersRefresh);
      window.addEventListener("orders_cache_updated", onOrdersCacheUpdated);
      document.addEventListener("visibilitychange", onVisibility);
    }

    return () => {
      if (typeof window !== "undefined") {
        window.removeEventListener("orders-refresh", onOrdersRefresh);
        window.removeEventListener("orders_cache_updated", onOrdersCacheUpdated);
        document.removeEventListener("visibilitychange", onVisibility);
      }
    };
  }, []);

  return (
    <div
      className="relative mx-auto flex h-[100dvh] min-h-0 w-full max-w-[402px] flex-col bg-[#FAFBFD]"
      style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
    >
      <MobilePageHeader title="My Orders" onBack={onBack} />

      <main className="scrollbar-hide min-h-0 flex-1 overflow-x-hidden overflow-y-auto bg-[#FAFBFD] px-6 pb-[80px]">
        <div className="mx-auto flex w-full max-w-[354px] flex-col gap-4">
          <Banner className="h-[89px] max-w-[354px] rounded-[10px] border border-[#E2E2E2]" />

          {loading ? (
            <div className="overflow-hidden rounded-[10px] border border-[#E2E2E2] bg-white">
              {[0, 1, 2].map((i) => (
                <div
                  key={i}
                  className="h-[72px] animate-pulse border-b border-[#E2E2E2] bg-white last:border-b-0"
                  aria-hidden
                />
              ))}
            </div>
          ) : orders.length === 0 ? (
            <div className="rounded-[10px] border border-[#E2E2E2] bg-white px-4 py-10 text-center shadow-sm">
              <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#EAF2FC]">
                <FontAwesomeIcon icon={faBox} className="text-[22px] text-[#4A90E5]" aria-hidden />
              </div>
              <p className="text-[16px] font-bold text-[#3D495E]">No orders yet</p>
              <p className="mt-1 text-[13px] font-medium text-[#8F98AD]">Your orders will appear here</p>
            </div>
          ) : (
            <ul className="flex list-none flex-col overflow-hidden rounded-[10px] border border-[#E2E2E2] bg-white p-0 shadow-sm">
              {orders.map((o, idx) => (
                <li key={`${o.order_number}-${idx}`} className="border-b border-[#E2E2E2] last:border-b-0">
                  <button
                    type="button"
                    onClick={() => onOpenOrder?.(o.order_number)}
                    className="flex w-full cursor-pointer items-stretch gap-4 bg-white px-4 py-2 text-left transition-colors hover:bg-[#FAFBFD] active:bg-[#F4F6FA]"
                  >
                    {/* Figma Order History Item: py-2 px-4, gap 16, min-h ~72 */}
                    <div className="flex min-w-0 flex-1 items-center gap-4">
                      <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-[5px] bg-[#EAF2FC]">
                        <FontAwesomeIcon icon={faBox} className="text-[18px] text-[#4A90E5]" aria-hidden />
                      </div>
                      <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                        <div className="flex flex-wrap items-center gap-2">
                          <span className="inline-flex rounded-full bg-[#E8F1FC] px-2 py-0.5 text-[10px] font-bold leading-none tracking-wide text-[#4A90E5]">
                            {formatStatusLabel(o.fulfillment_status)}
                          </span>
                          <span className="inline-flex items-center gap-0.5 rounded-full bg-[#E8F7EE] px-2 py-0.5 text-[10px] font-bold leading-none text-[#1FA463]">
                            <FontAwesomeIcon icon={faCreditCard} className="text-[9px]" aria-hidden />
                            {formatStatusLabel(o.payment_status)}
                          </span>
                        </div>
                        <div className="flex flex-wrap items-baseline gap-x-2 text-[13px] font-bold leading-tight tracking-[0.04em] text-[#3D495E]">
                          <span>Order: {o.order_number}</span>
                          <span className="font-normal text-[#C5CEDE]">•</span>
                          <span>
                            Total: {o.currency_symbol}
                            {o.total_paid.toFixed(2)}
                          </span>
                        </div>
                        <div className="flex flex-wrap items-center gap-x-1.5 text-[12px] font-medium leading-snug text-[#8F98AD]">
                          <span>
                            {o.skus} SKUs
                          </span>
                          <span className="text-[#C5CEDE]">•</span>
                          <span>
                            {o.units} Items
                          </span>
                          <span className="text-[#C5CEDE]">•</span>
                          <span className="inline-flex items-center gap-1">
                            <FontAwesomeIcon icon={faCalendarDays} className="text-[11px] opacity-90" aria-hidden />
                            {formatOrderListDate(o.ordered_at)}
                          </span>
                        </div>
                      </div>
                    </div>
                    <div className="flex shrink-0 items-center self-stretch border-l border-[#E2E2E2] pl-3">
                      <FontAwesomeIcon
                        icon={faChevronRight}
                        className="text-[18px] text-[#4A90E5]"
                        aria-hidden
                      />
                    </div>
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>
      </main>

      <nav
        className="fixed bottom-0 left-1/2 z-50 box-border flex h-[64px] w-full max-w-[402px] -translate-x-1/2 flex-col rounded-t-[10px] bg-white px-[43px] pb-4 pt-2 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)]"
        aria-label="Main navigation"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <div className="flex min-h-0 w-full flex-1 items-center justify-center">
          <div className="flex h-[40px] w-[316px] max-w-full items-center justify-center gap-2">
            <button
              type="button"
              onClick={() => onNavigate("dashboard")}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon
                icon={faChartSimple}
                className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]"
                aria-hidden
              />
              <span className="text-center text-[10px] font-bold leading-none">Dashboard</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", false)}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon
                icon={faShop}
                className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]"
                aria-hidden
              />
              <span className="text-center text-[10px] font-bold leading-none">Shop</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", true)}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon
                icon={faHeart}
                className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]"
                aria-hidden
              />
              <span className="text-center text-[10px] font-bold leading-none">Favourites</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("wallet")}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon
                icon={faWallet}
                className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]"
                aria-hidden
              />
              <span className="text-center text-[10px] font-bold leading-none">Wallet</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("account")}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-0.5 text-[#4A90E5]"
              aria-current="page"
            >
              <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#4A90E5]">
                <FontAwesomeIcon
                  icon={faUser}
                  className="h-[18px] w-[18px] text-[18px] leading-none text-white"
                  aria-hidden
                />
              </span>
              <span className="text-center text-[11px] font-bold leading-none text-[#4A90E5]">Account</span>
              <span className="h-0.5 w-6 shrink-0 rounded-full bg-[#4A90E5]" aria-hidden />
            </button>
          </div>
        </div>
      </nav>
    </div>
  );
}
