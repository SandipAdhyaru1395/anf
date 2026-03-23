"use client";

import { ArrowLeft, Home, ShoppingBag, User, Wallet, Package, ChevronRight } from "lucide-react";
import { Card } from "@/components/ui/card";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faGauge,
  faShop,
  faUser,
  faWallet,
  faBars,
  faTruck,
  faChevronRight,
  faChevronLeft,
  faBox,
  faChartSimple,
  faHeart,
  faCalendarAlt
} from "@fortawesome/free-solid-svg-icons";
import { useEffect, useState } from "react";
import { Banner } from "@/components/banner";

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

export function MobileOrders({ onNavigate, onBack, onOpenOrder }: MobileOrdersProps) {
  const [orders, setOrders] = useState<OrderItem[]>([]);
  const [loading, setLoading] = useState<boolean>(true);

  useEffect(() => {
    const fetchAll = async () => {
      setLoading(true);
      try {
        // Fetch a large number to approximate "all"; could be paginated later
        const res = await api.get("/orders", { params: { limit: 500 } });
        const json = res?.data;
        if (json?.success && Array.isArray(json.orders)) {
          setOrders(json.orders);
        } else {
          setOrders([]);
        }
      } catch {
        setOrders([]);
      } finally {
        setLoading(false);
      }
    };
    fetchAll();
  }, []);

  return (
    <div className="min-h-screen w-full max-w-[402px] mx-auto bg-[#F8F7FC] flex flex-col">
      {/* Header */}
      <div className="bg-[#F8F7FC] flex items-center justify-between px-4 h-[56px] sticky top-0 z-50">
        <button onClick={onBack} className="flex items-center gap-1 text-[#8F98AD] font-bold text-[13px]">
          <FontAwesomeIcon icon={faChevronLeft} className="text-[14px]" />
          <span>Back</span>
        </button>
        <h1 className="text-[16px] font-bold text-[#3D495E]">My Orders</h1>
        <div className="w-[40px]"></div> {/* Spacer for centering */}
      </div>

      <div className="flex w-full justify-center px-3 py-3">
        <Banner />
      </div>

      <main className="p-[10px] mb-[82px]">
        {loading ? (
          <div className="p-4">
            <div className="space-y-3">
              <div className="h-16 bg-gray-200 rounded animate-pulse" />
              <div className="h-16 bg-gray-200 rounded animate-pulse" />
              <div className="h-16 bg-gray-200 rounded animate-pulse" />
            </div>
          </div>
        ) : (
          <div className="space-y-0 flex-1">
            {orders.length === 0 ? (
              <div className="text-center text-gray-500 py-10 bg-white">No orders found</div>
            ) : (
              orders.map((o, idx) => (
                <div key={o.order_number + idx} className="bg-white border-b border-[#F1F2F7] 
                p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                  <div className="flex items-center gap-4 flex-1">
                    <div className="w-[42px] h-[42px] bg-[#EAF0FA] rounded-full 
                    flex items-center justify-center flex-shrink-0">
                      <FontAwesomeIcon icon={faBox} className="text-[#4A90E5] text-[20px]" />
                    </div>
                    <div className="flex flex-col gap-1.5 flex-1 min-w-0">
                      <div className="flex items-center gap-2 flex-wrap">
                        <span className="bg-[#EAF0FA] text-[#4A90E5] px-2 py-0.5
                         rounded-full text-[10px] font-bold uppercase tracking-wide border border-[#DCE1EE]">
                          {o.fulfillment_status}
                        </span>
                        <span className="bg-[#E4FAE7] text-[#34C759] px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border border-[#D2EBD5]">
                          {o.payment_status}
                        </span>
                      </div>
                      <div className="flex items-center gap-1 text-[#3D495E] font-bold text-[14px]">
                        <span>Order: {o.order_number}</span>
                        <span className="text-[#8F98AD] mx-1">•</span>
                        <span>Total: {o.currency_symbol}{o.total_paid.toFixed(2)}</span>
                      </div>
                      <div className="flex items-center gap-2 text-[#8F98AD] text-[11px] font-medium flex-wrap">
                        <span>{o.skus} SKUs</span>
                        <span className="opacity-50">•</span>
                        <span>{o.units} Items</span>
                        <span className="opacity-50">•</span>
                        <div className="flex items-center gap-1">
                          <FontAwesomeIcon icon={faCalendarAlt} className="text-[10px]" />
                          <span>{o.ordered_at}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div className="flex items-center h-full">
                    <div className="h-[40px] w-[1px] bg-[#F1F2F7] mx-2"></div>
                    <button onClick={() => onOpenOrder && onOpenOrder(o.order_number)} className="p-2 cursor-pointer hover:bg-[#EAF0FA] rounded-full transition-colors">
                      <FontAwesomeIcon icon={faChevronRight} className="text-[#4A90E5] text-[18px]" />
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>
        )}
      </main>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[402px] z-50 shadow-[0px_-1px_8px_0px_#555E5814] bg-white">
        <div className="h-[74px] px-2 pt-[8px] pb-[10px] grid grid-cols-5 items-center bg-[#F1F2F7] border-t border-[#E4E7F0]">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faChartSimple} className="text-[23px]" />
            <span>Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faShop} className="text-[23px]" />
            <span>Shop</span>
          </button>
          <button onClick={() => onNavigate("shop", true)} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faHeart} className="text-[23px]" />
            <span>Favourites</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faWallet} className="text-[23px]" />
            <span>Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center gap-[4px] text-[#4A90E5] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faUser} className="text-[23px]" />
            <span>Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}
