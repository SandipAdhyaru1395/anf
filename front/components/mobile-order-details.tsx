"use client";

import { ArrowLeft, Home, ShoppingBag, User, Wallet, Package } from "lucide-react";
import { Card } from "@/components/ui/card";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faTruck } from "@fortawesome/free-solid-svg-icons";
import { useEffect, useState } from "react";
import { Banner } from "@/components/banner";

interface MobileOrderDetailsProps {
  orderNumber: string;
  onNavigate: (page: "dashboard" | "shop" | "wallet" | "account" | "orders" | "order-details") => void;
  onBack: () => void;
  onReorder: (items: Array<{ product: any; quantity: number }>) => void;
}

type Address = { line1?: string | null; line2?: string | null; city?: string | null; state?: string | null; zip?: string | null; country?: string | null };

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
  currency_symbol: string;
  address: Address;
  items: Array<{ product_id: number; product_name?: string | null; product_image?: string | null; quantity: number; unit_price: number; wallet_credit_earned: number; total_price: number }>;
};

export function MobileOrderDetails({ orderNumber, onNavigate, onBack, onReorder }: MobileOrderDetailsProps) {
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

  return (
    <div className="min-h-screen w-full max-w-[1000px] mx-auto">
      {/* Header */}
      {/* <header className="bg-white px-4 py-3 flex items-center gap-3 border-b">
        <button onClick={onBack} className="p-2 hover:cursor-pointer rounded-full">
          <ArrowLeft className="w-5 h-5 text-gray-600" />
        </button>
        <div className="flex items-center gap-2">
          <div className="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
            <Package className="w-4 h-4 text-green-600" />
          </div>
          <span className="text-sm text-gray-600">Orders / Details - {orderNumber}</span>
        </div>
      </header> */}

      <header className="bg-white flex items-center border-b h-[50px]">
        <div className="w-[66px] h-[25px] flex items-center justify-center">
          <FontAwesomeIcon icon={faTruck} className="text-green-600" style={{ width: "30px", height: "24px" }} />
        </div>
        <span onClick={onBack} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline">
          Orders
        </span>
        &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
        &nbsp;<span className="text-[16px] font-semibold">Details - {orderNumber}</span>
      </header>

      {/* Banner */}
      <Banner />

      <main className="mb-[82px] mt-[10px]">
        {loading ? (
          <div className="p-[10px] space-y-3">
            <div className="h-20 bg-gray-200 rounded animate-pulse" />
            <div className="h-20 bg-gray-200 rounded animate-pulse" />
          </div>
        ) : !order ? (
          <div className="p-4 text-center text-gray-500">Order not found</div>
        ) : (
          <>
            {/* Order Summary (exact style with three sections and right-aligned values) */}
            <div className="px-[10px]">
              <h3 className="font-semibold text-black mt-[20px] mb-[10px] leading-[16px] text-[16px]">Order Summary</h3>
              <div className="bg-white border border-gray-300 rounded-md p-[14px] leading-[16px]">
                {/* Order details row */}
                <div className="">
                  <div className="flex items-start justify-between gap-x-[4px] gap-y-[8px] my-[4px] leading-[16px]">
                    <span className="font-semibold text-[14px] text-black flex-1 max-w-[742px] h-[16px]">Order details</span>
                    <div className="text-sm w-[200px] gap-x-[4px] gap-y-[8px] flex flex-col">
                      <div className="flex justify-between text-right flex-1">
                        <span className="text-black w-[120px] h-[16px] flex-1">Units</span>
                        <span className="text-black w-[80px] h-[16px] flex-1">{order.units}</span>
                      </div>
                      <div className="flex justify-between text-right flex-1">
                        <span className="text-black w-[120px] h-[16px] flex-1">SKUs</span>
                        <span className="text-black w-[80px] h-[16px] flex-1">{order.skus}</span>
                      </div>
                    </div>
                  </div>
                </div>
                {/* Delivery row */}
                <div className="border-t mt-[20px] pt-[20px] delivery-info">
                  <div className="flex items-start justify-between gap-x-[4px] gap-y-[8px] my-[4px] leading-[16px]">
                    <span className="font-semibold text-[14px] text-black flex-1 max-w-[742px] h-[16px]">Delivery</span>
                    <div className="text-right text-[14px] text-black max-w-[60%] gap-x-[4px] gap-y-[8px] flex flex-col">
                      <div className="">{order.delivery_method}</div>
                      <div>{order.address.line1}</div>
                      {order.address.line2 && <div>{order.address.line2}</div>}
                      <div>{order.address.city}</div>
                      <div>{order.address.zip}</div>
                      <div>{order.address.country}</div>
                    </div>
                  </div>
                </div>
                {/* Summary row */}
                <div className="border-t mt-[20px] pt-[20px]">
                  <div className="flex items-start justify-between gap-x-[4px] gap-y-[8px] my-[4px] leading-[16px]">
                    <span className="font-semibold text-[14px] text-black flex-1 max-w-[742px] h-[16px]">Summary</span>
                    <div className="text-sm w-[200px] gap-x-[4px] gap-y-[8px] flex flex-col">
                      <div className="flex justify-between text-right flex-1 text-[14px]">
                        <span className="text-black w-[120px] h-[16px]">Subtotal</span>
                        <span className="text-black w-[80px] h-[16px] flex-1">
                          {order.currency_symbol}
                          {order.subtotal.toFixed(2)}
                        </span>
                      </div>
                      <div className="flex justify-between text-right flex-1 text-[14px]">
                        <span className="text-black w-[120px] h-[16px]">Wallet Discount</span>
                        <span className="text-black w-[80px] h-[16px] flex-1">
                          {order.currency_symbol}
                          {order.wallet_discount.toFixed(2)}
                        </span>
                      </div>
                      <div className="flex justify-between text-right flex-1 text-[14px]">
                        <span className="text-black w-[120px] h-[16px]">Delivery</span>
                        <span className="text-black w-[80px] h-[16px] flex-1">{order.currency_symbol}{order.delivery_charge ? order.delivery_charge.toFixed(2) : '0.00'}</span>
                      </div>
                      <div className="flex justify-between text-right flex-1 text-[14px]">
                        <span className="text-black w-[120px] h-[16px]">VAT ({ (order.vat_amount > 0 ? ((order.vat_amount*100) / (order.subtotal+order.delivery_charge-order.wallet_discount)).toFixed(2) : '0.00') }%)</span>
                        <span className="text-black w-[80px] h-[16px] flex-1">
                          {order.currency_symbol}
                          {order.vat_amount.toFixed(2)}
                        </span>
                      </div>
                      <div className="flex justify-between text-right flex-1 text-[14px]">
                        <span className="text-black w-[120px] h-[16px] font-semibold">Payment Total</span>
                        <span className="text-black w-[80px] h-[16px] flex-1 font-semibold">
                          {order.currency_symbol}
                          {((order as any).payment_amount ?? Math.max(0, (order.total_paid ?? 0) - ((order as any).wallet_credit_used ?? 0))).toFixed(2)}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {/* Reorder Items CTA */}
              <div className="mt-[10px] mb-[35px] flex w-full">
                <button
                  disabled={reordering}
                  onClick={async () => {
                    if (!orderNumber) return;
                    try {
                      setReordering(true);
                      const res = await api.post(`/orders/${orderNumber}/reorder`);
                      const items = Array.isArray(res?.data?.items) ? res.data.items : [];
                      const mapped = items.map((it: any) => ({ product: it.product, quantity: it.quantity }));
                      onReorder(mapped);
                    } catch (e) {
                      // noop; optionally show a toast in future
                    } finally {
                      setReordering(false);
                    }
                  }}
                  className="w-full h-[50px] bg-green-600 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold text-lg py-[13px] rounded-md box-shadow-bottom"
                >
                  {reordering ? "Reordering…" : "Reorder Items"}
                </button>
              </div>
            </div>

            {/* Addresses removed per request */}

            {/* Order Lines */}
            <div className="px-[10px]">
              <h3 className="font-semibold text-gray-900 mb-[10px] mt-[20px] line-16">Order Lines</h3>
              <div className="mt-[10px]">
                {order.items.map((it, idx) => (
                  <div key={it.product_id + "-" + idx} className="py-[16px] border-t last:border-y">
                    <div className="flex">
                      {it.product_image ? <img src={it.product_image} alt="" className="w-[50px] h-[50px] mr-[10px] object-cover" /> : <div className="w-[50px] h-[50px] mr-[10px] bg-gray-100" />}
                      <div className="font-medium text-gray-900 text-[16px] leading-[16px] flex-5">{it.product_name || `Product #${it.product_id}`}</div>
                      <div className="text-gray-800 text-[16px] leading-[16px] flex-1 text-center">{it.quantity}</div>
                      <div className="font-semibold text-right text-[16px] leading-[16px]">
                        {order.currency_symbol}
                        {it.total_price.toFixed(2)}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </>
        )}
      </main>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 box-shadow-top px-[18px]">
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
