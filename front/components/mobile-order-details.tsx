"use client";

import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faChartSimple,
  faHeart,
  faShop,
  faUser,
  faWallet,
} from "@fortawesome/free-solid-svg-icons";
import { useEffect, useState } from "react";
import { Banner } from "@/components/banner";
import { MobilePageHeader } from "@/components/mobile-page-header";
import { resolveBackendAssetUrl } from "@/lib/utils";
import { formatDisplayDateTime } from "@/lib/format-date-time";

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

type OrderPayment = {
  date?: string | null;
  reference_no?: string | null;
  card_brand?: string | null;
  card_last4?: string | null;
  card_expiry?: string | null;
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
  company_name?: string | null;
  branch_name?: string | null;
  address: Address;
  payments?: OrderPayment[];
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

function formatStatusLabel(status: string): string {
  return String(status || "")
    .replace(/_/g, " ")
    .toLowerCase()
    .replace(/\b\w/g, (c) => c.toUpperCase());
}

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
  if (u.includes("PENDING") || u.includes("PROCESS") || u.includes("NEW")) {
    return "border border-[#FEDF89] bg-[#FFFAEB] text-[#B54708]";
  }
  return "border border-[#E4E7EC] bg-[#F9FAFB] text-[#344054]";
}

function LabeledAddressRows({
  companyLine,
  address,
}: {
  companyLine: string;
  address: Address;
}) {
  const rows: { label: string; value: string }[] = [];
  if (companyLine.trim()) {
    rows.push({ label: "Company Name", value: companyLine.trim() });
  }
  if (address.line1?.trim()) rows.push({ label: "Address line 1", value: address.line1.trim() });
  if (address.line2?.trim()) rows.push({ label: "Address line 2", value: address.line2.trim() });
  if (address.city?.trim()) rows.push({ label: "City", value: address.city.trim() });
  if (address.zip?.trim()) rows.push({ label: "Postcode", value: address.zip.trim() });
  if (address.country?.trim()) rows.push({ label: "Country", value: address.country.trim() });

  if (rows.length === 0) {
    return <p className="text-right text-[12px] text-[#8F98AD]">No address on file</p>;
  }

  return (
    <div className="mt-3 space-y-2.5 border-t border-[#E8EDF5] pt-3">
      {rows.map((row) => (
        <div key={row.label} className="flex items-start justify-between gap-3 text-[13px] leading-snug">
          <span className="shrink-0 text-[12px] font-medium text-[#8F98AD]">{row.label}</span>
          <span className="max-w-[62%] text-right font-medium text-[#4E5667]">{row.value}</span>
        </div>
      ))}
    </div>
  );
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
  const headerTitle = `#${displayOrderId}`;
  const companyLine = order
    ? String(order.company_name || order.branch_name || "").trim()
    : "";

  const headerTrailing = loading ? (
    <span className="h-[26px] w-[76px] shrink-0 animate-pulse rounded-full bg-[#E4E7EC]/90" aria-hidden />
  ) : order ? (
    <span
      className={`max-w-[118px] truncate rounded-full px-2.5 py-1 text-center text-[11px] font-semibold leading-tight ${statusBadgeClass(order.fulfillment_status)}`}
    >
      {formatStatusLabel(order.fulfillment_status)}
    </span>
  ) : null;

  return (
    <div
      className="relative mx-auto flex h-[100dvh] min-h-0 w-full max-w-[402px] flex-col bg-[#FAFBFD]"
      style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
    >
      <MobilePageHeader title={headerTitle} onBack={onBack} trailing={headerTrailing} />

      <main className="scrollbar-hide min-h-0 flex-1 overflow-x-hidden overflow-y-auto bg-[#FAFBFD] px-6 pb-[88px] pt-4">
        <div className="mx-auto flex w-full max-w-[354px] flex-col gap-4">
          <Banner className="h-[89px] max-w-[354px] rounded-[10px] border border-[#E2E2E2]" />

          {loading ? (
            <div className="space-y-4">
              <div className="h-4 w-3/4 animate-pulse rounded bg-[#E4E7F0]" />
              <div className="h-36 animate-pulse rounded-[12px] border border-[#E8ECF4] bg-white" />
              <div className="h-40 animate-pulse rounded-[10px] border border-[#E8ECF4] bg-white" />
            </div>
          ) : !order ? (
            <div className="rounded-[10px] border border-[#E2E2E2] bg-white py-10 text-center text-[15px] text-[#8F98AD]">
              Order not found
            </div>
          ) : (
            <>
              <div className="flex items-baseline justify-between gap-2">
                <span className="text-[13px] font-medium tracking-[0.04em] text-[#3D495E]">
                  Order: {order.order_number}
                </span>
                <span className="shrink-0 text-right text-[12px] font-medium text-[#8F98AD]">
                  Placed: {formatDisplayDateTime(order.ordered_at)}
                </span>
              </div>

              <div className="rounded-[12px] border border-[#A8C8EF] bg-white px-4 py-4 shadow-sm">
                <div className="flex items-start justify-between gap-3">
                  <span className="text-[14px] font-bold text-[#4E5667]">Order Details</span>
                  <div className="text-right text-[13px] font-medium tracking-[0.04em] text-[#4E5667]">
                    <span>SKUs: {order.skus}</span>
                    <span className="mx-2 text-[#C5CEDE]">|</span>
                    <span>Items: {order.units}</span>
                  </div>
                </div>

                <div className="my-3 border-t border-[#E8EDF5]" />

                <div className="flex items-start justify-between gap-3">
                  <span className="shrink-0 text-[14px] font-bold text-[#4E5667]">Delivery</span>
                  <div className="max-w-[72%] text-right text-[13px] font-medium leading-snug text-[#4E5667]">
                    {order.delivery_method || "—"}
                    {Number(order.delivery_charge) <= 0 ? " - Free" : ` - ${order.currency_symbol}${order.delivery_charge.toFixed(2)}`}
                  </div>
                </div>

                <LabeledAddressRows companyLine={companyLine} address={order.address} />
              </div>

              <section aria-label="Order lines">
                <h2 className="mb-2 text-[15px] font-bold text-[#3D495E]">Order Lines</h2>
                <div className="overflow-hidden rounded-[10px] border border-[#E2E2E2] bg-white">
                  {order.items.map((it, idx) => {
                    const imgSrc =
                      resolveBackendAssetUrl(it.product_image) ?? it.product_image ?? "";
                    const isLast = idx === order.items.length - 1;
                    return (
                      <div
                        key={`${it.product_id}-${idx}`}
                        className={`flex gap-3 px-3 py-3.5 ${!isLast ? "border-b border-[#E2E2E2]" : ""}`}
                      >
                        <div className="h-14 w-14 shrink-0 overflow-hidden rounded-md bg-[#F1F2F7]">
                          {imgSrc ? (
                            // eslint-disable-next-line @next/next/no-img-element
                            <img src={imgSrc} alt="" className="h-full w-full object-cover" />
                          ) : null}
                        </div>
                        <div className="min-w-0 flex-1">
                          <div className="text-[11px] font-bold uppercase leading-snug tracking-wide text-[#1F2937]">
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
              </section>

              <div className="rounded-[12px] border border-[#E4E7F0] bg-white px-4 py-4 shadow-sm">
                <h3 className="mb-3 text-[14px] font-bold text-[#4E5667]">Payment</h3>
                <div
                  className={`mb-3 space-y-2 text-[12px] text-[#4E5667] ${Array.isArray(order.payments) && order.payments.length > 0 ? "" : "border-b border-[#E8EDF5] pb-3"}`}
                >
                  <div className="flex justify-between gap-3">
                    <span className="shrink-0 text-[#8F98AD]">Status</span>
                    <span className="text-right font-medium">{formatStatusLabel(order.payment_status)}</span>
                  </div>
                </div>
                {(() => {
                  const p = Array.isArray(order.payments) && order.payments.length > 0 ? order.payments[0] : null;
                  const rowClass = "mb-3 space-y-2 border-b border-[#E8EDF5] pb-3 text-[12px] text-[#4E5667]";
                  if (!p) {
                    return null;
                  }
                  return (
                    <div className={rowClass}>
                      <div className="flex justify-between gap-3">
                        <span className="shrink-0 text-[#8F98AD]">Paid on</span>
                        <span className="text-right font-medium">{formatDisplayDateTime(p.date)}</span>
                      </div>
                      <div className="flex justify-between gap-3">
                        <span className="shrink-0 text-[#8F98AD]">Payment reference</span>
                        <span className="text-right font-medium">{p.reference_no ?? "—"}</span>
                      </div>
                      <div className="flex justify-between gap-3">
                        <span className="shrink-0 text-[#8F98AD]">Card brand</span>
                        <span className="text-right font-medium">{p.card_brand ?? "—"}</span>
                      </div>
                      <div className="flex justify-between gap-3">
                        <span className="shrink-0 text-[#8F98AD]">Card number</span>
                        <span className="text-right font-medium" dir="ltr">
                          {p.card_last4 ? `•••• ${p.card_last4}` : "—"}
                        </span>
                      </div>
                      <div className="flex justify-between gap-3">
                        <span className="shrink-0 text-[#8F98AD]">Expires</span>
                        <span className="text-right font-medium">{p.card_expiry ?? "—"}</span>
                      </div>
                    </div>
                  );
                })()}
                <div className="space-y-2 text-[13px] text-[#4E5667]">
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
                  <div className="flex justify-between border-t border-[#E8EDF5] pt-2.5 text-[14px] font-bold">
                    <span>Payment total</span>
                    <span dir="ltr">
                      {order.currency_symbol}
                      {(
                        order.payment_amount ??
                        Math.max(0, (order.total_paid ?? 0) - (order.wallet_credit_used ?? 0))
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
                      const items = Array.isArray(res?.data?.items) ? res.data.items : [];
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
                  className="mt-4 flex h-[48px] w-full items-center justify-center rounded-[25px] bg-gradient-to-b from-[#2868C0] to-[#4C92E9] text-[16px] font-bold text-white shadow-[0_2px_10px_rgba(40,104,192,0.35)] transition-opacity hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-60 disabled:shadow-none"
                >
                  {reordering ? "Reordering…" : "Reorder items"}
                </button>
              </div>
            </>
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
              <FontAwesomeIcon icon={faShop} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Shop</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", true)}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faHeart} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Favourites</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("wallet")}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faWallet} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Wallet</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("account")}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-0.5 text-[#4A90E5]"
              aria-current="page"
            >
              <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#4A90E5]">
                <FontAwesomeIcon icon={faUser} className="h-[18px] w-[18px] text-[18px] leading-none text-white" aria-hidden />
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
