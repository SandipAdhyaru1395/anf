"use client";
import { Button } from "@/components/ui/button";
import { useCurrency } from "@/components/currency-provider";
import React, { useEffect, useState } from "react";
import { Card } from "@/components/ui/card";
import { ChevronRight } from "lucide-react";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faGauge,
  faShop,
  faWallet,
  faUser,
  faStar,
  faBell,
  faChevronRight,
} from "@fortawesome/free-solid-svg-icons";
import { useCustomer } from "@/components/customer-provider";
import { Banner } from "@/components/banner";
import { startLoading, stopLoading } from "@/lib/loading";

interface MobileDashboardProps {
  onNavigate: (
    page: "dashboard" | "shop" | "wallet" | "account" | "orders",
    favorites?: boolean,
  ) => void;
  onOpenOrder?: (orderNumber: string) => void;
}

export function MobileDashboard({
  onNavigate,
  onOpenOrder,
}: MobileDashboardProps) {
  const { symbol } = useCurrency();
  const { customer } = useCustomer();
  const wallet = Number(customer?.wallet_balance || 0);

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
          } catch {}
        }
      } catch (e) {
      } finally {
        if (typeof window !== "undefined") {
          stopLoading();
        }
        try {
          sessionStorage.removeItem("orders_needs_refresh");
        } catch {}
      }
    };

    let refreshed = false;
    try {
      const needs = sessionStorage.getItem("orders_needs_refresh");
      if (needs === "1") {
        refreshed = true;
        fetchOrders();
      }
    } catch {}

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
      } catch {}
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
    <div className="min-h-screen app-container bg-[#f4f2f9]">
      {/* HEADER */}
      <header className="bg-white px-3 py-2 flex items-center justify-center shadow-sm">
        <div className="w-full flex justify-center"></div>
      </header>

      <main className="pb-20">
        <Banner />

        {/* TOP SECTION */}
        <div className="p-3 space-y-3">
          {/* REFERRAL */}
          <Card className="bg-green-500 text-white rounded-md px-3 py-2">
            <h3 className="font-semibold">Referral Rewards</h3>
            <p className="text-xs text-black font-semibold">
              Refer a Retailer to earn Rewards
            </p>
          </Card>

          {/* WALLET */}
          <Card
            onClick={() => onNavigate("wallet")}
            className="flex justify-between items-center px-4 py-2 border rounded-md cursor-pointer"
          >
            <div className="flex items-center gap-2">
              <FontAwesomeIcon
                icon={faWallet}
                className="text-green-600 text-sm"
              />
              <span className="text-sm font-semibold">
                {symbol}
                {wallet.toFixed(2)} credit in your wallet
              </span>
              <FontAwesomeIcon
                icon={faChevronRight}
                className="text-green-600 text-lg"
              />
            </div>
          </Card>

          {/* BUTTONS */}
          <div className="grid grid-cols-2 gap-2">
            <Button
              onClick={() => onNavigate("shop")}
              className="bg-green-500 text-white h-11"
            >
              <FontAwesomeIcon icon={faShop} className="mr-2" />
              Shop
            </Button>
            <Button
              onClick={() => onNavigate("shop", true)}
              className="bg-green-500 text-white h-11"
            >
              <FontAwesomeIcon icon={faStar} className="mr-2" />
              Favourites
            </Button>
          </div>
        </div>

        {/* NOTIFICATIONS */}
        <div className="flex flex-col items-center mt-[10px]">
          <h3 className="w-[370px] text-[15px] font-bold text-gray-700 mb-[6px]">
            Recent Notifications
          </h3>

          {[1, 2].map((n) => (
            <Card
              key={n}
              className="w-[370px] h-[34px] flex items-center justify-between px-[16px] border border-[#4A90E5] rounded-[5px] mb-[8px]"
            >
              <div className="flex items-center gap-3">
                <FontAwesomeIcon icon={faBell} className="text-[#4A90E5]" />
                <span className="text-[13px] font-bold text-gray-700">
                  "You've left products in your basket",
                </span>
                <FontAwesomeIcon
                  icon={faChevronRight}
                  className="text-[#4A90E5]"
                />
              </div>
            </Card>
          ))}
        </div>
        {/* LEADING BRANDS */}
        <div className="w-[370px] mx-auto mt-[20px] border-t pt-[10px]">
          <h3 className="text-[14px] font-bold text-gray-700 mb-[10px]">
            Leading Brands
          </h3>

          <div className="flex gap-[10px] overflow-x-auto">
            {["Lost Mary", "Elfbar", "Ske", "IVG", "Oxva"].map((b, i) => (
              <div key={i} className="flex flex-col items-center min-w-[70px]">
                <div className="w-[60px] h-[60px] bg-white rounded-full shadow flex items-center justify-center text-[10px] font-bold">
                  {b}
                </div>

                <span className="text-[10px] text-gray-400 mt-[4px]">{b}</span>
              </div>
            ))}
          </div>
        </div>

        {/* ORDERS */}
        {orders.length > 0 && (
          <div className="px-3 mt-4 border-t pt-3">
            <h3 className="text-[15px] font-bold mb-2">Recent Orders</h3>

            {orders.map((o, i) => (
              <Card key={i} className="p-3 mb-3 border rounded-md">
                <div className="flex justify-between text-sm">
                  <div className="space-y-1">
                    <p>Order No: {o.order_number}</p>
                    <p>Units: {o.units}</p>
                    <p>SKUs: {o.skus}</p>
                    <p className="font-semibold">
                      {o.currency_symbol}
                      {o.total_paid.toFixed(2)}
                    </p>
                  </div>

                  <ChevronRight
                    onClick={() => onOpenOrder && onOpenOrder(o.order_number)}
                    className="text-green-600 cursor-pointer"
                  />
                </div>
              </Card>
            ))}

            <Button
              onClick={() => onNavigate("orders")}
              className="w-full border border-green-600 bg-white text-black"
            >
              View All Orders
            </Button>
          </div>
        )}
      </main>
      {/* BASKET */}
      <div className="fixed app-fixed bottom-[72px] bg-gradient-to-r from-[#E8E8ED] to-[#F4F2F9] px-[8px] py-[12px] flex justify-between items-center">
        <div className="text-[11px]">
          <div className="font-bold">0 Units | 0 SKUs | {symbol}0.00</div>
          <div className="text-gray-500 text-[10px]">
            Includes FREE delivery
          </div>
        </div>

        <Button className="bg-[#4A90E5] h-[32px] text-[12px]">
          View Basket
        </Button>
      </div>

      {/* BOTTOM NAV */}
      <nav className="fixed app-fixed bottom-0 bg-white border-t flex justify-around py-3">
        <button className="flex flex-col items-center text-gray-500">
          <FontAwesomeIcon icon={faGauge} />
          <span className="text-xs">Dashboard</span>
        </button>
        <button
          onClick={() => onNavigate("shop")}
          className="flex flex-col items-center text-gray-500"
        >
          <FontAwesomeIcon icon={faShop} />
          <span className="text-xs">Shop</span>
        </button>
        <button
          onClick={() => onNavigate("wallet")}
          className="flex flex-col items-center text-gray-500"
        >
          <FontAwesomeIcon icon={faWallet} />
          <span className="text-xs">Wallet</span>
        </button>
        <button
          onClick={() => onNavigate("account")}
          className="flex flex-col items-center text-gray-500"
        >
          <FontAwesomeIcon icon={faUser} />
          <span className="text-xs">Account</span>
        </button>
      </nav>
    </div>
  );
}
