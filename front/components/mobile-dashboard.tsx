"use client";
import { Button } from "@/components/ui/button";
import { useCurrency } from "@/components/currency-provider";
import React, { useEffect, useState } from "react";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { ShoppingBag, Heart, Home, Wallet, User, ChevronRight, Bell, Gift, Package, CheckCircle, House } from "lucide-react";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faStar, faBell, faChevronRight } from "@fortawesome/free-solid-svg-icons";
import { useCustomer } from "@/components/customer-provider";
import { Banner } from "@/components/banner";
import { startLoading, stopLoading } from "@/lib/loading";

interface MobileDashboardProps {
  onNavigate: (page: "dashboard" | "shop" | "wallet" | "account" | "orders", favorites?: boolean) => void;
  onOpenOrder?: (orderNumber: string) => void;
}

export function MobileDashboard({ onNavigate, onOpenOrder }: MobileDashboardProps) {
  const { symbol } = useCurrency();
  const { customer } = useCustomer();
  const wallet = Number(customer?.wallet_balance || 0);
  const [orders, setOrders] = useState<Array<{ order_number: string; ordered_at: string; payment_status: string; fulfillment_status: string; units: number; skus: number; total_paid: number; currency_symbol: string }>>([]);

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
            // Store only the orders array (no metadata like `at`)
            sessionStorage.setItem("orders_cache", JSON.stringify(json.orders));
          } catch { }
        }
      } catch (e) {
        // ignore
      } finally {
        if (typeof window !== "undefined") {
          stopLoading();
        }
        try {
          sessionStorage.removeItem("orders_needs_refresh");
        } catch { }
      }
    };

    // 1) If a refresh is requested (e.g., after checkout), fetch now
    let refreshed = false;
    try {
      const needs = sessionStorage.getItem("orders_needs_refresh");
      if (needs === "1") {
        refreshed = true;
        fetchOrders();
      }
    } catch { }

    // 2) Otherwise, use cache if present; if not, fetch once (first login)
    if (!refreshed) {
      try {
        const raw = sessionStorage.getItem("orders_cache");
        if (raw) {
          const parsed = JSON.parse(raw);
          if (Array.isArray(parsed)) {
            setOrders(parsed);
          } else if (Array.isArray(parsed?.orders)) {
            // Backward compatibility with older cache shape
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

    // 3) Listen for explicit refresh events (e.g., after checkout while dashboard is mounted)
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
        window.removeEventListener("orders_cache_updated", onOrdersCacheUpdated);
      }
    };
  }, []);

  // Wallet balance now comes from CustomerProvider

  return (
    <div className="min-h-screen w-full max-w-[1000px] mx-auto">
      {/* Header */}
      <header className="bg-white flex items-center justify-between inner-header-shadow">
        <div className="flex items-center">
          <div className="w-[66px] h-6  flex items-center justify-center">
            <FontAwesomeIcon icon={faGauge} className="text-green-600" style={{ width: "24px", height: "24px" }} />
          </div>
          <h1 className="font-semibold text-gray-900">Dashboard</h1>
        </div>
      </header>

      {/* Main Content */}
      <main className="pb-19">
        {/* Banner */}
        <div className="mt-0">
          <Banner />
        </div>

        <div className="quickLinksWrapper bg-gray-100 p-[10px] my-[10px]">
          {/* Referral Rewards */}
          <Card className="bg-green-500 border-0 text-white mb-[10px] rounded">
            <div className="p-[14px] flex items-center justify-between referralbox">
              <div>
                <h3 className="font-semibold text-lg">Referral Rewards</h3>
                <p className="text-sm mt-1 text-black font-semibold">Refer a Retailer to earn Rewards</p>
              </div>
              {/* <Gift className="w-8 h-8" /> */}
            </div>
          </Card>

          {/* Wallet Credit */}

          <Card onClick={() => onNavigate("wallet")} className="p-[12px] mb-[10px] hover:cursor-pointer">
            <div className="flex items-center justify-between h-[16px]">
              <div className="flex items-center gap-2">
                <FontAwesomeIcon icon={faWallet} className="text-green-600" style={{ width: "16px", height: "16px" }} />
                <span className="font-semibold text-sm h-[16px] leading-[16px]">
                  {symbol}
                  {wallet.toFixed(2)} credit in your wallet
                </span>
              </div>
              <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
            </div>
          </Card>
          {/* Action Buttons */}
          <div className="grid grid-cols-2 gap-[10px]">
            <Button onClick={() => onNavigate("shop")} className="bg-green-500 hover:cursor-pointer text-white rounded-sm h-[45px] gap-0">
              <FontAwesomeIcon icon={faShop} className="text-[#fff] mr-[8px] min-w-[20px] min-h-[16px] min-w-{20px} min-h-{16px}" style={{ width: "20px", height: "16px" }} />
              <span className="font-semibold text-[16px]">Shop</span>
            </Button>
            <Button onClick={() => onNavigate("shop", true)} className="gap-0 bg-green-500 hover:cursor-pointer text-white rounded-sm h-[45px]">
              <FontAwesomeIcon icon={faStar} className="text-[#fff] mr-[8px] min-w-[20px] min-h-[16px] min-w-{20px} min-h-{16px}" style={{ minWidth: "20px", minHeight: "16px" }} />
              <span className="font-semibold text-[16px]">Favourites</span>
            </Button>
          </div>
        </div>

        {/* Recent Notifications */}
        <h3 className="font-semibold text-gray-900 my-[10px] text-[14px] px-[10px] leading-[16px]">Recent Notifications</h3>
        <div className="my-[10px] px-[10px]">
          <Card className="h-[42px] mb-[10px] p-[12px] hover:cursor-pointer">
            <div className="h-[16px] flex items-center justify-between leading-[16px]">
              <div className="flex items-center leading-[16px]">
                {/* <div className="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center"> */}
                <FontAwesomeIcon icon={faBell} className="text-green-600" style={{ width: "14px", height: "16px" }} />
                {/* </div> */}
                <span className="text-sm font-semibold mx-[10px] text-[14px] leading-[16px]">You've left products in your basket</span>
              </div>
              <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
            </div>
          </Card>
          <Card className="h-[42px] mb-[10px] p-[12px] hover:cursor-pointer">
            <div className="h-[16px] flex items-center justify-between leading-[16px]">
              <div className="flex items-center leading-[16px]">
                {/* <div className="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center"> */}
                <FontAwesomeIcon icon={faBell} className="text-green-600" style={{ width: "14px", height: "16px" }} />
                {/* </div> */}
                <span className="text-sm font-semibold mx-[10px] text-[14px] leading-[16px]">Lost Mary Nera 30K ONE DAY PROMOTION!</span>
              </div>
              <FontAwesomeIcon icon={faChevronRight} className="text-green-600" style={{ width: "12.5px", height: "21px" }} />
            </div>
          </Card>
        </div>

        <hr className="m-[10px]"></hr>

        {/* Recent Orders */}
        {orders.length > 0 && (
          <div>
            <h3 className="font-semibold text-gray-900 my-[10px] px-[10px] leading-[16px] text-[14px]">Recent Orders</h3>

            {orders.map((o, idx) => (
              <div key={o.order_number + idx} className="my-[10px] px-[10px]">
                <Card className="mb-[10px] py-[12px] pl-[12px] border-gray-300">
                  <div className="flex">
                    <div className="space-y-2 text-sm w-full pr-3 border-r border-gray-300">
                      <div className="flex justify-between mb-0">
                        <span className="text-black">Order No:</span>
                        <span className="text-black">{o.order_number}</span>
                      </div>
                      <div className="flex justify-between mb-0">
                        <span className="text-black">Ordered:</span>
                        <span className="text-black">{o.ordered_at}</span>
                      </div>
                      <div className="flex justify-between mb-0">
                        <span className="text-black">Payment Status:</span>
                        <span className="text-black">{o.payment_status}</span>
                      </div>
                      <div className="flex justify-between mb-0">
                        <span className="text-black">Fulfillment Status:</span>
                        <div className="flex items-center gap-1">
                          <span className="text-black">{o.fulfillment_status}</span>
                        </div>
                      </div>
                      <div className="flex justify-between mb-0">
                        <span className="text-black">Units:</span>
                        <span className="text-black">{o.units}</span>
                      </div>
                      <div className="flex justify-between mb-0">
                        <span className="text-black">SKUs:</span>
                        <span className="text-black">{o.skus}</span>
                      </div>
                      <div className="flex justify-between font-semibold">
                        <span className="text-black">Total Paid:</span>
                        <span className="text-black">
                          {o.currency_symbol}
                          {o.total_paid.toFixed(2)}
                        </span>
                      </div>
                    </div>
                    <ChevronRight onClick={() => onOpenOrder && onOpenOrder(o.order_number)} className="w-8 h-8 text-green-600 self-center ml-1 cursor-pointer" />
                  </div>
                </Card>
              </div>

            ))}
            {/** View All Orders button if more than 10 orders exist (API exposes has_more) **/}
            <div className="mt-2">  
              <Button
                onClick={async () => {
                  try {
                    // const res = await api.get('/orders')
                    // if (res?.data?.has_more) {
                    onNavigate("orders");
                    // }
                  } catch {
                    // ignore
                  }
                }}
                className="h-[42px] w-full border border-green-600  bg-white text-black hover:cursor-pointer text-sm font-semibold"
              >
                View All Orders
              </Button>
            </div>

          </div>
        )}
      </main>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 px-[18px]">
        <div className="flex flex-row items-center justify-between h-[72px] footer-nav-col">
          <button className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faGauge} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop", false)} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
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
