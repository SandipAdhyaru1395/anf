"use client";

import { Card } from "@/components/ui/card";
import { useCurrency } from "@/components/currency-provider";
import { Minus, Plus, Home, ShoppingBag, User, Wallet } from "lucide-react";
import '../app/globals.css';
import { useEffect, useState } from "react";
import api from "@/lib/axios";
import { useCustomer } from "@/components/customer-provider";
import { Banner } from "@/components/banner";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faUser, faWallet } from "@fortawesome/free-solid-svg-icons";

interface ProductItem {
  id: number;
  name: string;
  image: string;
  price: string;
  discount?: string;
}

interface MobileWalletProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account") => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  increment: (product: ProductItem) => void;
  decrement: (product: ProductItem) => void;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  clearCart: () => void;
}

export function MobileWallet({ onNavigate }: MobileWalletProps) {
  const { symbol } = useCurrency();
  const { customer } = useCustomer();
  const wallet = Number(customer?.wallet_balance || 0);
  return (
    <div className="w-full max-w-[1000px] mx-auto bg-gray-50 min-h-screen">
      {/* Header */}
      <div className="h-[50px] bg-white flex items-center">
        <div className="w-[66px] h-8 rounded-full flex items-center justify-center">
          <FontAwesomeIcon icon={faWallet} className="text-green-600" style={{ width: "30px", height: "24px" }} />
        </div>
        <h1 className="text-lg font-semibold text-black-600 text-[16px]">Wallet</h1>
      </div>

      {/* Banner */}
      <Banner />

      {/* Wallet Content */}
      <div className="p-[10px] pb-[82px] space-y-6">
        {/* Wallet Balance */}

          <h2 className="leading-[16px] text-base font-semibold text-gray-900 mb-0">Your wallet balance</h2>
          <Card className="mt-[8px] p-[13px] border border-green-600 max-h-[53px] leading-[12px] mb-0">
            <div className="flex items-center justify-center font-proxima font-[800] text-[24px]">
              <FontAwesomeIcon icon={faWallet} className="text-green-600" style={{ width: "30px", height: "24px" }} />
              <span className="text-[24px] mx-[4px]  ">
                {symbol}
                {wallet.toFixed(2)}
              </span>
              <span className="text-green-600">Credit</span>
            </div>
          </Card>
          <hr className="mt-[20px] mb-[20px]"></hr>
        {/* FAQ Sections */}
        <div className="space-y-4">
          <div>
            <h3 className="text-sm text-[16px] font-semibold text-gray-900 leading-[16px]">What is the wallet?</h3>
            <p className="text-[16px] leading-[16px] mt-[8px] mb-[20px]">The wallet contains credit you acquired from your previous purchases on this platform.</p>
          </div>

          <div>
            <h3 className="text-sm text-[16px] font-semibold text-gray-900 leading-[16px]">How much credit do I get?</h3>
            <p className="text-[16px] leading-[16px] mt-[8px] mb-[20px]">Every product has a wallet indicator which states how much credit you will be awarded for every unit of that product purchased.</p>
          </div>

          <div>
            <h3 className="text-sm text-[16px] font-semibold text-gray-900 leading-[16px]">How do I use my credit?</h3>
            <p className="text-[16px] leading-[16px] mt-[8px] mb-[20px]">Your wallet credit will be automatically applied on your next purchase as a discount from the order total.</p>
          </div>

          <div>
            <h3 className="text-sm text-[16px] font-semibold text-gray-900 leading-[16px]">Do I get credit if I don't use the platform?</h3>
            <p className="text-[16px] leading-[16px] mt-[8px] mb-[20px]">No. Credit is only added to your wallet when you purchase through this platform.</p>
          </div>
        </div>
      </div>

      {/* Bottom Navigation */}
      {/* <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t footer-nav">
        <div className="grid grid-cols-4 py-3 footer-nav-col">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center text-gray-400 hover:cursor-pointer">
            <Home className="w-7 h-7 mb-1" />
            <span className="text-xs">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center text-gray-400 hover:cursor-pointer">
            <ShoppingBag className="w-7 h-7 mb-1" />
            <span className="text-xs">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center text-green-600 hover:cursor-pointer">
            <Wallet className="w-7 h-7 mb-1" />
            <span className="text-xs">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center text-gray-400 hover:cursor-pointer">
            <User className="w-7 h-7 mb-1" />
            <span className="text-xs">Account</span>
          </button>
        </div>
      </nav> */}

      {/* Bottom Navigation */}
            <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 px-[18px]">
              <div className="flex flex-row items-center justify-between h-[72px] footer-nav-col">
                <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
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
