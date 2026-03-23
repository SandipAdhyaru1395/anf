"use client";

import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faChartSimple,
  faShop,
  faWallet,
  faUser,
  faHeart,
  faChevronLeft,
} from "@fortawesome/free-solid-svg-icons";
import { useEffect, useState } from "react";
import { Banner } from "@/components/banner";
import { resolveBackendAssetUrl } from "@/lib/utils";

interface MobileOrderDetailsProps {
  orderNumber: string;
  onNavigate: (page: any, favorites?: boolean) => void;
  onBack: () => void;
  onReorder: (items: Array<{ product: any; quantity: number }>) => void;
}

type Address = {
  line1?: string | null;
  line2?: string | null;
  city?: string | null;
  state?: string | null;
  zip?: string | null;
  country?: string | null;
};

type OrderDetails = {
  order_number: string;
  ordered_at: string;
  payment_status: string;
  fulfillment_status: string;
  units: number;
  skus: number;
  subtotal: number;
  vat_amount: number;
  delivery_method: string;
  delivery_charge: number;
  wallet_discount: number;
  total_paid: number;
  payment_amount?: number;
  wallet_credit_used?: number;
  currency_symbol: string;
  address: Address;
  items: Array<{
    product_id: number;
    product_name?: string | null;
    product_image?: string | null;
    quantity: number;
    unit_price: number;
    wallet_credit_earned: number;
    total_price: number;
  }>;
};

function formatPlacedAt(apiValue: string): string {
  const s = String(apiValue || "").trim();
  const m = s.match(/^(\d{1,2}:\d{2})\s+(.+)$/);
  if (m) return `${m[2]} ${m[1]}`;
  return s;
}

/** Title-case API status e.g. DELIVERED → Delivered */
function formatStatusLabel(status: string): string {
  return String(status || "")
    .replace(/_/g, " ")
    .toLowerCase()
    .replace(/\b\w/g, (c) => c.toUpperCase());
}

/** Pill badge: fill + border + text (matches design specs) */
function statusBadgeClass(status: string): string {
  const u = (status || "").toUpperCase();
  if (u.includes("DELIVER")) {
    return "border border-[#ABEFC6] bg-[#ECFDF3] text-[#067647]";
  }
  if (u.includes("SHIP") || u.includes("DISPATCH")) {
    return "border border-[#B2DDFF] bg-[#EFF8FF] text-[#175CD3]";
  }
  if (u.includes("CANCEL")) {
    return "border border-[#FECDCA] bg-[#FEF3F2] text-[#B42318]";
  }
  if (u.includes("PENDING") || u.includes("PROCESS")) {
    return "border border-[#FEDF89] bg-[#FFFAEB] text-[#B54708]";
  }
  return "border border-[#E4E7EC] bg-[#F9FAFB] text-[#344054]";
}

export function MobileOrderDetails({
  orderNumber,
  onNavigate,
  onBack,
  onReorder,
}: MobileOrderDetailsProps) {
  const [order, setOrder] = useState<OrderDetails | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [reordering, setReordering] = useState<boolean>(false);

  useEffect(() => {
    const fetchDetails = async () => {
      setLoading(true);
      try {
        const res = await api.get(`/orders/${orderNumber}`);
        if (res?.data?.success && res.data.order) {
          setOrder(res.data.order);
        } else {
          setOrder(null);
        }
      } catch {
        setOrder(null);
      } finally {
        setLoading(false);
      }
    };
    fetchDetails();
  }, [orderNumber]);

  const displayOrderId = order?.order_number ?? orderNumber;

  return (
    <div className="mx-auto flex h-[100dvh] min-h-0 w-full max-w-[402px] flex-col bg-[#F8F7FC]">
      {/* Header — design: lavender bar, Back #667085, title #344054 / 700, pill badge + border */}
      <header className="relative z-50 flex h-[60px] w-full shrink-0 items-center border-b border-[#E4E7EC] bg-[#EEF2F9] px-4">
        <button
          type="button"
          onClick={onBack}
          className="absolute left-4 top-1/2 z-10 flex -translate-y-1/2 items-center gap-1.5 text-[#667085] transition-colors hover:text-[#475467] active:opacity-80"
        >
          <FontAwesomeIcon icon={faChevronLeft} className="text-[15px]" />
          <span className="text-[15px] font-normal leading-none">Back</span>
        </button>

        {/* True center: independent of Back / badge width */}
        <h1
          className="pointer-events-none absolute left-1/2 top-1/2 z-0 max-w-[min(200px,calc(100%-9.5rem))] -translate-x-1/2 -translate-y-1/2 truncate text-center text-[18px] font-[700] leading-tight tracking-tight text-[#344054]"
          title={`#${displayOrderId}`}
        >
          #{displayOrderId}
        </h1>

        <div className="absolute right-4 top-1/2 z-10 flex min-h-[28px] min-w-[72px] -translate-y-1/2 items-center justify-end">
          {loading ? (
            <span
              className="h-[26px] w-[76px] shrink-0 animate-pulse rounded-full bg-[#E4E7EC]/90"
              aria-hidden
            />
          ) : order ? (
            <span
              className={`max-w-[118px] truncate rounded-full px-2.5 py-1 text-center text-[11px] font-semibold leading-tight ${statusBadgeClass(order.fulfillment_status)}`}
            >
              {formatStatusLabel(order.fulfillment_status)}
            </span>
          ) : null}
        </div>
      </header>

      <div className="scrollbar-hide min-h-0 flex-1 overflow-x-hidden overflow-y-auto pb-[90px]">

        <section
          className="w-full px-4 pt-3 pb-2"
          aria-label="Promotional banner"
        >

          <div className="mx-auto h-[94px] w-full max-w-[380px] overflow-hidden rounded-[10px] bg-[#E8ECF3]/60">
            <Banner className="h-full w-full max-w-none !mx-0 rounded-none border-0 shadow-none" />
          </div>
        </section>

        {loading ? (
          <div className="space-y-4 px-4 pt-2">
            <div className="h-4 w-3/4 animate-pulse rounded bg-[#E4E7F0]" />
            <div className="h-32 animate-pulse rounded-xl bg-[#E4E7F0]" />
            <div className="h-24 animate-pulse rounded-xl bg-[#E4E7F0]" />
          </div>
        ) : !order ? (
          <div className="px-4 py-8 text-center text-[15px] text-[#8F98AD]">
            Order not found
          </div>
        ) : (
          <>
            {/* Order + Placed row */}
            <div className="flex items-baseline justify-between gap-2 px-4 pt-2 pb-3">
              <span className="text-[14px] font-bold text-[#4E5667]">
                Order: {order.order_number}
              </span>
              <span className="shrink-0 text-right text-[12px] font-medium text-[#8F98AD]">
                Placed: {formatPlacedAt(order.ordered_at)}
              </span>
            </div>

            {/* Summary card — light blue border */}
            <div className="mx-4 rounded-[12px] border border-[#A8C8EF] bg-white px-4 py-4 shadow-sm">
              <div className="flex items-start justify-between gap-3">
                <span className="text-[14px] font-bold text-[#4E5667]">
                  Order Details
                </span>
                <div className="text-right text-[13px] font-medium text-[#4E5667]">
                  <span>SKUs: {order.skus}</span>
                  <span className="mx-2 text-[#C5CEDE]">|</span>
                  <span>Items: {order.units}</span>
                </div>
              </div>

              <div className="my-3 border-t border-[#E8EDF5]" />

              <div className="flex items-start justify-between gap-3">
                <span className="text-[14px] font-bold text-[#4E5667]">
                  Delivery
                </span>
                <div className="max-w-[65%] text-right text-[13px] text-[#4E5667]">
                  <div className="font-medium leading-snug">
                    {order.delivery_method || "—"}
                    {Number(order.delivery_charge) <= 0
                      ? " - Free"
                      : ` - ${order.currency_symbol}${order.delivery_charge.toFixed(2)}`}
                  </div>
                  <div className="mt-2 space-y-0.5 text-[12px] leading-snug text-[#6B7280]">
                    {order.address.line1 ? (
                      <div>{order.address.line1}</div>
                    ) : null}
                    {order.address.line2 ? (
                      <div>{order.address.line2}</div>
                    ) : null}
                    {order.address.city ? <div>{order.address.city}</div> : null}
                    {order.address.zip ? <div>{order.address.zip}</div> : null}
                    {order.address.country ? (
                      <div>{order.address.country}</div>
                    ) : null}
                  </div>
                </div>
              </div>
            </div>

            {/* Payment summary + reorder (below main card) */}
            <div className="mx-4 mt-4 rounded-[12px] border border-[#E4E7F0] bg-white px-4 py-3">
              <h3 className="mb-2 text-[13px] font-bold text-[#4E5667]">
                Payment
              </h3>
              <div className="space-y-1.5 text-[13px] text-[#4E5667]">
                <div className="flex justify-between">
                  <span>Subtotal</span>
                  <span dir="ltr">
                    {order.currency_symbol}
                    {order.subtotal.toFixed(2)}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span>Wallet discount</span>
                  <span dir="ltr">
                    {order.currency_symbol}
                    {Math.abs(order.wallet_discount).toFixed(2)}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span>Delivery</span>
                  <span dir="ltr">
                    {order.currency_symbol}
                    {(order.delivery_charge || 0).toFixed(2)}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span>VAT</span>
                  <span dir="ltr">
                    {order.currency_symbol}
                    {order.vat_amount.toFixed(2)}
                  </span>
                </div>
                <div className="flex justify-between border-t border-[#E8EDF5] pt-2 font-bold">
                  <span>Payment total</span>
                  <span dir="ltr">
                    {order.currency_symbol}
                    {(
                      order.payment_amount ??
                      Math.max(
                        0,
                        (order.total_paid ?? 0) -
                          (order.wallet_credit_used ?? 0),
                      )
                    ).toFixed(2)}
                  </span>
                </div>
              </div>
              <button
                type="button"
                disabled={reordering}
                onClick={async () => {
                  if (!orderNumber) return;
                  try {
                    setReordering(true);
                    const res = await api.post(`/orders/${orderNumber}/reorder`);
                    const items = Array.isArray(res?.data?.items)
                      ? res.data.items
                      : [];
                    const mapped = items.map((it: { product: unknown; quantity: number }) => ({
                      product: it.product,
                      quantity: it.quantity,
                    }));
                    onReorder(mapped);
                  } catch {
                    /* optional toast */
                  } finally {
                    setReordering(false);
                  }
                }}
                className="mt-4 w-full rounded-[10px] py-3 text-[15px] font-[700] leading-none text-white shadow-sm transition-opacity hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-60"
                style={{
                  background:
                    "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)",
                }}
              >
                {reordering ? "Reordering…" : "Reorder items"}
              </button>
            </div>

            {/* Order lines */}
            <div className="mt-6 px-4">
              <h2 className="mb-1 text-[15px] font-bold text-[#4E5667]">
                Order Lines
              </h2>
              <div className="divide-y divide-[#ECEEF3]">
                {order.items.map((it, idx) => {
                  const imgSrc =
                    resolveBackendAssetUrl(it.product_image) ??
                    it.product_image ??
                    "";
                  return (
                    <div
                      key={`${it.product_id}-${idx}`}
                      className="flex gap-3 py-4 first:pt-3"
                    >
                      <div className="h-14 w-14 shrink-0 overflow-hidden rounded-md bg-[#F1F2F7]">
                        {imgSrc ? (
                          // eslint-disable-next-line @next/next/no-img-element
                          <img
                            src={imgSrc}
                            alt=""
                            className="h-full w-full object-cover"
                          />
                        ) : null}
                      </div>
                      <div className="min-w-0 flex-1">
                        <div className="text-[12px] font-bold uppercase leading-snug text-[#1F2937]">
                          {it.product_name || `Product #${it.product_id}`}
                        </div>
                        <div className="mt-1 text-[12px] text-[#8F98AD]">
                          {it.quantity} @ {order.currency_symbol}
                          {it.unit_price.toFixed(2)}
                        </div>
                      </div>
                      <div className="shrink-0 self-center text-[14px] font-bold text-[#1F2937]" dir="ltr">
                        {order.currency_symbol}
                        {it.total_price.toFixed(2)}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          </>
        )}
      </div>

      {/* Bottom nav — match wallet / orders app shell; Account active (orders flow) */}
      <nav className="fixed bottom-0 left-1/2 z-50 w-full max-w-[402px] -translate-x-1/2 shadow-[0px_-1px_8px_0px_#555E5814]">
        <div className="grid h-[74px] grid-cols-5 items-center border-t border-[#E4E7F0] bg-[#F1F2F7] px-2 pb-[10px] pt-2">
          <button
            type="button"
            onClick={() => onNavigate("dashboard")}
            className="flex flex-col items-center gap-1 text-[11px] font-medium leading-none text-[#BDC7DE]"
          >
            <FontAwesomeIcon icon={faChartSimple} className="text-[23px]" />
            <span>Dashboard</span>
          </button>
          <button
            type="button"
            onClick={() => onNavigate("shop", false)}
            className="flex flex-col items-center gap-1 text-[11px] font-medium leading-none text-[#BDC7DE]"
          >
            <FontAwesomeIcon icon={faShop} className="text-[23px]" />
            <span>Shop</span>
          </button>
          <button
            type="button"
            onClick={() => onNavigate("shop", true)}
            className="flex flex-col items-center gap-1 text-[11px] font-medium leading-none text-[#BDC7DE]"
          >
            <FontAwesomeIcon icon={faHeart} className="text-[23px]" />
            <span>Favourites</span>
          </button>
          <button
            type="button"
            onClick={() => onNavigate("wallet")}
            className="flex flex-col items-center gap-1 text-[11px] font-medium leading-none text-[#BDC7DE]"
          >
            <FontAwesomeIcon icon={faWallet} className="text-[23px]" />
            <span>Wallet</span>
          </button>
          <button
            type="button"
            onClick={() => onNavigate("account")}
            className="flex flex-col items-center gap-1 text-[11px] font-medium leading-none text-[#4A90E5]"
          >
            <FontAwesomeIcon icon={faUser} className="text-[23px]" />
            <span>Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}
