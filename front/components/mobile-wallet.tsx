"use client";

import { useCurrency } from "@/components/currency-provider";
import { Info, Calendar } from "lucide-react";
import { useCustomer } from "@/components/customer-provider";
import { useSettings } from "@/components/settings-provider";
import { resolveBackendAssetUrl } from "@/lib/utils";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChartSimple, faShop, faUser, faWallet, faHeart } from "@fortawesome/free-solid-svg-icons";
import { Banner } from "@/components/banner";
import api from "@/lib/axios";

import { useEffect, useState } from "react";
import { MobilePageHeader } from "@/components/mobile-page-header";

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

/** Figma coin asset ~26.14×32.38 — scales inside fixed box */
const CoinStackIcon = () => (
  <svg className="h-full w-full" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
    <path d="M20 29C26.6274 29 32 27.2091 32 25C32 22.7909 26.6274 21 20 21C13.3726 21 8 22.7909 8 25C8 27.2091 13.3726 29 20 29Z" fill="#FCA5A5" />
    <path d="M20 29C26.6274 29 32 27.2091 32 25V26.5C32 28.7091 26.6274 30.5 20 30.5C13.3726 30.5 8 28.7091 8 26.5V25C8 27.2091 13.3726 29 20 29Z" fill="#EF4444" />
    <path d="M20 25C26.6274 25 32 23.2091 32 21C32 18.7909 26.6274 17 20 17C13.3726 17 8 18.7909 8 21C8 23.2091 13.3726 25 20 25Z" fill="#FDBA74" />
    <path d="M20 25C26.6274 25 32 23.2091 32 21V22.5C32 24.7091 26.6274 26.5 20 26.5C13.3726 26.5 8 24.7091 8 22.5V21C8 23.2091 13.3726 25 20 25Z" fill="#F97316" />
    <path d="M20 21C26.6274 21 32 19.2091 32 17C32 14.7909 26.6274 13 20 13C13.3726 13 8 14.7909 8 17C8 19.2091 13.3726 21 20 21Z" fill="#FCD34D" />
    <path d="M20 21C26.6274 21 32 19.2091 32 17V18.5C32 20.7091 26.6274 22.5 20 22.5C13.3726 22.5 8 20.7091 8 18.5V17C8 19.2091 13.3726 21 20 21Z" fill="#F59E0B" />
  </svg>
);

/** Figma "Wallet Transaction Item": row flex, p 16, gap 16; list uses divide-y #E2E2E2, radius 5 */
const TransactionItem = ({ type, amount, order, total, date, symbol }: { type: "used" | "earned"; amount: string; order: string; total: string; date: string; symbol: string }) => {
  return (
    <div className="flex w-full gap-4 p-4">
      <div className="flex h-[32px] w-[26px] shrink-0 items-center justify-center self-center">
        <CoinStackIcon />
      </div>
      <div className="flex min-w-0 flex-1 flex-col justify-center gap-[6px]">
        <p className="text-[13px] font-semibold leading-none tracking-normal text-[#111827]">
          {type === "used" ? "- " : "+ "}
          {symbol}
          {amount} - Credit {type === "used" ? "Used" : "Earned"}
        </p>
        <div className="flex flex-wrap items-center gap-2 text-[12px] font-normal leading-none text-[#6B7280]">
          <span>Order: {order}</span>
          <span className="text-[#D1D5DB]" aria-hidden>
            •
          </span>
          <span>
            Total {symbol}
            {total}
          </span>
          <span className="text-[#D1D5DB]" aria-hidden>
            •
          </span>
          <span className="inline-flex items-center gap-2">
            <Calendar className="h-3 w-3 shrink-0 opacity-90" strokeWidth={2} aria-hidden />
            {date}
          </span>
        </div>
      </div>
    </div>
  );
};

type WalletTransactionRow = {
  id: number;
  order_id: number | null;
  order_number: string | null;
  order_total_amount: number | null;
  amount: number;
  type: string;
  created_at?: string | null;
};

export function MobileWallet({ onNavigate }: MobileWalletProps) {
  const { symbol } = useCurrency();
  const { customer } = useCustomer();
  const { settings } = useSettings();
  const bannerSrc =
    resolveBackendAssetUrl(settings?.banner) ?? settings?.banner ?? null;
  const wallet = Number(customer?.wallet_balance || 0);
  /** Show wallet explainer only when user taps info icon. */
  const [showIntro, setShowIntro] = useState(false);
  const [transactions, setTransactions] = useState<WalletTransactionRow[]>([]);
  const [loadingTransactions, setLoadingTransactions] = useState(false);

  useEffect(() => {
    let active = true;
    const loadTransactions = async () => {
      setLoadingTransactions(true);
      try {
        const res = await api.get("/wallet-transactions");
        const rows = Array.isArray(res?.data?.transactions) ? res.data.transactions : [];
        if (!active) return;
        setTransactions(rows);
      } catch {
        if (!active) return;
        setTransactions([]);
      } finally {
        if (active) setLoadingTransactions(false);
      }
    };
    loadTransactions();
    return () => {
      active = false;
    };
  }, []);

  const groupedTransactions = (() => {
    const monthFmt = new Intl.DateTimeFormat("en-GB", { month: "long", year: "numeric" });
    const dateFmt = new Intl.DateTimeFormat("en-GB", { day: "2-digit", month: "2-digit", year: "numeric" });
    const groups: Record<string, WalletTransactionRow[]> = {};
    const orderedKeys: string[] = [];

    for (const tx of transactions) {
      const dt = tx.created_at ? new Date(tx.created_at) : null;
      const safeDate = dt && !isNaN(dt.getTime()) ? dt : new Date();
      const key = monthFmt.format(safeDate);
      if (!groups[key]) {
        groups[key] = [];
        orderedKeys.push(key);
      }
      groups[key].push(tx);
    }

    return { groups, orderedKeys, dateFmt };
  })();

  if (showIntro) {
    return (
      <div className="fixed inset-0 z-50 flex items-center justify-center bg-[#C9C9C9] px-4 py-6">
        <div className="flex w-full max-w-[402px] flex-col overflow-hidden bg-[#FFFFFF]">
          <div className="p-8 pb-4 space-y-6">
            <div className="space-y-1">
              <h2 className="text-[#3D495E] font-bold text-[13px]">What is the Wallet?</h2>
              <p className="text-[#8F98AD] text-[11px] leading-relaxed font-medium">
                Your wallet stores the credit you have earned from previous orders placed through this platform.
              </p>
            </div>

            <div className="space-y-1">
              <h2 className="text-[#3D495E] font-bold text-[13px]">How is wallet credit earned?</h2>
              <p className="text-[#8F98AD] text-[11px] leading-relaxed font-medium">
                Each product displays a wallet credit value, showing how much credit will be added to your wallet for every unit purchased. You can also earn additional credit by referring other retailers to the platform.
              </p>
            </div>

            <div className="space-y-1">
              <h2 className="text-[#3D495E] font-bold text-[13px]">How do I use wallet credit?</h2>
              <p className="text-[#8F98AD] text-[11px] leading-relaxed font-medium">
                Any credit in your wallet is automatically deducted from the total of your next order when you check out.
              </p>
            </div>

            <div className="space-y-1">
              <h2 className="text-[#3D495E] font-bold text-[13px]">Can I earn credit without ordering through the platform?</h2>
              <p className="text-[#8F98AD] text-[11px] leading-relaxed font-medium">
                No. Wallet credit is only awarded when purchases are made directly through this platform.
              </p>
            </div>
          </div>

          <button
            type="button"
            onClick={() => {
              setShowIntro(false);
            }}
            className="mt-4 w-full cursor-pointer bg-[#4A90E5] py-4 text-[18px] font-bold text-white transition-colors hover:bg-[#3d7fd4]"
          >
            Ok, Got It
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="relative mx-auto flex h-[100dvh] min-h-0 w-full max-w-[402px] flex-col bg-[#FAFBFD]" style={{ fontFamily: "Roboto, system-ui, sans-serif" }}>
      <MobilePageHeader title="Wallet" onBack={() => onNavigate("dashboard")} noTopPadding />

            <div className="shrink-0 bg-[#FAFBFD] px-4 pb-2 pt-2">
              <Banner className="mx-auto h-[93px] w-full max-w-[370px] !rounded-[2px]" />
            </div>

      {/* Scroll: Figma padding 16 / bottom ~60 + nav; gap 16 between blocks */}
      <main className="scrollbar-hide min-h-0 w-full flex-1 overflow-x-hidden overflow-y-auto bg-[#FAFBFD] px-4 pb-[72px] pt-4">
        <div className="mx-auto flex w-full max-w-[370px] flex-col gap-4">
          {/* Figma typography: title 33/700/3%, "Available" 21/500/3%, amount 33/900/3%; grad + r25 + py22 */}
          <div
            className={`box-border flex w-full flex-col rounded-[25px] py-[22px] text-center text-white shadow-[0_8px_24px_rgba(40,104,192,0.28)] ${!bannerSrc ? "mt-0" : ""}`}
            style={{
              fontFamily: "Roboto, system-ui, sans-serif",
              background: "linear-gradient(180deg, #2868C0 0%, #4C92E9 100%)",
            }}
          >
            <div className="mx-auto flex w-full max-w-[370px] flex-col items-center gap-[3px] px-6">
              <h2 className="text-[33px] font-bold leading-none tracking-[0.03em] text-white">Wallet Balance</h2>
              <p className="text-[21px] font-medium leading-none tracking-[0.03em] text-white">Available to use</p>
              <div className="mt-1 flex items-center justify-center gap-2">
                <span className="text-[33px] font-black leading-none tracking-[0.03em] text-white">
                  {symbol}
                  {wallet.toFixed(2)}
                </span>
                <button
                  type="button"
                  onClick={() => setShowIntro(true)}
                  className="flex h-8 w-8 shrink-0 items-center justify-center self-center rounded-full border border-white/75 bg-white/10 transition-opacity hover:opacity-90"
                  aria-label="Wallet balance information"
                >
                  <Info className="h-4 w-4 text-white" strokeWidth={2.5} />
                </button>
              </div>
            </div>
            <p className="mx-auto mt-3 max-w-[370px] shrink-0 px-6 text-[12px] font-normal leading-none tracking-[0.02em] text-white/95">
              Total balance inc. pending: {symbol}0.00
            </p>
          </div>

          {/* Figma: month block gap 8 (title→list); 16px between month groups */}
          <div className="flex w-full flex-col gap-4">
            {loadingTransactions ? (
              <div className="rounded-[5px] bg-white px-4 py-5 text-center text-[13px] text-[#6B7280] shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                Loading transactions...
              </div>
            ) : groupedTransactions.orderedKeys.length === 0 ? (
              <div className="rounded-[5px] bg-white px-4 py-5 text-center text-[13px] text-[#6B7280] shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                No wallet transactions yet.
              </div>
            ) : (
              groupedTransactions.orderedKeys.map((monthKey) => (
                <section key={monthKey} className="flex w-full flex-col gap-2">
                  <h3 className="text-left text-[15px] font-semibold capitalize leading-none tracking-[0.03em] text-[#3D495E]">
                    {monthKey}
                  </h3>
                  <div className="w-full overflow-hidden rounded-[5px] bg-white divide-y divide-[#E2E2E2] shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    {groupedTransactions.groups[monthKey].map((tx) => {
                      const type: "used" | "earned" = String(tx.type).toLowerCase() === "debit" ? "used" : "earned";
                      const dt = tx.created_at ? new Date(tx.created_at) : null;
                      const dateText =
                        dt && !isNaN(dt.getTime()) ? groupedTransactions.dateFmt.format(dt) : "—";
                      return (
                        <TransactionItem
                          key={tx.id}
                          type={type}
                          amount={Math.abs(Number(tx.amount || 0)).toFixed(2)}
                          order={tx.order_number || "—"}
                          total={Number(tx.order_total_amount || 0).toFixed(2)}
                          date={dateText}
                          symbol={symbol}
                        />
                      );
                    })}
                  </div>
                </section>
              ))
            )}
          </div>
        </div>
      </main>

      <nav
        className="fixed bottom-0 left-1/2 z-50 box-border flex h-[64px] w-full max-w-[402px] -translate-x-1/2 flex-col rounded-t-[10px] bg-white px-[43px] pb-4 pt-2 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)]"
        aria-label="Main navigation"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <div className="flex min-h-0 w-full flex-1 items-center justify-center">
          <div className="flex h-[40px] w-[316px] max-w-full items-center justify-between">
            <button type="button" onClick={() => onNavigate("dashboard")} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
              <FontAwesomeIcon icon={faChartSimple} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Dashboard</span>
            </button>
            <button type="button" onClick={() => onNavigate("shop", false)} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
              <FontAwesomeIcon icon={faShop} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Shop</span>
            </button>
            <button type="button" onClick={() => onNavigate("shop", true)} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
              <FontAwesomeIcon icon={faHeart} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Favourites</span>
            </button>
            <button type="button" onClick={() => onNavigate("wallet")} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 text-[#4A90E5]" aria-current="page">
              <FontAwesomeIcon icon={faWallet} className="h-[23px] w-[23px] shrink-0 text-[23px] leading-none text-[#4A90E5]" aria-hidden />
              <span className="inline-flex h-[13px] min-w-[36px] items-center justify-center text-center text-[11px] font-bold leading-none tracking-normal">Wallet</span>
            </button>
            <button type="button" onClick={() => onNavigate("account")} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
              <FontAwesomeIcon icon={faUser} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Account</span>
            </button>
          </div>
        </div>
      </nav>
    </div>
  );
}

