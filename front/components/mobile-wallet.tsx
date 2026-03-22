"use client";

import { useCurrency } from "@/components/currency-provider";
import { ChevronLeft, Info, Calendar } from "lucide-react";
import '../app/globals.css';
import { useCustomer } from "@/components/customer-provider";
import { Banner } from "@/components/banner";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChartSimple, faShop, faUser, faWallet, faHeart } from "@fortawesome/free-solid-svg-icons";

import { useEffect, useState } from "react";

interface ProductItem {
  id: number;
  name: string;
  image: string;
  price: string;
  discount?: string;
}

interface MobileWalletProps {
  onNavigate: (page: any, favorites?: boolean) => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  increment: (product: ProductItem) => void;
  decrement: (product: ProductItem) => void;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  clearCart: () => void;
}

const CoinStackIcon = () => (
  <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M20 29C26.6274 29 32 27.2091 32 25C32 22.7909 26.6274 21 20 21C13.3726 21 8 22.7909 8 25C8 27.2091 13.3726 29 20 29Z" fill="#FCA5A5" />
    <path d="M20 29C26.6274 29 32 27.2091 32 25V26.5C32 28.7091 26.6274 30.5 20 30.5C13.3726 30.5 8 28.7091 8 26.5V25C8 27.2091 13.3726 29 20 29Z" fill="#EF4444" />
    <path d="M20 25C26.6274 25 32 23.2091 32 21C32 18.7909 26.6274 17 20 17C13.3726 17 8 18.7909 8 21C8 23.2091 13.3726 25 20 25Z" fill="#FDBA74" />
    <path d="M20 25C26.6274 25 32 23.2091 32 21V22.5C32 24.7091 26.6274 26.5 20 26.5C13.3726 26.5 8 24.7091 8 22.5V21C8 23.2091 13.3726 25 20 25Z" fill="#F97316" />
    <path d="M20 21C26.6274 21 32 19.2091 32 17C32 14.7909 26.6274 13 20 13C13.3726 13 8 14.7909 8 17C8 19.2091 13.3726 21 20 21Z" fill="#FCD34D" />
    <path d="M20 21C26.6274 21 32 19.2091 32 17V18.5C32 20.7091 26.6274 22.5 20 22.5C13.3726 22.5 8 20.7091 8 18.5V17C8 19.2091 13.3726 21 20 21Z" fill="#F59E0B" />
  </svg>
);

const TransactionItem = ({ type, amount, order, total, date, symbol }: { type: 'used' | 'earned', amount: string, order: string, total: string, date: string, symbol: string }) => {
  return (
    <div className="bg-white rounded-[6px] p-3 flex gap-3 shadow-sm border border-[#F3F4F9]">
      <div className="flex-shrink-0 flex items-center justify-center">
        <CoinStackIcon />
      </div>
      <div className="flex-1 flex flex-col justify-center">
        <div className="font-bold text-[#4E5667] text-[14px] leading-tight mb-[4px]">
          {type === 'used' ? '- ' : '+ '}{symbol}{amount} - Credit {type === 'used' ? 'Used' : 'Earned'}
        </div>
        <div className="text-[12px] text-[#8F98AD] flex items-center tracking-tight">
          <span>Order: {order}</span>
          <span className="px-1.5">•</span>
          <span>Total {symbol}{total}</span>
          <span className="px-1.5">•</span>
          <Calendar className="w-3.5 h-3.5 mr-1" />
          <span>{date}</span>
        </div>
      </div>
    </div>
  );
};

export function MobileWallet({ onNavigate }: MobileWalletProps) {
  const { symbol } = useCurrency();
  const { customer } = useCustomer();
  const wallet = Number(customer?.wallet_balance || 0);
  const [showIntro, setShowIntro] = useState(true);

  if (showIntro) {
    return (
      <div className="min-h-screen flex items-center justify-center p-6">
        <div className="bg-[#F8F7FC] w-full max-w-[402px] rounded-lg shadow-2xl overflow-hidden flex flex-col">
          <div className="p-8 pb-4 space-y-6">
            <div className="space-y-1">
              <h2 className="text-[#4E5667] font-bold text-[17px]">What is the Wallet?</h2>
              <p className="text-[#8F98AD] text-[13px] leading-relaxed font-medium">
                Your wallet stores the credit you have earned from previous orders placed through this platform.
              </p>
            </div>

            <div className="space-y-1">
              <h2 className="text-[#4E5667] font-bold text-[17px]">How is wallet credit earned?</h2>
              <p className="text-[#8F98AD] text-[13px] leading-relaxed font-medium">
                Each product displays a wallet credit value, showing how much credit will be added to your wallet for every unit purchased. You can also earn additional credit by referring other retailers to the platform.
              </p>
            </div>

            <div className="space-y-1">
              <h2 className="text-[#4E5667] font-bold text-[17px]">How do I use wallet credit?</h2>
              <p className="text-[#8F98AD] text-[13px] leading-relaxed font-medium">
                Any credit in your wallet is automatically deducted from the total of your next order when you check out.
              </p>
            </div>

            <div className="space-y-1">
              <h2 className="text-[#4E5667] font-bold text-[17px]">Can I earn credit without ordering through the platform?</h2>
              <p className="text-[#8F98AD] text-[13px] leading-relaxed font-medium">
                No. Wallet credit is only awarded when purchases are made directly through this platform.
              </p>
            </div>
          </div>

          <button
            onClick={() => setShowIntro(false)}
            className="w-full bg-[#5294E2] text-white py-4 text-[18px] font-bold hover:bg-[#4A90E5] transition-colors mt-4"
          >
            Ok, Got It
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex flex-col w-full max-w-[402px] mx-auto bg-[#F8F7FC]">
      {/* Header */}
      <div className="h-[60px] flex items-center px-4 bg-[#F8F7FC] sticky top-0 z-50">
        <button onClick={() => onNavigate("dashboard")} className="flex items-center text-[#8F98AD] hover:text-[#4E5667] transition-colors absolute left-4">
          <ChevronLeft className="w-5 h-5 mr-1" />
          <span className="text-[15px]">Back</span>
        </button>
        <h1 className="text-[18px] font-bold text-[#4E5667] mx-auto">Wallet</h1>
      </div>

      <div className="flex-1 overflow-y-auto pb-[90px]">
        {/* Banner */}
        <div className="px-4">
          <Banner />
        </div>

        {/* Blue Card */}
        <div className="mx-4 mt-4 bg-[#4A90E5] rounded-[24px] px-6 py-6 text-white text-center shadow-[0_4px_10_px_-4px_#4A90E5]">
          <h2 className="text-[26px] font-bold tracking-tight">Wallet Balance</h2>
          <div className="text-[17px] text-white/90 font-medium mb-2">Available to use</div>
          <div className="flex items-center justify-center gap-2 mb-2">
            <span className="text-[44px] font-bold leading-none">{symbol}{wallet.toFixed(2)}</span>
            <Info className="w-6 h-6 text-white" />
          </div>
          <div className="text-[12px] text-white/90 font-medium">
            Total balance inc. pending: {symbol}0.00
          </div>
        </div>

        {/* Transactions List */}
        <div className="px-3 mt-6">
          <h3 className="text-[15px] font-bold text-[#4E5667] mb-3 ml-1">March</h3>
          <div className="space-y-2 mb-5">
            <TransactionItem type="used" amount="18.60" order="4789403" total="112.20" date="01/02/2026" symbol={symbol} />
            <TransactionItem type="earned" amount="18.60" order="4789403" total="112.20" date="01/02/2026" symbol={symbol} />
            <TransactionItem type="earned" amount="18.60" order="4789403" total="112.20" date="01/02/2026" symbol={symbol} />
          </div>

          <h3 className="text-[15px] font-bold text-[#4E5667] mb-3 ml-1">February</h3>
          <div className="space-y-2">
            <TransactionItem type="used" amount="18.60" order="4789403" total="112.20" date="01/02/2026" symbol={symbol} />
            <TransactionItem type="earned" amount="18.60" order="4789403" total="112.20" date="01/02/2026" symbol={symbol} />
          </div>
        </div>
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[402px] z-50 shadow-[0px_-1px_8px_0px_#555E5814]">
        <div className="h-[74px] px-2 pt-[8px] pb-[10px] grid grid-cols-5 items-center bg-[#F1F2F7] border-t border-[#E4E7F0]">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-medium leading-none">
            <FontAwesomeIcon icon={faChartSimple} className="text-[23px]" />
            <span>Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop", false)} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-medium leading-none">
            <FontAwesomeIcon icon={faShop} className="text-[23px]" />
            <span>Shop</span>
          </button>
          <button onClick={() => onNavigate("shop", true)} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-medium leading-none">
            <FontAwesomeIcon icon={faHeart} className="text-[23px]" />
            <span>Favourites</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center gap-[4px] text-[#4A90E5] text-[11px] font-medium leading-none">
            <FontAwesomeIcon icon={faWallet} className="text-[23px]" />
            <span>Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-medium leading-none">
            <FontAwesomeIcon icon={faUser} className="text-[23px]" />
            <span>Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}

