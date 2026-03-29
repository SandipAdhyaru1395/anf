"use client";

import { 
  ChevronLeft, 
  ChevronRight, 
  ChevronDown, 
  ChevronUp, 
  Home as HomeIcon, 
  Package as PackageIcon, 
  Check, 
  Star,
  User,
  Wallet,
  ShoppingBag,
  ArrowLeft
} from "lucide-react";
import { Card } from "@/components/ui/card";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { 
  faGauge, 
  faShop, 
  faWallet, 
  faUser, 
  faBars, 
  faTruck, 
  faChevronUp, 
  faChevronDown, 
  faCheck,
  faChartSimple,
  faHeart 
} from "@fortawesome/free-solid-svg-icons";
import { useEffect, useMemo, useState } from "react";
import { Banner } from "@/components/banner";
import { useToast } from "@/hooks/use-toast";
import { useCustomer } from "@/components/customer-provider";
import FloatingInput from "./ui/floating-input";
import { useCurrency } from "@/components/currency-provider";
import { useSettings } from "@/components/settings-provider";

interface Branch {
  id: number;
  name: string;
  address_line1: string;
  address_line2: string;
  city: string;
  zip_code: string;
  country: string;
}

interface ProductItem {
  id: number;
  name: string;
  image: string;
  price: string;
  discount?: string;
  step_quantity?: number;
  wallet_credit?: number;
  vat_amount?: number;
}

interface DeliveryMethod {
  id: number;
  name: string;
  time: string;
  rate: number;
  status: string;
}

interface BankOption {
  bank_id: string;
  name: string;
  friendly_name: string;
  logo?: string;
  icon?: string;
}

interface MobileCheckoutProps {
  onNavigate: (page: any, favorites?: boolean) => void;
  onBack: () => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  clearCart: () => void;
  /** Called after a successful non-redirect checkout (e.g. pay later) with the new order number. */
  onCheckoutSuccess?: (orderNumber: string) => void;
}

export function MobileCheckout({ onNavigate, onBack, cart, totals, clearCart, onCheckoutSuccess }: MobileCheckoutProps) {
  const [isProcessing, setIsProcessing] = useState(false);
  const [paymentMode, setPaymentMode] = useState<"gateway" | "gateway_bank" | "pay_later" | null>("gateway");
  const [banks, setBanks] = useState<BankOption[]>([]);
  const [selectedBankId, setSelectedBankId] = useState<string>("");
  const [banksLoading, setBanksLoading] = useState(false);
  const [isDispatchExpanded, setIsDispatchExpanded] = useState(false);
  const [isBillingExpanded, setIsBillingExpanded] = useState(false);
  const [isDeliveryExpanded, setIsDeliveryExpanded] = useState(false);
  const [branches, setBranches] = useState<Branch[]>([]);
  const [selectedShippingBranch, setSelectedShippingBranch] = useState<Branch | null>(null);
  const [selectedBillingBranch, setSelectedBillingBranch] = useState<Branch | null>(null);
  const [deliveryInstructions, setDeliveryInstructions] = useState("");
  const [poNumber, setPoNumber] = useState("");
  const [deliveryMethods, setDeliveryMethods] = useState<DeliveryMethod[]>([]);
  const [selectedDeliveryMethod, setSelectedDeliveryMethod] = useState<DeliveryMethod | null>(null);
  const { toast } = useToast();
  const { refresh, customer } = useCustomer();
  const { format, symbol } = useCurrency();
  const { settings } = useSettings();
  const [gatewayUnavailable, setGatewayUnavailable] = useState<boolean>(false);

  // Backend-driven cart snapshot for checkout
  const [items, setItems] = useState<Array<{ product: ProductItem; quantity: number }>>([]);
  const [cartTotals, setCartTotals] = useState<{ units: number; skus: number; subtotal: number; totalDiscount: number; total: number }>({ units: 0, skus: 0, subtotal: 0, totalDiscount: 0, total: 0 });
  const [walletCreditTotal, setWalletCreditTotal] = useState<number>(0);

  // Fetch branches on component mount
  const fetchBranches = async () => {
    try {
      const response = await api.get("/branches");
      if (response.data.success) {
        setBranches(response.data.branches);
        // Select the first branch (ignore is_default for checkout selection)
        if (Array.isArray(response.data.branches) && response.data.branches.length > 0) {
          setSelectedShippingBranch(response.data.branches[0]);
          setSelectedBillingBranch(response.data.branches[0]);
        } else {
          setSelectedShippingBranch(null);
          setSelectedBillingBranch(null);
        }
      }
    } catch (error) {
      console.error("Error fetching branches:", error);
    }
  };

  // Fetch DNA Pay by Bank list when gateway_bank is selected
  const fetchBanks = async () => {
    setBanksLoading(true);
    try {
      const resp = await api.get("/banks");
      if (resp.data?.success && Array.isArray(resp.data.banks)) {
        setBanks(resp.data.banks);
        if (resp.data.banks.length > 0 && !selectedBankId) {
          setSelectedBankId(String(resp.data.banks[0].bank_id ?? ""));
        }
      } else {
        setBanks([]);
      }
    } catch {
      setBanks([]);
    } finally {
      setBanksLoading(false);
    }
  };

  // Fetch delivery methods on component mount
  const fetchDeliveryMethods = async () => {
    try {
      const resp = await api.get("/delivery-methods");
      if (resp.data.success && Array.isArray(resp.data.delivery_methods) && resp.data.delivery_methods.length > 0) {
        setDeliveryMethods(resp.data.delivery_methods);
        // Do NOT setSelectedDeliveryMethod here: let the useEffect on deliveryMethods handle default selection!
      } else {
        setDeliveryMethods([]);
        setSelectedDeliveryMethod(null);
      }
    } catch (err) {
      setDeliveryMethods([]);
    }
  };
  // Calculate total wallet credit from cart items (Credit Awarded)
  const totalWalletCredit = useMemo(() => walletCreditTotal, [walletCreditTotal]);

  // Calculate wallet discount (assuming 10% of subtotal as example)
  const walletDiscount = customer?.wallet_balance ? Math.min(customer.wallet_balance, cartTotals.subtotal) : 0;

  // Calculate total VAT amount from cart for all products
  const totalVatAmount = useMemo(() =>
    items.reduce((sum, { product, quantity }) => {
      const vat = Number(product.vat_amount ?? 0);
      return sum + (isNaN(vat) ? 0 : vat) * quantity;

    }, 0), [items]);

  // Always show summary even if no delivery method exists; use 0 as default
  const showSummary = typeof cartTotals?.subtotal === 'number';

  // All summary values always from very latest state, guaranteed numbers
  const deliveryRate = Number(selectedDeliveryMethod?.rate) || 0;
  const subtotal = Number(cartTotals?.subtotal) || 0;
  const discount = Number(walletDiscount) || 0;
  const vat = Number(totalVatAmount) || 0;
  const paymentTotal = subtotal - discount + deliveryRate + vat;

  // Ensure selectedDeliveryMethod is only set after deliveryMethods are loaded, and not on every re-render
  useEffect(() => {
    fetchBranches();
    fetchDeliveryMethods();
    // Load latest cart snapshot from backend
    const loadCart = async () => {
      try {
        const res = await api.get('/cart');
        const apiItems: Array<{ product_id: number; quantity: number; product?: any }> = res?.data?.cart?.items || [];
        const mapped = apiItems
          .filter((it) => it?.product)
          .map((it) => ({
            product: {
              id: Number(it.product.id),
              name: it.product.name,
              image: it.product.image,
              price: String(it.product.price),
              wallet_credit: Number(it.product.wallet_credit ?? 0),
              vat_amount: Number(it.product.vat_amount ?? 0),
            } as ProductItem,
            quantity: Number(it.quantity) || 0,
          }));
        setItems(mapped);
        const c = res?.data?.cart;
        setCartTotals({
          units: Number(c?.units || 0),
          skus: Number(c?.skus || 0),
          subtotal: Number(c?.subtotal || 0),
          totalDiscount: Number(c?.total_discount || 0),
          total: Number(c?.total || 0),
        });
        setWalletCreditTotal(Number(c?.wallet_credit_total ?? 0));
      } catch {}
    };
    loadCart();
    // Also refresh cart snapshot if products are updated (credit/price may change)
    const onProductsCacheUpdated = () => { loadCart(); };
    if (typeof window !== 'undefined') {
      window.addEventListener('products_cache_updated', onProductsCacheUpdated);
    }
    return () => {
      if (typeof window !== 'undefined') {
        window.removeEventListener('products_cache_updated', onProductsCacheUpdated);
      }
    }
  }, []);
  useEffect(() => {
    // Only auto-select first delivery method on load if none already selected
    if (!selectedDeliveryMethod && deliveryMethods.length > 0) {
      setSelectedDeliveryMethod(deliveryMethods[0]);
    }
  }, [deliveryMethods]);

  const payLaterAllowed = !!customer?.pay_later_allowed;
  const initialGatewayAvailable = typeof settings?.payment_gateway_available === 'boolean' ? settings.payment_gateway_available : true;
  const effectiveGatewayUnavailable = gatewayUnavailable || !initialGatewayAvailable;
  const gatewayAvailable = !effectiveGatewayUnavailable;
  const hidePaymentSection = !payLaterAllowed && gatewayUnavailable;

  // If gateway becomes unavailable but pay-later is allowed, default to pay-later
  useEffect(() => {
    if (!gatewayAvailable && payLaterAllowed && paymentMode !== "pay_later") {
      setPaymentMode("pay_later");
    }
  }, [gatewayAvailable, payLaterAllowed, paymentMode]);

  // Fetch banks when user selects Pay by bank
  useEffect(() => {
    if (paymentMode === "gateway_bank" && gatewayAvailable) {
      fetchBanks();
    } else {
      setBanks([]);
      setSelectedBankId("");
    }
  }, [paymentMode, gatewayAvailable]);

  const handleContinueToPayment = async () => {
    if (hidePaymentSection) {
      toast({
        title: "Checkout unavailable",
        description: "Online payment is currently unavailable. Please contact support.",
        variant: "destructive",
      });
      return;
    }
    if (!selectedShippingBranch) {
      toast({ title: "Select dispatch branch", description: "Please choose Dispatch To before continuing.", variant: "destructive" });
      return;
    }
    if (!selectedBillingBranch) {
      toast({ title: "Select billing branch", description: "Please choose Bill To before continuing.", variant: "destructive" });
      return;
    }

    // Require a valid payment mode
    if (!paymentMode || (paymentMode === "pay_later" && !payLaterAllowed) || (paymentMode === "gateway_bank" && !selectedBankId)) {
      const description = paymentMode === "gateway_bank" && !selectedBankId
        ? "Please select a bank for Pay by bank."
        : payLaterAllowed
          ? "Please select Payment Gateway or Pay Later."
          : "Please select Payment Gateway.";
      toast({
        title: "Choose payment option",
        description,
        variant: "destructive"
      });
      return;
    }

    setIsProcessing(true);
    try {
      const payloadItems = items.map(({ product, quantity }) => ({ product_id: product.id, quantity }));

      const payload: Record<string, unknown> = {
        items: payloadItems,
        total: cartTotals.total,
        units: cartTotals.units,
        skus: cartTotals.skus,
        shipping_branch_id: selectedShippingBranch?.id,
        billing_branch_id: selectedBillingBranch?.id,
        delivery_instructions: deliveryInstructions,
        delivery_note: deliveryInstructions,
        customer_po_number: poNumber.trim() || undefined,
        delivery_method_id: selectedDeliveryMethod?.id ?? null,
        delivery_method_name: selectedDeliveryMethod?.name ?? null,
        delivery_time: selectedDeliveryMethod?.time ?? null,
        delivery_charge: selectedDeliveryMethod?.rate ?? null,
        payment_mode: paymentMode,
      };
      if (paymentMode === "gateway_bank" && selectedBankId) {
        payload.bank_id = selectedBankId;
      }
      const { data: result } = await api.post("/checkout", payload);

        if (result.success && result.requires_redirect && result.redirect_url) {
        const inv =
          typeof result.dna_invoice_id === "string" && result.dna_invoice_id
            ? result.dna_invoice_id
            : typeof result.payment_id === "string" && result.payment_id
              ? result.payment_id
              : "";
        if (inv && typeof window !== "undefined") {
          try {
            sessionStorage.setItem("dna_pending_invoice_id", inv);
          } catch { }
        }
        // DNA payment gateway flow: redirect to hosted checkout
        if (typeof window !== "undefined") {
          window.location.href = result.redirect_url;
        }
        return;
      }

      if (result.success) {
        const orderNum =
          result.order_number != null && result.order_number !== ""
            ? String(result.order_number)
            : "";
        toast({
          title: "Order Placed Successfully! 🎉",
          description: orderNum ? `Order Number: ${orderNum}` : "Your order was placed.",
          variant: "default",
        });
        try {
          sessionStorage.setItem("orders_needs_refresh", "1");
        } catch { }
        if (typeof window !== "undefined") {
          try {
            window.dispatchEvent(new Event("orders-refresh"));
          } catch { }
        }
        try {
          await refresh();
        } catch { }
        clearCart();
        if (orderNum && onCheckoutSuccess) {
          onCheckoutSuccess(orderNum);
        } else {
          onNavigate("dashboard");
        }
      } else {
        if (result?.code === 'stock_adjusted' && Array.isArray(result?.adjustments)) {
          try { sessionStorage.setItem('cart_adjustments', JSON.stringify(result.adjustments)); } catch {}
          toast({ title: 'Basket updated', description: 'Some items were adjusted to available stock.' });
          onBack();
          return;
        }
        const errorMessage: string | undefined = typeof result?.message === "string" ? result.message : undefined;

        // If DNA payment gateway failed due to being disabled or not configured,
        // mark gateway as unavailable. Behaviour:
        // - If pay-later is allowed: only show Pay Later (gateway option hidden).
        // - If pay-later is not allowed: hide entire payment section and block checkout.
        const paymentModeErrors: unknown = result?.errors?.payment_mode;
        const firstPaymentError =
          Array.isArray(paymentModeErrors) && typeof paymentModeErrors[0] === "string"
            ? paymentModeErrors[0]
            : undefined;

        const gatewayErrorText = firstPaymentError || errorMessage || "";
        if (
          typeof gatewayErrorText === "string" &&
          (gatewayErrorText.includes("Payment Gateway is disabled.") ||
            gatewayErrorText.includes("Payment Gateway is not configured."))
        ) {
          setGatewayUnavailable(true);
          if (!payLaterAllowed) {
            setPaymentMode(null);
          } else if (paymentMode === "gateway" || paymentMode === "gateway_bank" || paymentMode === null) {
            setPaymentMode("pay_later");
          }
        }

        toast({
          title: "Checkout Failed",
          description: errorMessage || "Please try again later.",
          variant: "destructive",
        });
      }
    } catch (error) {
      toast({
        title: "Checkout Failed",
        description: "Please try again later.",
        variant: "destructive",
      });
      console.error("Checkout error:", error);
    } finally {
      setIsProcessing(false);
    }
  };
  return (
    <div className="min-h-screen flex flex-col w-full max-w-[402px] mx-auto bg-[#F3F4F8]">
      {/* Header */}
      <div className="bg-[#EEF0F5] flex items-center justify-between px-4 h-[58px] border-b border-[#E2E6EF] relative">
        <button 
          onClick={onBack} 
          className="flex items-center gap-1 text-[#8A94A6] hover:text-black transition-colors"
        >
          <ChevronLeft className="w-5 h-5" />
          <span className="text-[14px] font-medium">Back</span>
        </button>
        
        <h1 className="absolute left-1/2 -translate-x-1/2 text-[17px] font-bold text-[#1E293B]">
          Checkout
        </h1>
        
        <div className="w-[60px]"></div> {/* Spacer for balance */}
      </div>

      <div className="flex w-full justify-center px-3 py-2">
        <Banner />
      </div>

      <main className="flex-1 overflow-y-auto px-[10px] py-[2px] space-y-2 pb-10">
        {/* Dispatch To Section */}
        <div className="bg-white rounded-[4px] border border-[#DCE1EE] overflow-hidden">
          <button 
            onClick={() => setIsDispatchExpanded(!isDispatchExpanded)}
            className="w-full flex items-center px-3 py-2.5 gap-3"
          >
            <div className="w-[22px] h-[22px] rounded-full border border-[#DCE1EE] bg-[#F7FAFF] flex items-center justify-center flex-shrink-0">
              <HomeIcon className="w-3.5 h-3.5 text-[#6EA2E8]" />
            </div>
            <div className="flex-1 text-left">
              <span className="block text-[13px] font-bold text-[#4E5667] leading-none">Dispatch To:</span>
              <div className="text-[12px] text-[#4E5667] font-semibold line-clamp-2 mt-1 leading-[1.2]">
                {selectedShippingBranch ? (
                  <>
                    <span className="font-bold">{selectedShippingBranch.name}</span>, {selectedShippingBranch.address_line1} {selectedShippingBranch.address_line2}
                  </>
                ) : "Select a branch"}
              </div>
            </div>
            <div className="h-8 w-px bg-[#E4E7F0]" />
            <ChevronDown className={`w-5 h-5 text-[#5A9AEC] transition-transform ${isDispatchExpanded ? 'rotate-180' : ''}`} />
          </button>
          
          {isDispatchExpanded && (
            <div className="px-4 pb-4 space-y-2 border-t border-gray-50 pt-3">
              {branches.map((branch) => (
                <label key={branch.id} className="flex items-center p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors">
                  <input
                    type="radio"
                    name="selectedBranch"
                    checked={selectedShippingBranch?.id === branch.id}
                    onChange={() => setSelectedShippingBranch(branch)}
                    className="w-4 h-4 text-[#4A90E5] focus:ring-[#4A90E5]"
                  />
                  <div className="ml-3 text-[13px] text-[#131A44]">
                    <span className="font-bold">{branch.name}</span>, {branch.address_line1}
                  </div>
                </label>
              ))}
            </div>
          )}
        </div>

        {/* Bill To Section */}
        <div className="bg-white rounded-[4px] border border-[#DCE1EE] overflow-hidden">
          <button
            onClick={() => setIsBillingExpanded(!isBillingExpanded)}
            className="w-full flex items-center px-3 py-2.5 gap-3"
          >
            <div className="w-[22px] h-[22px] rounded-full border border-[#DCE1EE] bg-[#F7FAFF] flex items-center justify-center flex-shrink-0">
              <HomeIcon className="w-3.5 h-3.5 text-[#6EA2E8]" />
            </div>
            <div className="flex-1 text-left">
              <span className="block text-[13px] font-bold text-[#4E5667] leading-none">Bill To:</span>
              <div className="text-[12px] text-[#4E5667] font-semibold line-clamp-2 mt-1 leading-[1.2]">
                {selectedBillingBranch ? (
                  <>
                    <span className="font-bold">{selectedBillingBranch.name}</span>, {selectedBillingBranch.address_line1} {selectedBillingBranch.address_line2}
                  </>
                ) : "Select a branch"}
              </div>
            </div>
            <div className="h-8 w-px bg-[#E4E7F0]" />
            <ChevronDown className={`w-5 h-5 text-[#5A9AEC] transition-transform ${isBillingExpanded ? 'rotate-180' : ''}`} />
          </button>

          {isBillingExpanded && (
            <div className="px-4 pb-4 space-y-2 border-t border-gray-50 pt-3">
              {branches.map((branch) => (
                <label key={`billing-${branch.id}`} className="flex items-center p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors">
                  <input
                    type="radio"
                    name="selectedBillingBranch"
                    checked={selectedBillingBranch?.id === branch.id}
                    onChange={() => setSelectedBillingBranch(branch)}
                    className="w-4 h-4 text-[#4A90E5] focus:ring-[#4A90E5]"
                  />
                  <div className="ml-3 text-[13px] text-[#131A44]">
                    <span className="font-bold">{branch.name}</span>, {branch.address_line1}
                  </div>
                </label>
              ))}
            </div>
          )}
        </div>

        {/* Delivery Method Section */}
        <div className="bg-white rounded-[4px] border border-[#DCE1EE] overflow-hidden">
          <button
            type="button"
            onClick={() => setIsDeliveryExpanded((v) => !v)}
            className="w-full flex items-center px-3 py-2.5 gap-3 hover:cursor-pointer"
          >
            <div className="w-[22px] h-[22px] rounded-full border border-[#DCE1EE] bg-[#F7FAFF] flex items-center justify-center flex-shrink-0">
              <PackageIcon className="w-3.5 h-3.5 text-[#6EA2E8]" />
            </div>
            <div className="flex-1 text-left">
              <span className="block text-[13px] font-bold text-[#4E5667] leading-none">Delivery Method:</span>
              <div className="text-[13px] text-[#4E5667] font-semibold mt-1">
                {selectedDeliveryMethod ? selectedDeliveryMethod.name : "Select method"}
              </div>
              {selectedDeliveryMethod?.time && (
                <div className="text-[12px] text-[#5A9AEC] font-semibold leading-none mt-1">
                  Estimated: {selectedDeliveryMethod.time} ({format(selectedDeliveryMethod.rate)})
                </div>
              )}
            </div>
            <div className="h-8 w-px bg-[#E4E7F0]" />
            <ChevronDown className={`w-5 h-5 text-[#5A9AEC] transition-transform ${isDeliveryExpanded ? 'rotate-180' : ''}`} />
          </button>
          {isDeliveryExpanded && (
            <div className="px-3 pb-3 pt-1 space-y-2 border-t border-[#EEF1F7]">
              {deliveryMethods.map((method) => (
                <label key={method.id} className="flex items-center gap-2.5 cursor-pointer group rounded-[4px] border border-[#E7EBF4] p-2.5">
                  <input
                    type="radio"
                    name="deliveryMethod"
                    checked={selectedDeliveryMethod?.id === method.id}
                    onChange={() => setSelectedDeliveryMethod(method)}
                    className="w-4 h-4 text-[#4A90E5] focus:ring-[#4A90E5]"
                  />
                  <div className="flex-1 text-[12px] leading-tight">
                    <span className="font-bold text-[#4E5667] block">{method.name}</span>
                    <span className="text-[#5A9AEC] font-semibold">{method.time} ({format(method.rate)})</span>
                  </div>
                </label>
              ))}
            </div>
          )}
        </div>

        {/* Additional Instructions */}
        <div className="bg-white rounded-[4px] border border-[#DCE1EE] px-3 py-2.5">
          <input
            type="text"
            placeholder="Note"
            className="w-full text-[13px] text-[#4E5667] placeholder:text-[#A4ADBC] border-none focus:ring-0 p-0"
            value={deliveryInstructions}
            onChange={(e) => setDeliveryInstructions(e.target.value)}
          />
        </div>
        <div className="bg-white rounded-[4px] border border-[#DCE1EE] px-3 py-2.5 mt-2">
          <input
            type="text"
            placeholder="P.O. Number (if applicable)"
            className="w-full text-[13px] text-[#4E5667] placeholder:text-[#A4ADBC] border-none focus:ring-0 p-0"
            value={poNumber}
            onChange={(e) => setPoNumber(e.target.value)}
            autoComplete="off"
          />
        </div>

        {/* Combined Summary Card */}
        <div className="bg-[#FBFCFF] rounded-[6px] border border-[#D5DBE7] overflow-hidden text-[13px]">
          {/* Order Details */}
          <div className="px-5 pt-4 pb-3">
            <div className="flex items-start justify-between">
              <h4 className="font-bold text-[#3F4B63] text-[13px] leading-[1]">Order Details</h4>
              <div className="space-y-1 text-right">
                <div className="flex items-center justify-end gap-5">
                  <span className="text-[#3F4B63] font-bold text-[13px] leading-none">SKUs</span>
                  <span className="text-[#5F6C83] text-[14px] leading-none">{cartTotals.skus}</span>
                </div>
                <div className="flex items-center justify-end gap-5">
                  <span className="text-[#3F4B63] font-bold text-[13px] leading-none">Items</span>
                  <span className="text-[#5F6C83] text-[14px] leading-none">{cartTotals.units}</span>
                </div>
              </div>
            </div>
          </div>

          {/* Delivery Details */}
          <div className="px-5 py-3 border-t border-[#E5EAF3]">
            <div className="flex justify-between items-start mb-1">
              <h4 className="font-bold text-[#3F4B63] text-[13px]">Delivery</h4>
              <span className="font-bold text-[#3F4B63] text-[13px] leading-none">{selectedDeliveryMethod?.name} - {format(deliveryRate)}</span>
            </div>
            <div className="text-[#3F4B63] leading-[1.25] text-[13px] text-right">
              {selectedShippingBranch ? (
                <>
                  <div className="font-[500] text-[#4E5667]">{selectedShippingBranch.name}</div>
                  <div>{selectedShippingBranch.address_line1}</div>
                  {selectedShippingBranch.address_line2 && <div>{selectedShippingBranch.address_line2}</div>}
                  <div>{selectedShippingBranch.city}</div>
                  <div>{selectedShippingBranch.zip_code}</div>
                </>
              ) : "No address selected"}
            </div>
          </div>

          {/* Summary Details */}
          <div className="px-5 pt-3 pb-4 border-t border-[#D9DFEB]">
            <h4 className="font-bold text-[#3F4B63] mb-3 text-[16px]">Summary</h4>
            <div className="space-y-[7px]">
              <div className="flex justify-between">
                <span className="text-[#5E6A80] font-[500] text-[15px]">Subtotal</span>
                <span className="text-[#5E6A80] font-[500] text-[15px]">{format(subtotal)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[#5E6A80] font-[500] text-[15px]">Wallet Discount</span>
                <span className="text-[#5E6A80] font-[500] text-[15px]">{format(discount)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[#5E6A80] font-[500] text-[15px]">Delivery</span>
                <span className="text-[#5E6A80] font-[500] text-[15px]">{format(deliveryRate)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[#5E6A80] font-[500] text-[15px]">VAT ({subtotal > 0 ? ((vat/subtotal)*100).toFixed(2) : '20.00'}%)</span>
                <span className="text-[#5E6A80] font-[500] text-[15px]">{format(vat)}</span>
              </div>
              <div className="flex justify-between text-[16px] pt-3 mt-2">
                <span className="text-[#3A86E4] font-bold text-[14px]">Payment Total</span>
                <span className="text-[#3A86E4] font-bold text-[16px]">{format(paymentTotal)}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Payment options */}
        {!hidePaymentSection && (
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <h3 className="text-[14px] font-bold text-[#131A44] mb-4 uppercase tracking-wider opacity-60">Payment Method</h3>
            <div className="space-y-3">
              {gatewayAvailable && (
                <label className="flex items-center gap-3 cursor-pointer">
                  <input
                    type="radio"
                    name="paymentMode"
                    className="w-4 h-4 text-[#4A90E5] focus:ring-[#4A90E5]"
                    checked={paymentMode === "gateway"}
                    onChange={() => setPaymentMode("gateway")}
                  />
                  <span className="text-[13px] text-[#131A44] font-medium leading-tight">Online (Card, Apple/Google Pay)</span>
                </label>
              )}
              {gatewayAvailable && (
                <label className="flex items-center gap-3 cursor-pointer">
                  <input
                    type="radio"
                    name="paymentMode"
                    className="w-4 h-4 text-[#4A90E5] focus:ring-[#4A90E5]"
                    checked={paymentMode === "gateway_bank"}
                    onChange={() => setPaymentMode("gateway_bank")}
                  />
                  <span className="text-[13px] text-[#131A44] font-medium leading-tight">Bank Transfer</span>
                </label>
              )}
              {paymentMode === "gateway_bank" && (
                <div className="pl-7 mt-2">
                  <select
                    value={selectedBankId}
                    onChange={(e) => setSelectedBankId(e.target.value)}
                    className="w-full text-[13px] border border-gray-100 rounded-lg px-3 py-2 bg-gray-50 focus:ring-[#4A90E5] outline-none"
                  >
                    <option value="">Select your bank</option>
                    {banks.map((b) => (
                      <option key={b.bank_id} value={b.bank_id}>
                        {b.friendly_name || b.name}
                      </option>
                    ))}
                  </select>
                </div>
              )}
              {payLaterAllowed && (
                <label className="flex items-center gap-3 cursor-pointer">
                  <input
                    type="radio"
                    name="paymentMode"
                    className="w-4 h-4 text-[#4A90E5] focus:ring-[#4A90E5]"
                    checked={paymentMode === "pay_later"}
                    onChange={() => setPaymentMode("pay_later")}
                  />
                  <span className="text-[13px] text-[#131A44] font-medium leading-tight">Pay Later (Order on credit)</span>
                </label>
              )}
            </div>
          </div>
        )}

        {/* Checkout Button */}
        <div className="pt-4 pb-12">
          <button
            onClick={handleContinueToPayment}
            disabled={isProcessing || !selectedShippingBranch || !selectedBillingBranch}
            className="w-full bg-[#4A90E5] disabled:bg-[#BDC7DE] text-white py-4 rounded-xl 
            font-bold text-[18px] hover:bg-[#3B7DCF] transition-colors shadow-lg shadow-[#4A90E53D] active:scale-[0.98] transform transition-transform"
          >
            {isProcessing ? "Processing..." : "Continue to Payment"}
          </button>
        </div>
      </main>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[402px] z-50 shadow-[0px_-1px_8px_0px_#555E5814] bg-white">
        <div className="h-[74px] px-2 pt-[8px] pb-[10px] grid grid-cols-5 items-center 
        bg-[#F1F2F7] border-t border-[#E4E7F0]">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faChartSimple} className="text-[23px]" />
            <span>Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center gap-[4px] text-[#4A90E5] text-[11px] font-bold leading-none relative h-full justify-center">
            <FontAwesomeIcon icon={faShop} className="text-[23px]" />
            <span>Shop</span>
            <div className="absolute bottom-[2px] w-[20px] h-[2px] bg-[#4A90E5] rounded-full"></div>
          </button>
          <button onClick={() => onNavigate("shop", true)} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faHeart} className="text-[23px]" />
            <span>Favourites</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faWallet} className="text-[23px]" />
            <span>Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faUser} className="text-[23px]" />
            <span>Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}