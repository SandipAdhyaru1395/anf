"use client";

import api from "@/lib/axios";
import { useEffect, useState, useMemo } from "react";
import { Minus, Plus, Home, ShoppingBag, User, Wallet, Star } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faBagShopping, faPlus, faMinus, faStar, faChartSimple, faHeart } from "@fortawesome/free-solid-svg-icons";
import { useCustomer } from "@/components/customer-provider";
import { useCurrency } from "@/components/currency-provider";
import { Banner } from "@/components/banner";
import { ChevronLeft, Trash2, Heart } from "lucide-react";

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

  const defaultImagePath = `${getApiBaseUrl()}/public/assets/img/default_product.png`;

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
    <div className="min-h-screen flex flex-col w-full max-w-[402px] mx-auto bg-white">

      {/* <div className="h-[50px] bg-white flex items-center">
        <div className="w-[66px] h-[25px] flex items-center justify-center">
          <FontAwesomeIcon icon={faBagShopping} className="text-green-600" style={{ width: "21px", height: "24px" }} />
        </div>
        <h1 className="text-lg font-semibold text-gray-300 text-[12px] hover:cursor-pointer hover:underline">Shop</h1>
        &nbsp;<h1 className="text-lg font-semibold text-gray-100 text-[16px] ">/</h1>
        &nbsp;<h1 className="text-lg font-semibold text-black-600 text-[16px]">Basket</h1>
      </div> */}

      {/* Header */}
      <div className="bg-white flex items-center justify-between px-4 h-[60px] border-b border-gray-100 relative">
        <button
          onClick={onBack}
          className="flex items-center gap-1 text-[#8A94A6] hover:text-black transition-colors"
        >
          <ChevronLeft className="w-5 h-5" />
          <span className="text-[15px] font-medium">Back</span>
        </button>

        <h1 className="absolute left-1/2 -translate-x-1/2 text-[17px] font-bold text-[#1E293B]">
          Basket
        </h1>

        <div className="w-[60px]"></div> {/* Spacer for balance */}
      </div>

      {/* Banner */}
      <div className="mt-2">
        <Banner />
      </div>
      {adjustments.length > 0 && (
        <div className="bg-yellow-100 border-b border-yellow-300 text-yellow-900 px-4 py-2 text-sm">
          <div className="font-semibold mb-1">We adjusted some items to available stock:</div>
          <ul className="list-disc pl-5">
            {adjustments.map((a, idx) => (
              <li key={idx}>
                {a.product_name || `#${a.product_id}`}: {a.old_quantity} → {a.new_quantity}
              </li>
            ))}
          </ul>
        </div>
      )}
      <div className="flex-1 bg-white overflow-y-auto mt-[4px] pb-56">
        {items.map(({ product, quantity }) => (
          <div key={product.id} className="flex relative border-b border-gray-100 py-4 px-4 gap-4">
            {/* Product Image */}
            <div className="w-[85px] h-[85px] flex-shrink-0 bg-white border border-gray-100 rounded-lg overflow-hidden flex items-center justify-center p-1">
              <img
                src={product.image || defaultImagePath}
                onError={handleImageError}
                alt={product.name}
                className="w-full h-full object-contain"
              />
            </div>

            {/* Product Details */}
            <div className="flex-1 flex flex-col min-w-0">
              <h3 className="text-[13px] font-bold text-[#1E293B] leading-snug uppercase mb-1 line-clamp-2">
                {product.name}
              </h3>

              <div className="flex items-center gap-4 mt-2 mb-3">
                {/* Quantity Selector */}
                <div className="flex items-center bg-[#131A44] rounded-full h-[32px] px-1 gap-1">
                  <button
                    onClick={() => apiDecrement(product)}
                    className="w-8 h-8 flex items-center justify-center text-[#4A90E5] hover:opacity-80 transition-opacity"
                  >
                    <Minus className="w-4 h-4" strokeWidth={3} />
                  </button>
                  <span className="min-w-[24px] text-center text-white text-[14px] font-bold">
                    {quantity}
                  </span>
                  <button
                    onClick={() => apiIncrement(product)}
                    className="w-8 h-8 flex items-center justify-center text-[#4A90E5] hover:opacity-80 transition-opacity"
                  >
                    <Plus className="w-4 h-4" strokeWidth={3} />
                  </button>
                </div>

                <div className="flex items-center gap-2">
                  {/* Favourite button */}
                  <button
                    onClick={() => toggleFavorite(product)}
                    className="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-[#BDC7DE] hover:text-[#4A90E5] transition-colors"
                  >
                    <Heart className={`w-5 h-5 ${favourites[product.id] ? "fill-[#35D6EC] text-[#35D6EC]" : ""}`} strokeWidth={2} />
                  </button>

                  {/* Delete button */}
                  <button
                    onClick={() => apiRemoveItem(product)}
                    disabled={Boolean(deletingIds[product.id])}
                    className="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-[#BDC7DE] hover:text-red-500 transition-colors"
                  >
                    <Trash2 className="w-5 h-5" strokeWidth={2} />
                  </button>
                </div>
              </div>
            </div>

            {/* Price section */}
            <div className="flex flex-col items-end justify-center min-w-[70px]">
              {(() => {
                const unitPrice = parseFloat((product.price ?? '0').replace(/[^\d.\-]+/g, '')) || 0;
                const total = unitPrice * quantity;
                return (
                  <>
                    <span className="text-[17px] font-bold text-[#1E293B]">
                      {format(total)}
                    </span>
                    {typeof product.wallet_credit === "number" && product.wallet_credit > 0 && (
                      <div className="flex items-center gap-1 text-[#4A90E5] mt-1">
                        <FontAwesomeIcon icon={faWallet} className="text-[12px]" />
                        <span className="text-[12px] font-bold">
                          {symbol}{(product.wallet_credit * quantity).toFixed(2)}
                        </span>
                      </div>
                    )}
                  </>
                );
              })()}
            </div>
          </div>
        ))}
        {items.length === 0 && <div className="px-4 py-12 text-center text-[15px] font-medium text-[#8A94A6]">Your basket is empty</div>}
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[402px] bg-white border-t z-50 shadow-[0px_-1px_8px_0px_#555E5814]">
        <div className="bg-[#F3F4F9] border-t border-[#DCE1EE] px-4 py-3 flex flex-col items-center gap-2">
          {/* Stats row */}
            <div className="flex items-center justify-center gap-2 text-[14px] text-[#4E5667] font-bold">
              <span>{cartTotals.units} Units</span>
              <span className="text-[#DCE1EE] font-normal px-1">|</span>
              <span>{cartTotals.skus} SKUs</span>
              <span className="text-[#DCE1EE] font-normal px-1">|</span>
              <span>{format(cartTotals.total)}</span>
              <span className="text-[#DCE1EE] font-normal px-1">|</span>
              <div className="flex items-center gap-1 text-[#4A90E5]">
                <FontAwesomeIcon icon={faWallet} className="text-[14px]" />
                <span>+{symbol}{totalWalletCredit.toFixed(2)}</span>
              </div>
            </div>

            {/* Delivery Info */}
            <div className="text-[13px] text-[#8F98AD] font-bold">
              Includes FREE delivery
            </div>

            {/* Full-width Checkout Button */}
            <button
              onClick={handleCheckout}
              className="w-full bg-[#4A90E5] text-white py-3 rounded-[30px] font-bold text-[17px] hover:bg-[#3B7DCF] transition-colors shadow-sm shadow-[#4A90E53D] mt-1"
            >
              Checkout
            </button>
          </div>
        <div className="h-[74px] px-2 pt-[8px] pb-[10px] grid grid-cols-5 items-center bg-[#F1F2F7] border-t border-[#E4E7F0]">
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