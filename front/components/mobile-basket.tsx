"use client";

import api from "@/lib/axios";
import { useEffect, useState, useMemo } from "react";
import { Minus, Plus, Home, ShoppingBag, User, Wallet, Star } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faBagShopping, faPlus, faMinus, faStar } from "@fortawesome/free-solid-svg-icons";
import { useCustomer } from "@/components/customer-provider";
import { useCurrency } from "@/components/currency-provider";
import { Banner } from "@/components/banner";

interface ProductItem {
  id: number;
  name: string;
  image: string;
  price: string;
  discount?: string;
  step_quantity?: number;
  wallet_credit?: number;
}

interface MobileBasketProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "checkout" | "wallet" | "account") => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  increment: (product: ProductItem) => void;
  decrement: (product: ProductItem) => void;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  clearCart: () => void;
  onBack: () => void;
}

export function MobileBasket({ onNavigate, cart, increment, decrement, totals, clearCart, onBack }: MobileBasketProps) {
  const [favourites, setFavourites] = useState<Record<number, boolean>>({});
  const { toast } = useToast();
  const { symbol, format } = useCurrency();
  const { refresh } = useCustomer();
  const [adjustments, setAdjustments] = useState<Array<{ product_id: number; product_name?: string; old_quantity: number; new_quantity: number }>>([]);

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
    } catch {}
    
    // Default to 1
    return 1;
  };

  // Load cart from API on mount and when products update (price may change)
  useEffect(() => {
    const loadCart = async () => {
      try {
        const res = await api.get('/cart');
        const apiItems: Array<{ product_id: number; quantity: number; product?: any }> = res?.data?.cart?.items || [];
        const mapped = apiItems
          .filter((it) => it?.product)
          .map((it) => {
            const productId = Number(it.product.id);
            const apiStepQty = Number(it.product.step_quantity ?? 0);
            const stepQty = apiStepQty > 0 ? apiStepQty : getStepQuantity(productId);
            
            return {
              product: {
                id: productId,
                name: it.product.name,
                image: it.product.image,
                price: String(it.product.price),
                wallet_credit: Number(it.product.wallet_credit ?? 0),
                step_quantity: stepQty,
              } as ProductItem,
              quantity: Number(it.quantity) || 0,
            };
          });
        setItems(mapped);
        if (mapped.length === 0) {
          onNavigate('shop');
        }
        const c = res?.data?.cart;
        setCartTotals({
          units: Number(c?.units || 0),
          skus: Number(c?.skus || 0),
          subtotal: Number(c?.subtotal || 0),
          totalDiscount: Number(c?.total_discount || 0),
          total: Number(c?.total || 0),
        });
      } catch {}
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
    } catch {}
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
      const apiItems: Array<{ product_id: number; quantity: number; product?: any }> = res?.data?.cart?.items || [];
      const mapped = apiItems
        .filter((it) => it?.product)
        .map((it) => {
          const productId = Number(it.product.id);
          const apiStepQty = Number(it.product.step_quantity ?? 0);
          const stepQty = apiStepQty > 0 ? apiStepQty : getStepQuantity(productId);
          
          return {
            product: {
              id: productId,
              name: it.product.name,
              image: it.product.image,
              price: String(it.product.price),
              wallet_credit: Number(it.product.wallet_credit ?? 0),
              step_quantity: stepQty,
            } as ProductItem,
            quantity: Number(it.quantity) || 0,
          };
        });
      setItems(mapped);
      const c = res?.data?.cart;
      setCartTotals({
        units: Number(c?.units || 0),
        skus: Number(c?.skus || 0),
        subtotal: Number(c?.subtotal || 0),
        totalDiscount: Number(c?.total_discount || 0),
        total: Number(c?.total || 0),
      });
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
      const apiItems: Array<{ product_id: number; quantity: number; product?: any }> = res?.data?.cart?.items || [];
      const mapped = apiItems
        .filter((it) => it?.product)
        .map((it) => {
          const productId = Number(it.product.id);
          const apiStepQty = Number(it.product.step_quantity ?? 0);
          const stepQty = apiStepQty > 0 ? apiStepQty : getStepQuantity(productId);
          
          return {
            product: {
              id: productId,
              name: it.product.name,
              image: it.product.image,
              price: String(it.product.price),
              wallet_credit: Number(it.product.wallet_credit ?? 0),
              step_quantity: stepQty,
            } as ProductItem,
            quantity: Number(it.quantity) || 0,
          };
        });
      setItems(mapped);
      const c = res?.data?.cart;
      setCartTotals({
        units: Number(c?.units || 0),
        skus: Number(c?.skus || 0),
        subtotal: Number(c?.subtotal || 0),
        totalDiscount: Number(c?.total_discount || 0),
        total: Number(c?.total || 0),
      });
    } catch (e: any) {
      toast({ title: 'Failed to update item', description: e?.message || 'Please try again', variant: 'destructive' });
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
    <div className="min-h-screen  flex flex-col w-full max-w-[1000px] mx-auto">
      
      {/* <div className="h-[50px] bg-white flex items-center">
        <div className="w-[66px] h-[25px] flex items-center justify-center">
          <FontAwesomeIcon icon={faBagShopping} className="text-green-600" style={{ width: "21px", height: "24px" }} />
        </div>
        <h1 className="text-lg font-semibold text-gray-300 text-[12px] hover:cursor-pointer hover:underline">Shop</h1>
        &nbsp;<h1 className="text-lg font-semibold text-gray-100 text-[16px] ">/</h1>
        &nbsp;<h1 className="text-lg font-semibold text-black-600 text-[16px]">Basket</h1>
      </div> */}

      {/* Header */}
      <div className="bg-white flex items-center border-b h-[50px]">
        <div className="w-[66px] h-[25px] flex items-center justify-center">
          <FontAwesomeIcon icon={faBagShopping} className="text-green-600" style={{ width: "21px", height: "24px" }} />
        </div>
        <span onClick={onBack} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline">
          Shop
        </span>
        &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
        &nbsp;<span className="text-[16px] font-semibold">Basket</span>
      </div>

      {/* Banner */}
      <div className="mt-0">
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
      <div className="flex-1 bg-white overflow-y-auto mt-[10px] pb-48">
        {items.map(({ product, quantity }) => (
          <div key={product.id} className="flex items-start flex-col border-t border-gray-200 py-[16px] mx-[10px]">
            <div className="flex items-start h-[53px] w-[100%] grow">
              <div className="w-[50px] h-[50px] overflow-hidden flex items-center justify-center">
                <img src={product.image || "/placeholder.svg"} alt={product.name} className="w-full h-full object-contain" />
              </div>
              <div className="text-[16px] h-[53px] text-black truncate grow">{product.name}</div>
            </div>

            <div className="flex items-center justify-between w-full">
              <div className="qtySelectWrapper bg-black w-[120px] h-[32px] rounded-full flex items-center">
                <button onClick={() => apiDecrement(product)} className="flex items-center justify-center hover:cursor-pointer w-[32px] h-[32px]">
                  <FontAwesomeIcon icon={faMinus} className="text-green-600" style={{ width: "16px", height: "16px" }} />
                </button>
                <span className="min-w-[56px] h-[26px] text-center text-lg text-white font-semibold">{quantity}</span>
                <button onClick={() => apiIncrement(product)} className="flex items-center justify-center hover:cursor-pointer p-2 w-[32px] h-[32px]">
                  <FontAwesomeIcon icon={faPlus} className="text-green-600" style={{ width: "16px", height: "16px" }} />
                </button>
              </div>
              <div className="rightwrapper flex items-center">
                <div className="favSelectWrapper mr-[12px]">
                  {/* Favourite toggle */}
                  <button onClick={() => toggleFavorite(product)} className={`w-8 h-8 rounded-full border-2 flex items-center justify-center ${favourites[product.id] ? "bg-white border-green-600" : "border-[#c0d3c4] bg-white"}`} aria-label="Toggle favourite">
                    <Star className={`w-[16px] h-[16px] ${favourites[product.id] ? "text-green-600 fill-green-600" : "text-[#c0d3c4] fill-[#c0d3c4]"}`} />
                  </button>
                </div>
                <div className="deleteWrapper">
                  {/* Delete removes the product from cart */}
                  <button
                    onClick={async () => {
                      try {
                        await api.post('/cart/set', { product_id: product.id, quantity: 0 });
                        const res = await api.get('/cart');
                        const apiItems: Array<{ product_id: number; quantity: number; product?: any }> = res?.data?.cart?.items || [];
                        const mapped = apiItems
                          .filter((it) => it?.product)
                          .map((it) => {
                            const productId = Number(it.product.id);
                            const apiStepQty = Number(it.product.step_quantity ?? 0);
                            const stepQty = apiStepQty > 0 ? apiStepQty : getStepQuantity(productId);
                            
                            return {
                              product: {
                                id: productId,
                                name: it.product.name,
                                image: it.product.image,
                                price: String(it.product.price),
                                wallet_credit: Number(it.product.wallet_credit ?? 0),
                                step_quantity: stepQty,
                              } as ProductItem,
                              quantity: Number(it.quantity) || 0,
                            };
                          });
                        setItems(mapped);
                        const c = res?.data?.cart;
                        setCartTotals({
                          units: Number(c?.units || 0),
                          skus: Number(c?.skus || 0),
                          subtotal: Number(c?.subtotal || 0),
                          totalDiscount: Number(c?.total_discount || 0),
                          total: Number(c?.total || 0),
                        });
                      } catch {}
                    }}
                    className="w-[70px] h-[32px] rounded-sm border border-[#c0d3c4] mr-[12px] text-sm text-black bg-white hover:cursor-pointer border-btn-shadow"
                  >
                    Delete
                  </button>
                </div>
                <div className="pricesWrapper flex flex-col text-right justify-end w-[80px]">
                  <span className="font-semibold text-black text-[16px]">
                    {format((parseFloat((product.price ?? '0').replace(/[^\d.\-]+/g, '')) || 0) * quantity)}
                  </span>
                  {typeof product.wallet_credit === "number" && product.wallet_credit > 0 && (
                    <span className="inline-flex items-center gap-1 text-green-600 justify-end">
                      <FontAwesomeIcon icon={faWallet} className="text-green-600" style={{ width: "12px", height: "13px" }} />
                      <span className="font-semibold text-xs">£{product.wallet_credit.toFixed(2)}</span>
                    </span>
                  )}
                  {product.discount && <span className="text-green-600 ml-2">{product.discount} off</span>}
                </div>
              </div>
            </div>

            {/* <div className="flex-1 min-w-0">
              <div className="text-xs text-gray-500 flex items-center gap-2"></div>
            </div>
            <div className="flex items-center gap-2"></div> */}
          </div>
        ))}
        {items.length === 0 && <div className="px-4 py-6 text-center text-sm text-gray-500">Your basket is empty</div>}
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50">
        {cartTotals.units > 0 && (
          <div className="bg-white border-b px-4 py-3 box-shadow-top max-h-[109px]">
            <div className="flex items-center justify-center text-sm gap-2 pt-1 h-[20px]">
              <span className="text-black font-semibold">{cartTotals.units} Units</span>
              <span className="spacer"> | </span>
              <span className="text-black font-semibold">{cartTotals.skus} SKUs</span>
              <span className="spacer"> | </span>
              <span className="font-semibold text-black">{format(cartTotals.total)}</span>
              <span className="spacer"> | </span>
              <div className="flex items-center">
                <span className="inline-flex items-center gap-1 text-green-600 text-sm font-semibold">
                  <FontAwesomeIcon icon={faWallet} className="text-green-600" style={{ width: "14px", height: "14px" }} />
                  <span>
                    {symbol}
                    {totalWalletCredit.toFixed(2)}
                  </span>
                </span>
                {cartTotals.totalDiscount > 0 && <span className="text-green-600 text-xs">{format(cartTotals.totalDiscount)} off</span>}
              </div>
            </div>
            <div className="text-sm font-semibold text-center text-[#999] pt-1 pb-2 leading-[16px]">Includes FREE delivery</div>
            <button onClick={handleCheckout} className="w-full bg-green-600 text-white py-2 rounded-sm font-semibold text-lg hover:cursor-pointer box-shadow-bottom">
              Checkout
            </button>
          </div>
        )}
        <div className="flex flex-row items-center justify-between h-[72px] footer-nav-col px-[18px]">
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
