"use client";

import api from "@/lib/axios";
import { useEffect, useState, useMemo } from "react";
import { Minus, Plus, Trash2, Heart } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChartSimple, faHeart as faHeartSolid, faShop, faTruck, faUser, faWallet } from "@fortawesome/free-solid-svg-icons";
import { useCustomer } from "@/components/customer-provider";
import { useCurrency } from "@/components/currency-provider";
import { Banner } from "@/components/banner";
import { MobilePageHeader } from "@/components/mobile-page-header";

interface ProductItem {
  id: number;
  name: string;
  image: string;
  // Effective (possibly discounted) unit price as string
  price: string;
  // Original (pre-discount) unit price
  original_price?: number;
  // Percentage discount applied at unit level, if any
  applied_discount_percentage?: number;
  discount?: string;
  step_quantity?: number;
  wallet_credit?: number;
}

interface MobileBasketProps {
  onNavigate: (page: any, favorites?: boolean) => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  onCartSync: (nextCart: Record<number, { product: ProductItem; quantity: number }>) => void;
  increment: (product: ProductItem) => void;
  decrement: (product: ProductItem) => void;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  clearCart: () => void;
  onBack: () => void;
}

export function MobileBasket({ onNavigate, cart, onCartSync, increment, decrement, totals, clearCart, onBack }: MobileBasketProps) {
  const [favourites, setFavourites] = useState<Record<number, boolean>>({});
  const { toast } = useToast();
  const { symbol, format } = useCurrency();
  const { refresh } = useCustomer();
  const [adjustments, setAdjustments] = useState<Array<{ product_id: number; product_name?: string; old_quantity: number; new_quantity: number }>>([]);

  // Get API base URL (without /api) to access admin assets
  const getApiBaseUrl = () => {
    if (typeof window === 'undefined') return 'http://localhost:8000';
    const rawBase = process.env.NEXT_PUBLIC_API_URL || process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000';
    return rawBase.replace(/\/api$/, '').replace(/\/$/, '');
  };

  const defaultImagePath = `${getApiBaseUrl()}/assets/img/default_product.png`;

  const handleImageError = (e: React.SyntheticEvent<HTMLImageElement, Event>) => {
    const target = e.currentTarget;
    if (target.src !== defaultImagePath && !target.src.includes('default_product.png')) {
      target.src = defaultImagePath;
    }
  };

  // Sync favourites from shared customer provider to local map
  const { favoriteProductIds, setFavorite } = useCustomer();
  useEffect(() => {
    const map: Record<number, boolean> = {};
    favoriteProductIds.forEach((id) => {
      map[id] = true;
    });
    setFavourites(map);
  }, [favoriteProductIds]);

  // Backend-driven cart state
  const [items, setItems] = useState<Array<{ product: ProductItem; quantity: number }>>([]);
  const [cartTotals, setCartTotals] = useState<{ units: number; skus: number; subtotal: number; totalDiscount: number; total: number }>({ units: 0, skus: 0, subtotal: 0, totalDiscount: 0, total: 0 });
  const [deletingIds, setDeletingIds] = useState<Record<number, boolean>>({});

  // Calculate total wallet credit from backend cart items
  const totalWalletCredit = useMemo(() => {
    return items.reduce((sum, { product, quantity }) => {
      const credit = Number(product.wallet_credit ?? 0);
      return sum + (isNaN(credit) ? 0 : credit) * quantity;
    }, 0);
  }, [items]);

  // Helper function to get step_quantity from product or products_cache
  const getStepQuantity = (productId: number, product?: ProductItem): number => {
    // First try from the product object passed in
    if (product?.step_quantity && Number(product.step_quantity) > 0) {
      return Number(product.step_quantity);
    }

    // Try to find it in products_cache
    try {
      const raw = sessionStorage.getItem('products_cache');
      if (raw) {
        const parsed = JSON.parse(raw);
        const categories = Array.isArray(parsed) ? parsed : (parsed?.categories || []);

        const findProductInNodes = (nodes: any[]): any => {
          for (const node of nodes) {
            if (Array.isArray(node.products)) {
              const found = node.products.find((p: any) => Number(p.id) === productId);
              if (found) return found;
            }
            if (Array.isArray(node.subcategories)) {
              const found = findProductInNodes(node.subcategories);
              if (found) return found;
            }
          }
          return null;
        };

        const productFromCache = findProductInNodes(categories);
        if (productFromCache && productFromCache.step_quantity && Number(productFromCache.step_quantity) > 0) {
          return Number(productFromCache.step_quantity);
        }
      }
    } catch { }

    // Default to 1
    return 1;
  };

  const findProductInProductsCache = (productId: number): any | null => {
    try {
      const raw = sessionStorage.getItem('products_cache');
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      const categories = Array.isArray(parsed) ? parsed : (parsed?.categories || []);
      const findProductInNodes = (nodes: any[]): any => {
        for (const node of nodes) {
          if (Array.isArray(node?.products)) {
            const found = node.products.find((p: any) => Number(p?.id) === productId);
            if (found) return found;
          }
          if (Array.isArray(node?.subcategories)) {
            const found = findProductInNodes(node.subcategories);
            if (found) return found;
          }
        }
        return null;
      };
      return findProductInNodes(categories);
    } catch {
      return null;
    }
  };

  const mapApiItemsToBasketItems = (
    apiItems: Array<{ product_id: number; quantity: number; product?: any; unit_price?: number; original_unit_price?: number; applied_discount_percentage?: number }>
  ): Array<{ product: ProductItem; quantity: number }> => {
    return apiItems
      .map((it) => {
        const productId = Number(it?.product?.id ?? it?.product_id);
        if (!Number.isFinite(productId)) return null;

        const fallbackProduct = cart?.[productId]?.product as any;
        const cacheProduct = findProductInProductsCache(productId);
        const p = it?.product ?? fallbackProduct ?? cacheProduct;
        if (!p) return null;

        const apiStepQty = Number(p?.step_quantity ?? 0);
        const stepQty = apiStepQty > 0 ? apiStepQty : getStepQuantity(productId, p as ProductItem);
        const baseUnit = Number(p?.price ?? it?.original_unit_price ?? it?.unit_price ?? 0);
        const effectiveUnit = Number(p?.effective_price ?? it?.unit_price ?? baseUnit);

        return {
          product: {
            id: productId,
            name: String(p?.name ?? ""),
            image: String(p?.image ?? ""),
            price: String(effectiveUnit),
            original_price: Number.isFinite(baseUnit) ? baseUnit : undefined,
            applied_discount_percentage: typeof p?.applied_discount_percentage === "number" ? p.applied_discount_percentage : undefined,
            wallet_credit: Number(p?.wallet_credit ?? 0),
            step_quantity: stepQty,
          } as ProductItem,
          quantity: Number(it?.quantity) || 0,
        };
      })
      .filter((x): x is { product: ProductItem; quantity: number } => Boolean(x));
  };

  const extractApiErrorMessage = (error: unknown, fallback: string) => {
    const err = error as {
      message?: string;
      response?: { data?: { message?: string; error?: string } };
    };
    return err?.response?.data?.message || err?.response?.data?.error || err?.message || fallback;
  };

  const applyCartResponse = (res: any) => {
    const apiItems: Array<{ product_id: number; quantity: number; product?: any; unit_price?: number; original_unit_price?: number; applied_discount_percentage?: number }> = res?.data?.cart?.items || [];
    const mapped = mapApiItemsToBasketItems(apiItems);
    setItems(mapped);
    const nextCart: Record<number, { product: ProductItem; quantity: number }> = {};
    mapped.forEach(({ product, quantity }) => {
      nextCart[product.id] = { product, quantity };
    });
    onCartSync(nextCart);
    const c = res?.data?.cart;
    setCartTotals({
      units: Number(c?.units || 0),
      skus: Number(c?.skus || 0),
      subtotal: Number(c?.subtotal || 0),
      totalDiscount: Number(c?.total_discount || 0),
      total: Number(c?.total || 0),
    });
  };

  const loadCartFromApi = async () => {
    const res = await api.get('/cart');
    applyCartResponse(res);
  };

  // Load cart from API on mount and when products update (price may change)
  useEffect(() => {
    const loadCart = async () => {
      try {
        await loadCartFromApi();
      } catch { }
    };
    loadCart();
    // If checkout adjusted quantities, show a banner to the user
    try {
      const raw = sessionStorage.getItem('cart_adjustments');
      if (raw) {
        const adj = JSON.parse(raw);
        if (Array.isArray(adj)) setAdjustments(adj);
        sessionStorage.removeItem('cart_adjustments');
      }
    } catch { }
    const onProductsCacheUpdated = () => {
      loadCart();
    };
    if (typeof window !== 'undefined') {
      window.addEventListener('products_cache_updated', onProductsCacheUpdated);
    }
    return () => {
      if (typeof window !== 'undefined') {
        window.removeEventListener('products_cache_updated', onProductsCacheUpdated);
      }
    }
  }, []);

  const apiIncrement = async (product: ProductItem) => {
    try {
      const step = getStepQuantity(product.id, product);
      const res = await api.post('/cart/add', { product_id: product.id, quantity: step });
      if (res?.data && res.data.success === false) {
        const msg = res.data.message || 'Requested quantity is not available';
        toast({ title: 'Quantity not available', description: msg, variant: 'destructive' });
        return;
      }
      applyCartResponse(res);
    } catch (e: any) {
      toast({ title: 'Failed to add item', description: e?.message || 'Please try again', variant: 'destructive' });
    }
  };

  const apiDecrement = async (product: ProductItem) => {
    try {
      const step = getStepQuantity(product.id, product);
      const currentQty = items.find(item => item.product.id === product.id)?.quantity || 0;
      // Calculate new quantity after decrement
      const nextQty = Math.max(0, currentQty - step);
      const decrementQty = currentQty > 0 ? step : 0;

      if (decrementQty === 0) return;

      const res = await api.post('/cart/decrement', { product_id: product.id, quantity: decrementQty });
      applyCartResponse(res);
    } catch (e: any) {
      toast({ title: 'Failed to update item', description: e?.message || 'Please try again', variant: 'destructive' });
    }
  };

  const apiRemoveItem = async (product: ProductItem) => {
    const productId = Number(product?.id);
    if (!Number.isFinite(productId) || productId <= 0) {
      toast({ title: 'Failed to remove item', description: 'Invalid product id', variant: 'destructive' });
      return;
    }
    try {
      setDeletingIds((prev) => ({ ...prev, [productId]: true }));
      const res = await api.post('/cart/set', { product_id: productId, quantity: 0 });
      applyCartResponse(res);
    } catch (e: unknown) {
      try {
        await loadCartFromApi();
      } catch { }
      toast({
        title: 'Failed to remove item',
        description: extractApiErrorMessage(e, 'Please try again'),
        variant: 'destructive',
      });
    } finally {
      setDeletingIds((prev) => ({ ...prev, [productId]: false }));
    }
  };

  const handleCheckout = () => {
    onNavigate("checkout");
  };

  const toggleFavorite = async (product: ProductItem) => {
    const productId = product.id;
    const next = !favourites[productId];
    // optimistic update
    setFavourites((prev) => ({ ...prev, [productId]: next }));
    try {
      await setFavorite(productId, next);
      toast({ title: next ? "Added to favourites" : "Removed from favourites", description: product.name });
    } catch (e: any) {
      // revert on failure
      setFavourites((prev) => ({ ...prev, [productId]: !next }));
      toast({ title: "Failed to update favourites", description: e?.message || "Please try again", variant: "destructive" });
    }
  };
  return (
    <div className="flex h-[100dvh] w-full justify-center overflow-hidden bg-[#FAFBFD]">
      <div
        className="relative mx-auto h-full min-h-0 w-full max-w-[402px] overflow-hidden bg-[#FAFBFD] lg:max-h-[1024px] lg:max-w-[1000px]"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
      <MobilePageHeader title="Basket" onBack={onBack} />

      <main
        className={`scrollbar-hide absolute inset-x-0 bottom-[64px] top-[96px] w-full overflow-y-auto bg-[#FAFBFD] px-4 pt-4 ${items.length > 0 ? "pb-[220px] lg:pb-[188px]" : "pb-[100px]"}`}
      >
        <div className="mx-auto w-full max-w-[354px] px-0 lg:max-w-full">
          <Banner className="mx-auto h-[89px] w-full max-w-[354px] rounded-[10px] border border-[#E2E2E2] lg:h-[242px] lg:max-w-[968px] lg:border-0 lg:!rounded-[10px]" />
        </div>
        {adjustments.length > 0 && (
          <div className="mx-auto mt-2 w-full max-w-[968px] border-b border-amber-200 bg-amber-50 px-4 py-2 text-[13px] leading-snug text-amber-950">
            <div className="mb-1 font-bold">We adjusted some items to available stock:</div>
            <ul className="list-disc pl-5">
              {adjustments.map((a, idx) => (
                <li key={idx}>
                  {a.product_name || `#${a.product_id}`}: {a.old_quantity} → {a.new_quantity}
                </li>
              ))}
            </ul>
          </div>
        )}

        <div className="mt-1">
        {items.map(({ product, quantity }) => {
          const unitPrice = parseFloat((product.price ?? "0").replace(/[^\d.\-]+/g, "")) || 0;
          const lineTotal = unitPrice * quantity;
          const walletLine = (Number(product.wallet_credit) || 0) * quantity;
          return (
            <div
              key={product.id}
              className="flex gap-3 border-b border-[#E2E2E2] bg-white px-4 py-3"
            >
              <div className="flex h-[85px] w-[85px] shrink-0 items-center justify-center overflow-hidden rounded-lg border border-[#E8ECF4] bg-white p-1">
                <img
                  src={product.image || defaultImagePath}
                  onError={handleImageError}
                  alt={product.name}
                  className="h-full w-full object-contain"
                />
              </div>

              <div className="min-w-0 flex-1 flex flex-col gap-1.5">
                <h3 className="line-clamp-2 text-[12px] font-medium uppercase leading-none tracking-[0.03em] text-[#0A0835]">
                  {product.name}
                </h3>

                {/* Figma Control / Price: horizontal, justify-between, ~34px */}
                <div className="flex min-h-[34px] w-full items-center justify-between gap-2">
                  <div className="flex min-w-0 items-center gap-2">
                    <div className="flex h-[34px] shrink-0 items-center rounded-full bg-[#0A0835] px-0.5">
                      <button
                        type="button"
                        onClick={() => apiDecrement(product)}
                        className="flex h-8 w-8 items-center justify-center text-white transition-opacity hover:opacity-80"
                        aria-label="Decrease quantity"
                      >
                        <Minus className="h-4 w-4" strokeWidth={2.5} />
                      </button>
                      <span className="min-w-[22px] text-center text-[14px] font-bold leading-none text-white">{quantity}</span>
                      <button
                        type="button"
                        onClick={() => apiIncrement(product)}
                        className="flex h-8 w-8 items-center justify-center text-white transition-opacity hover:opacity-80"
                        aria-label="Increase quantity"
                      >
                        <Plus className="h-4 w-4" strokeWidth={2.5} />
                      </button>
                    </div>
                    <button
                      type="button"
                      onClick={() => toggleFavorite(product)}
                      className={`flex h-8 w-8 shrink-0 items-center justify-center text-[#BDC7DE] transition-colors ${favourites[product.id] ? "text-[#4A90E5]" : "hover:text-[#4A90E5]"}`}
                      aria-label={favourites[product.id] ? "Remove from favourites" : "Add to favourites"}
                    >
                      <Heart className={`h-5 w-5 ${favourites[product.id] ? "fill-[#4A90E5] text-[#4A90E5]" : ""}`} strokeWidth={2} />
                    </button>
                    <button
                      type="button"
                      onClick={() => apiRemoveItem(product)}
                      disabled={Boolean(deletingIds[product.id])}
                      className="flex h-8 w-8 shrink-0 items-center justify-center text-[#BDC7DE] transition-colors hover:text-red-500 disabled:opacity-50"
                      aria-label="Remove from basket"
                    >
                      <Trash2 className="h-5 w-5" strokeWidth={2} />
                    </button>
                  </div>
                  <div className="flex shrink-0 flex-col items-end justify-center text-right">
                    <span className="text-[15px] font-bold leading-tight tracking-tight text-[#0A0835]">{format(lineTotal)}</span>
                    <span className="mt-0.5 text-[12px] font-semibold leading-none text-[#4A90E5]">
                      +{symbol}
                      {walletLine.toFixed(2)}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          );
        })}
        {items.length === 0 ? (
          <div className="px-4 py-14 text-center text-[15px] font-medium text-[#8F98AD]">Your basket is empty</div>
        ) : null}
        </div>
      </main>

      {/* Sticky summary + checkout — only when basket has line items */}
      {items.length > 0 ? (
        <div
          className="fixed bottom-[64px] left-1/2 z-40 h-[127.2px] w-full max-w-[402px] -translate-x-1/2 bg-gradient-to-b from-[#E8E8ED] to-[#F4F2F9] px-[23px] pb-[13px] pt-4 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)] lg:h-[127.2px] lg:max-w-[1000px] lg:px-[23px] lg:pb-[13px] lg:pt-4"
          style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
        >
          <div className="mx-auto flex h-[43.2px] w-full max-w-[500px] items-center justify-between rounded-[2px] px-[12px] text-[13px] font-bold leading-none text-[#3D495E] lg:max-w-[500px]">
            <span>{cartTotals.units} Units</span>
            <span className="h-3 w-px shrink-0 bg-[#D2D0E1]" aria-hidden />
            <span>{cartTotals.skus} SKUs</span>
            <span className="h-3 w-px shrink-0 bg-[#D2D0E1]" aria-hidden />
            <span>{format(cartTotals.total)}</span>
            <span className="h-3 w-px shrink-0 bg-[#D2D0E1]" aria-hidden />
            <span className="inline-flex items-center gap-1 text-[13px] font-bold text-[#4A90E5]">
              <FontAwesomeIcon icon={faTruck} className="text-[12px]" aria-hidden />
              <span>
                +{symbol}
                {totalWalletCredit.toFixed(2)}
              </span>
            </span>
          </div>
          <p className="mt-0 text-center text-[12px] font-normal leading-none tracking-[0.05em] text-[#68676E]">Includes FREE delivery</p>
          <button
            type="button"
            onClick={handleCheckout}
            className="mt-2 mx-auto flex h-[43px] w-full max-w-[954px] items-center justify-center rounded-[25px] bg-gradient-to-b from-[#2868C0] to-[#4C92E9] px-[7px] py-3 text-[16px] font-bold leading-none text-white shadow-[0_2px_10px_rgba(40,104,192,0.35)] transition-opacity hover:opacity-95 active:opacity-90"
          >
            Checkout
          </button>
        </div>
      ) : null}

      <nav
        className="fixed bottom-0 left-1/2 z-50 box-border flex h-[64px] w-full max-w-[402px] -translate-x-1/2 flex-col rounded-t-[10px] bg-[#F6F4FA] px-[43px] pb-4 pt-2 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)] lg:max-w-[1000px]"
        aria-label="Main navigation"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <div className="flex min-h-0 w-full flex-1 items-center justify-center">
          <div className="flex h-[40px] w-[316px] max-w-full items-center justify-between">
            <button
              type="button"
              onClick={() => onNavigate("dashboard")}
              className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faChartSimple} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Dashboard</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", false)}
              className="flex h-full min-w-0 flex-col items-center justify-center gap-1 text-[#4A90E5]"
              aria-current="page"
            >
              <FontAwesomeIcon icon={faShop} className="h-[23px] w-[23px] shrink-0 text-[23px] leading-none text-[#4A90E5]" aria-hidden />
              <span className="inline-flex h-[13px] min-w-[28px] items-center justify-center text-center text-[11px] font-medium leading-none text-[#4A90E5]">
                Shop
              </span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", true)}
              className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faHeartSolid} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Favourites</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("wallet")}
              className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faWallet} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Wallet</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("account")}
              className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faUser} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Account</span>
            </button>
          </div>
        </div>
      </nav>
      </div>
    </div>
  );
}