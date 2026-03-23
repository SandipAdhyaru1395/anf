"use client";

import { useEffect, useMemo, useState } from "react";
import { Search, Filter, X, ShoppingBag, ChevronDown, ChevronUp, Plus, Minus, Star, Home, Wallet, User, Heart, RefreshCw, Scan, SlidersHorizontal } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { useToast } from "@/hooks/use-toast";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faStar, faSearch, faChartSimple, faHeart } from "@fortawesome/free-solid-svg-icons";
import api from "@/lib/axios";
import { Thumbnail } from "@/components/thumbnail";
import { useCurrency } from "@/components/currency-provider";
import { useCustomer } from "@/components/customer-provider";
import { useSettings } from "@/components/settings-provider";
import { startLoading, stopLoading } from "@/lib/loading";
import { resolveBackendAssetUrl } from "@/lib/utils";

interface MobileShopProps {
  onNavigate: (page: any, favorites?: boolean) => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  increment: (product: ProductItem) => void;
  decrement: (product: ProductItem) => void;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  showFavorites?: boolean;
}

interface ProductItem {
  id: number;
  name: string;
  image: string;
  price: string;
  rrp?: string;
  discount?: string;
  step_quantity?: number;
  wallet_credit?: number;
  quantity?: number;
  available_qty?: number;
  allow_out_of_stock?: boolean;
}

/** Backend sometimes omits qty fields — don't treat as 0 stock or + button stays dead. */
function getProductStockInfo(product: ProductItem): { known: boolean; stock: number } {
  const raw = (product as any)?.quantity ?? (product as any)?.available_qty;
  if (raw === undefined || raw === null || raw === "") {
    return { known: false, stock: 0 };
  }
  const n = Number(raw);
  if (!Number.isFinite(n)) {
    return { known: false, stock: 0 };
  }
  return { known: true, stock: n };
}

const PRIMARY_BUTTON_GRADIENT: React.CSSProperties = {
  background: "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)",
};

// Generic tree node that can be either a category (has subcategories)
// or a brand (has products). Brands appear as subcategories of leaf categories.
interface TreeNode {
  name: string;
  badge?: string;
  badgeColor?: string;
  // Optional comma-separated or array of tags coming from backend (e.g., "NEW,HOT")
  tags?: string[] | string;
  subcategories?: TreeNode[];
  products?: ProductItem[];
  is_special: number;
  image: string;
}

// fetched categories state
const initialCategories: TreeNode[] = [];

/** Shop header logo — Figma max box */
const SHOP_LOGO_BOX = {
  width: 168.8212432861328,
  height: 22.00458335876465,
  opacity: 1 as const,
} as const;

export function MobileShop({
  onNavigate = () => { },
  cart = {},
  increment = () => { },
  decrement = () => { },
  totals = { units: 0, skus: 0, subtotal: 0, totalDiscount: 0, total: 0 },
  showFavorites = false
}: Partial<MobileShopProps>) {
  const { settings } = useSettings();
  const resolvedLogo =
    resolveBackendAssetUrl(settings?.company_logo_url) ?? settings?.company_logo_url ?? null;
  const resolvedThumb =
    resolveBackendAssetUrl(settings?.thumbnail) ?? settings?.thumbnail ?? null;
  /** Only use backend sources — no static fallback */
  const companyLogoSrc = resolvedLogo || resolvedThumb || null;
  const { format, symbol } = useCurrency();
  const [searchQuery, setSearchQuery] = useState("");
  const [categories, setCategories] = useState<TreeNode[]>(initialCategories);
  // Track expanded nodes by path key (e.g., "Vaping", "Vaping::Disposables", "Vaping::Disposables::Brand X")
  const [expandedPaths, setExpandedPaths] = useState<string[]>([]);
  const { toast } = useToast();
  const { isFavorite, setFavorite } = useCustomer();
  const [cartQuantities, setCartQuantities] = useState<Record<number, number>>({});
  const [cartTotals, setCartTotals] = useState<{ units: number; skus: number; subtotal: number; totalDiscount: number; total: number }>({ units: 0, skus: 0, subtotal: 0, totalDiscount: 0, total: 0 });
  const [walletCreditTotal, setWalletCreditTotal] = useState<number>(0);

  // total wallet credit now sourced from backend cart items
  const totalWalletCredit = walletCreditTotal;
  const togglePath = (path: string, singleRoot = false) => {
    setExpandedPaths((prev) => {
      const isOpen = prev.includes(path);
      if (singleRoot) {
        return isOpen ? [] : [path];
      }
      return isOpen ? prev.filter((p) => p !== path) : [...prev, path];
    });
  };

  useEffect(() => {
    let isMounted = true;
    const loadCart = async () => {
      try {
        const res = await api.get('/cart');
        const items: Array<{ product_id: number; quantity: number; product?: { wallet_credit?: number } }> = res?.data?.cart?.items || [];
        if (!isMounted) return;
        const map: Record<number, number> = {};
        let wallet = 0;
        items.forEach(it => {
          const q = Number(it.quantity) || 0;
          map[Number(it.product_id)] = q;
          const rawCredit: any = it?.product?.wallet_credit ?? 0;
          const credit = Number(rawCredit);
          wallet += (isNaN(credit) ? 0 : credit) * q;
        });
        setCartQuantities(map);
        setWalletCreditTotal(wallet);
        const c = res?.data?.cart;
        setCartTotals({
          units: Number(c?.units || 0),
          skus: Number(c?.skus || 0),
          subtotal: Number(c?.subtotal || 0),
          totalDiscount: Number(c?.total_discount || 0),
          total: Number(c?.total || 0),
        });
        setWalletCreditTotal(Number(c?.wallet_credit_total || wallet));
      } catch { }
    };
    loadCart();
    try {
      const raw = sessionStorage.getItem("products_cache");
      if (raw) {
        const parsed = JSON.parse(raw);
        if (!isMounted) return;
        if (Array.isArray(parsed)) {
          setCategories(parsed);
        } else if (Array.isArray(parsed?.categories)) {
          // Backward compatibility with older cache shape
          setCategories(parsed.categories);
        }
      } else {
        // No cache present: fetch settings for version, then products once and cache
        const filterNodesWithProducts = (nodes: TreeNode[]): TreeNode[] => {
          return nodes
            .map((node) => {
              const filteredChildren = node.subcategories ? filterNodesWithProducts(node.subcategories) : undefined;
              const productsCount = Array.isArray(node.products) ? node.products.length : 0;
              const hasProductsHere = productsCount > 0;
              const hasProductsInChildren = Array.isArray(filteredChildren) && filteredChildren.length > 0;
              if (!hasProductsHere && !hasProductsInChildren) {
                return null as unknown as TreeNode;
              }
              return {
                ...node,
                ...(filteredChildren ? { subcategories: filteredChildren } : {}),
              };
            })
            .filter((n): n is TreeNode => Boolean(n));
        };
        (async () => {
          try {
            let productVersion = 0;
            try {
              const settingsRes = await api.get("/settings");
              const vers = settingsRes?.data?.versions;
              productVersion = Number(vers?.Product || 0) || 0;
            } catch { }

            const res = await api.get("/products");
            const data = res.data;
            if (!isMounted) return;
            if (Array.isArray(data?.categories)) {
              const filtered = filterNodesWithProducts(data.categories as TreeNode[]);
              // Remove duplicate products by id within each category tree
              const dedupeProductsInTree = (nodes: any[]): any[] => {
                return nodes.map((node: any) => {
                  let nextProducts = Array.isArray(node?.products) ? node.products : undefined;
                  if (Array.isArray(nextProducts)) {
                    const seen = new Set<number>();
                    nextProducts = nextProducts.filter((p: any) => {
                      const id = Number(p?.id);
                      if (!Number.isFinite(id)) return false;
                      if (seen.has(id)) return false;
                      seen.add(id);
                      return true;
                    });
                  }
                  const nextChildren = Array.isArray(node?.subcategories) ? dedupeProductsInTree(node.subcategories) : undefined;
                  return { ...node, ...(nextProducts ? { products: nextProducts } : {}), ...(nextChildren ? { subcategories: nextChildren } : {}) };
                });
              };
              const deduped = dedupeProductsInTree(filtered);
              setCategories(deduped);
              try { sessionStorage.setItem("products_cache", JSON.stringify({ version: productVersion, categories: deduped })); } catch { }
            }
          } catch { }
        })();
      }
    } catch { }
    // Listen for cache updates to re-render with latest data
    const onProductsCacheUpdated = () => {
      try {
        const raw2 = sessionStorage.getItem("products_cache");
        if (!raw2) return;
        const parsed2 = JSON.parse(raw2);
        if (Array.isArray(parsed2)) {
          setCategories(parsed2);
        } else if (Array.isArray(parsed2?.categories)) {
          setCategories(parsed2.categories);
        }
        // Also refresh cart totals using latest prices from backend reprice logic
        loadCart();
      } catch { }
    };
    if (typeof window !== "undefined") {
      window.addEventListener("products_cache_updated", onProductsCacheUpdated);
    }
    return () => {
      isMounted = false;
      if (typeof window !== "undefined") {
        window.removeEventListener("products_cache_updated", onProductsCacheUpdated);
      }
    };
  }, []);

  // Derived categories filtered by search/favourites and top-level special stock logic.
  const displayedCategories = useMemo(() => {
    const query = searchQuery.trim().toLowerCase();

    const filterForDisplay = (nodes: TreeNode[], topAncestorIsSpecial: boolean): TreeNode[] => {
      return nodes
        .map((node) => {
          const filteredChildren = node.subcategories ? filterForDisplay(node.subcategories, topAncestorIsSpecial) : undefined;
          let filteredProducts = node.products;

          if (filteredProducts) {
            if (query) {
              filteredProducts = filteredProducts.filter((p) => p.name.toLowerCase().includes(query));
            }
            if (showFavorites) {
              filteredProducts = filteredProducts.filter((p) => isFavorite(p.id));
            }
            if (topAncestorIsSpecial) {
              filteredProducts = filteredProducts.filter((p) => {
                const stock = Number((p as any)?.quantity ?? (p as any)?.available_qty ?? 0);
                const rawPrice: any = (p as any)?.price;
                const numericPrice = typeof rawPrice === 'number'
                  ? rawPrice
                  : Number(String(rawPrice ?? '').replace(/[^0-9.]/g, ''));
                const priceOk = !isNaN(numericPrice) && numericPrice > 0;
                return stock > 0 && priceOk;
              });
            }
          }

          const hasChildren = Array.isArray(filteredChildren) && filteredChildren.length > 0;
          const hasProducts = Array.isArray(filteredProducts) && filteredProducts.length > 0;

          // Prune empty categories
          if (!hasChildren && !hasProducts) {
            return null as unknown as TreeNode;
          }

          return {
            ...node,
            ...(filteredChildren ? { subcategories: filteredChildren } : {}),
            ...(filteredProducts ? { products: filteredProducts } : {}),
          } as TreeNode;
        })
        .filter((n): n is TreeNode => Boolean(n));
    };

    return categories
      .map((root) => {
        const topIsSpecial = root.is_special === 1;
        const res = filterForDisplay([root], topIsSpecial);
        return res[0];
      })
      .filter((n): n is TreeNode => Boolean(n));
  }, [categories, searchQuery, showFavorites, isFavorite]);

  // Auto-expand paths when searching to reveal matches.
  // Do NOT collapse on unrelated state changes (e.g., favorites toggle)
  useEffect(() => {
    const query = searchQuery.trim();
    if (!query) return;

    const paths: string[] = [];
    const traverse = (nodes: TreeNode[], parentPath?: string) => {
      nodes.forEach((node) => {
        const path = parentPath ? `${parentPath}::${node.name}` : node.name;
        if ((node.subcategories && node.subcategories.length) || (node.products && node.products.length)) {
          paths.push(path);
        }
        if (node.subcategories && node.subcategories.length) {
          traverse(node.subcategories, path);
        }
      });
    };
    traverse(displayedCategories);
    setExpandedPaths(paths);
  }, [searchQuery, displayedCategories]);

  // cart and totals are provided by parent

  const handleIncrement = async (product: ProductItem) => {
    try {
      const step = Number(product?.step_quantity) > 0 ? Number(product.step_quantity) : 1;
      const allowOutOfStock = Boolean((product as any)?.allow_out_of_stock);
      const { known: stockKnown, stock } = getProductStockInfo(product);
      const current = Number(cartQuantities[product.id] || 0);
      if (!allowOutOfStock && stockKnown && stock > 0 && current + step > stock) {
        toast({ title: 'Quantity not available', description: `Only ${stock} in stock`, variant: 'destructive' });
        return;
      }
      const res = await api.post('/cart/add', { product_id: product.id, quantity: step });
      if (res?.data && res.data.success === false) {
        const msg = res.data.message || 'Requested quantity is not available';
        toast({ title: 'Quantity not available', description: msg, variant: 'destructive' });
        return;
      }
      increment(product);
      const items: Array<{ product_id: number; quantity: number; product?: { wallet_credit?: number } }> = res?.data?.cart?.items || [];
      const map: Record<number, number> = {};
      let wallet = 0;
      items.forEach(it => {
        const q = Number(it.quantity) || 0;
        map[Number(it.product_id)] = q;
        const rawCredit: any = it?.product?.wallet_credit ?? 0;
        const credit = Number(rawCredit);
        wallet += (isNaN(credit) ? 0 : credit) * q;
      });
      setCartQuantities(map);
      setWalletCreditTotal(wallet);
      const c = res?.data?.cart;
      setCartTotals({
        units: Number(c?.units || 0),
        skus: Number(c?.skus || 0),
        subtotal: Number(c?.subtotal || 0),
        totalDiscount: Number(c?.total_discount || 0),
        total: Number(c?.total || 0),
      });
      setWalletCreditTotal(Number(c?.wallet_credit_total || wallet));
    } catch (e: any) {
      toast({ title: 'Failed to add to cart', description: e?.message || 'Please try again', variant: 'destructive' });
    }
  };

  const handleDecrement = async (product: ProductItem) => {
    try {
      const step = Number(product?.step_quantity) > 0 ? Number(product.step_quantity) : 1;
      const current = Number(cartQuantities[product.id] || 0);
      // Calculate new quantity after decrement
      const nextQty = Math.max(0, current - step);
      const decrementQty = current > 0 ? step : 0;

      if (decrementQty === 0) return;

      const res = await api.post('/cart/decrement', { product_id: product.id, quantity: decrementQty });
      if (res?.data && res.data.success === false) {
        const msg = res.data.message || 'Could not update cart';
        toast({ title: 'Update failed', description: msg, variant: 'destructive' });
        return;
      }
      decrement(product);
      const items: Array<{ product_id: number; quantity: number; product?: { wallet_credit?: number } }> = res?.data?.cart?.items || [];
      const map: Record<number, number> = {};
      let wallet = 0;
      items.forEach(it => {
        const q = Number(it.quantity) || 0;
        map[Number(it.product_id)] = q;
        const rawCredit: any = it?.product?.wallet_credit ?? 0;
        const credit = Number(rawCredit);
        wallet += (isNaN(credit) ? 0 : credit) * q;
      });
      const prevQty = cartQuantities[product.id] || 0;
      setCartQuantities(map);
      setWalletCreditTotal(wallet);
      const c = res?.data?.cart;
      setCartTotals({
        units: Number(c?.units || 0),
        skus: Number(c?.skus || 0),
        subtotal: Number(c?.subtotal || 0),
        totalDiscount: Number(c?.total_discount || 0),
        total: Number(c?.total || 0),
      });
      setWalletCreditTotal(Number(c?.wallet_credit_total || wallet));
      // Show message when item is removed (quantity becomes 0)
      if (prevQty > 0 && (map[product.id] || 0) === 0) {
        toast({ title: 'Removed from Cart', description: `${product.name} removed from your basket` });
      }
    } catch (e: any) {
      toast({ title: 'Failed to update cart', description: e?.message || 'Please try again', variant: 'destructive' });
    }
  };

  const toggleFavorite = async (product: ProductItem) => {
    const current = isFavorite(product.id);
    const prevExpanded = expandedPaths;
    try {
      await setFavorite(product.id, !current);
      // Restore expansion state to prevent collapsing due to re-render
      setExpandedPaths(prevExpanded);
      toast({ title: !current ? "Added to favourites" : "Removed from favourites", description: product.name });
    } catch (e: any) {
      setExpandedPaths(prevExpanded);
      toast({ title: "Failed to update favourites", description: e?.message || "Please try again", variant: "destructive" });
    }
  };

  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsLoading(false);
    }, 2000);
    return () => clearTimeout(timer);
  }, []);

  /** Logo + thumbnail + search — same chrome for loading and loaded; soft shadow under whole top block */
  const shopTopChrome = (
    <div className="sticky top-0 z-[60] w-full shrink-0 bg-white shadow-[0_4px_16px_-4px_rgba(15,23,42,0.12)]">
      <header className="w-full border-b border-[#F1F2F7] bg-white">
        <div
          className="flex w-full min-h-[38px] items-center"
          style={{ paddingLeft: 20, paddingRight: 20, paddingTop: 14, gap: 8 , marginTop :"50px" }}
        >
          <Thumbnail
            height={25.00458335876465}
            containerClassName="max-w-[168.8212432861328px]"
            imgClassName="object-left "
          />
        </div>
      </header>
      <div className="px-[14px] pb-[12px] pt-[20px] bg-white">
        <div className="flex items-center gap-[10px]">
          <div className="relative flex h-[42px] flex-1 items-center rounded-[21px] bg-[#F3F4F9] px-[16px]">
            <Search className="mr-2 h-4 w-4 text-[#8A94A6]" strokeWidth={2.5} />
            <Input
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              readOnly={isLoading}
              aria-busy={isLoading}
              className="h-full w-full border-none bg-transparent pl-0 pr-10 text-[14px] text-[#3D495E] shadow-none placeholder:text-[#8A94A6] focus-visible:ring-0 disabled:cursor-wait disabled:opacity-90"
              style={{ border: "none", boxShadow: "none" }}
              placeholder="Search products, brands, SKUs..."
            />
            {!isLoading && searchQuery && (
              <button type="button" onClick={() => setSearchQuery("")} className="absolute right-[46px] top-1/2 -translate-y-1/2">
                <X className="h-4 w-4 cursor-pointer text-[#8A94A6]" />
              </button>
            )}
            <SlidersHorizontal className={`absolute right-[16px] top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-[#8A94A6] ${isLoading ? "pointer-events-none" : "cursor-pointer"}`} />
          </div>
          <button type="button" className="flex h-[42px] w-[42px] items-center justify-center rounded-[14px] bg-[#F3F4F9] text-[#8A94A6]" aria-label="Scan">
            <Scan className="h-5 w-5" />
          </button>
        </div>
      </div>
    </div>
  );

  if (isLoading) {
    return (
      <div className="mx-auto flex min-h-screen w-full max-w-[402px] flex-col bg-white">
        {shopTopChrome}
        <div className="flex flex-1 flex-col items-center justify-center px-6 pb-[100px]">
          {companyLogoSrc ? (
            <img src={companyLogoSrc} alt="" className="h-auto w-[200px] max-w-[85%] object-contain animate-pulse" />
          ) : (
            <div className="h-24 w-48 max-w-[85%] rounded-lg bg-[#F3F4F9] animate-pulse" />
          )}
        </div>
        <nav className="fixed bottom-0 left-1/2 z-50 w-full max-w-[402px] -translate-x-1/2 bg-[#F1F2F7] shadow-[0_-4px_16px_-4px_rgba(15,23,42,0.12)]">
          <div className="grid h-[74px] grid-cols-5 items-center border-t border-[#E4E7F0] px-2 pb-[10px] pt-2">
            <button type="button" onClick={() => onNavigate("dashboard")} className="flex flex-col items-center gap-1 text-[11px] font-bold leading-none text-[#BDC7DE]">
              <FontAwesomeIcon icon={faChartSimple} className="text-[23px]" />
              <span>Dashboard</span>
            </button>
            <button type="button" onClick={() => onNavigate("shop", false)} className={`flex flex-col items-center gap-1 text-[11px] font-bold leading-none ${!showFavorites ? "text-[#4A90E5]" : "text-[#BDC7DE]"}`}>
              <FontAwesomeIcon icon={faShop} className="text-[23px]" />
              <span>Shop</span>
            </button>
            <button type="button" onClick={() => onNavigate("shop", true)} className={`flex flex-col items-center gap-1 text-[11px] font-bold leading-none ${showFavorites ? "text-[#4A90E5]" : "text-[#BDC7DE]"}`}>
              <FontAwesomeIcon icon={faHeart} className="text-[23px]" />
              <span>Favourites</span>
            </button>
            <button type="button" onClick={() => onNavigate("wallet")} className="flex flex-col items-center gap-1 text-[11px] font-bold leading-none text-[#BDC7DE]">
              <FontAwesomeIcon icon={faWallet} className="text-[23px]" />
              <span>Wallet</span>
            </button>
            <button type="button" onClick={() => onNavigate("account")} className="flex flex-col items-center gap-1 text-[11px] font-bold leading-none text-[#BDC7DE]">
              <FontAwesomeIcon icon={faUser} className="text-[23px]" />
              <span>Account</span>
            </button>
          </div>
        </nav>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex flex-col w-full max-w-[402px] mx-auto bg-white">
      {shopTopChrome}

      {/* Categories (recursive) */}
      <div className="space-y-2 overflow-y-auto pb-56 px-[12px] pt-[12px] bg-white">
        {displayedCategories.map((node) => (
          <CategoryNode key={node.name} node={node} path={node.name} depth={0} expandedPaths={expandedPaths} togglePath={togglePath} cart={cart} onIncrement={handleIncrement} onDecrement={handleDecrement} isFavorite={isFavorite} onToggleFavorite={toggleFavorite} cartQuantities={cartQuantities} topAncestorIsSpecial={node.is_special === 1} />
        ))}
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[402px] z-50 bg-white shadow-[0_-4px_16px_-4px_rgba(15,23,42,0.12)]">
        {/* Basket Summary */}
         <div className="fixed bottom-[74px] left-1/2 w-full max-w-[402px] h-[60px] -translate-x-1/2 bg-[#F3F4F9] border-t border-[#DCE1EE] pt-[12px] pr-[8px] pb-[12px] pl-[8px] z-40">
              <div className="flex h-full items-center justify-between">
                <div className="flex flex-col">
                  <div className="flex items-center gap-1.5 text-[13px] text-[#3E4A62] font-bold whitespace-nowrap tracking-tight">
                    <span>{totals.units} Units</span>
                    <span className="text-[#D0D7E6] font-normal px-[2px]">|</span>
                    <span>{totals.skus} SKUs</span>
                    <span className="text-[#D0D7E6] font-normal px-[2px]">|</span>
                    <span>{symbol}{totals.total.toFixed(2)}</span>
                    <span className="text-[#D0D7E6] font-normal px-[2px]">|</span>
                    <span className="inline-flex items-center gap-[3px] text-[#4A90E5] font-bold">
                      <FontAwesomeIcon icon={faWallet} className="text-[12px] opacity-90" />
                      <span>+{symbol}{totalWalletCredit.toFixed(2)}</span>
                    </span>
                  </div>
                  <div className="text-[12px] text-[#727C90] mt-[2px] font-medium text-center">
                    Includes FREE delivery
                  </div>
                </div>
      
                <button
                  type="button"
                  onClick={() => onNavigate("basket")}
                  className="w-[116px] h-[35px] max-w-[300px] rounded-[5px] p-[8px] text-[15px] font-normal leading-none text-white shadow-sm transition-opacity hover:opacity-95 flex items-center justify-center"
                  style={PRIMARY_BUTTON_GRADIENT}
                >
                  View Basket
                </button>
              </div>
            </div>

        <div className="h-[74px] px-2 pt-[8px] pb-[10px] grid grid-cols-5 items-center bg-[#F1F2F7] border-t border-[#E4E7F0]">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faChartSimple} className="text-[23px]" />
            <span>Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop", false)} className="flex flex-col items-center gap-[4px] text-[#4A90E5] text-[11px] font-bold leading-none">
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
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faUser} className="text-[23px]" />
            <span>Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}

type CategoryNodeProps = {
  node: TreeNode;
  path: string;
  depth: number;
  expandedPaths: string[];
  togglePath: (path: string, singleRoot?: boolean) => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  onIncrement: (product: ProductItem) => void;
  onDecrement: (product: ProductItem) => void;
  isFavorite: (productId: number) => boolean;
  onToggleFavorite: (product: ProductItem) => void;
  cartQuantities: Record<number, number>;
  topAncestorIsSpecial: boolean;
};

function CategoryNode({ node, path, depth, expandedPaths, togglePath, cart, onIncrement, onDecrement, isFavorite, onToggleFavorite, cartQuantities, topAncestorIsSpecial }: CategoryNodeProps) {
  const { symbol } = useCurrency();
  const isOpen = expandedPaths.includes(path);
  const hasChildren = Array.isArray(node.subcategories) && node.subcategories.length > 0;
  const hasProducts = Array.isArray(node.products) && node.products.length > 0;

  // Get API base URL (without /api) to access admin assets
  const getApiBaseUrl = () => {
    if (typeof window === 'undefined') return 'http://localhost:8000';
    const rawBase = process.env.NEXT_PUBLIC_API_URL || process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000';
    return rawBase.replace(/\/api$/, '').replace(/\/$/, '');
  };

  const defaultImagePath = `${getApiBaseUrl()}/public/assets/img/default_product.png`;
  const defaultBrandImagePath = `${getApiBaseUrl()}/public/assets/img/default_brand.png`;

  const handleImageError = (e: React.SyntheticEvent<HTMLImageElement, Event>) => {
    const target = e.currentTarget;
    if (target.src !== defaultImagePath && !target.src.includes('default_product.png')) {
      target.src = defaultImagePath;
    }
  };

  const handleBrandImageError = (e: React.SyntheticEvent<HTMLImageElement, Event>) => {
    const target = e.currentTarget;
    if (target.src !== defaultBrandImagePath && !target.src.includes('default_brand.png')) {
      target.src = defaultBrandImagePath;
    }
  };

  const depthColors = [
    "bg-[#E9007F]", // depth 0 - Deep Pink
    "bg-[#E2EFFF]", // depth 1 - Light Blue
    "bg-[#F3F4F6]", // depth 2 - Light Gray
    "bg-gray-100", // depth 3
    "bg-gray-50", // depth 4
    "", // depth 5+
  ];

  if (node.is_special == 1) {
    var bgClass = "bg-[#E9007F]";
  } else {
    var bgClass = depthColors[Math.min(depth, depthColors.length - 1)];
  }

  const buttonClasses = depth === 0
    ? `w-full h-[46px] mx-auto ${bgClass} flex items-center justify-between pl-[14px] pr-[14px] rounded-[6px] font-bold`
    : `w-full h-[52px] mx-auto ${bgClass} flex items-center justify-between pl-[10px] pr-[14px] rounded-[6px] font-bold`;

  const nameTextColorClass = depth === 0 || node.is_special === 1 ? "text-white text-[15.5px]" : "text-[#1E293B] text-[15.5px]";
  const iconColorClass = depth === 0 || node.is_special === 1 ? "text-white opacity-100" : "text-[#4A90E5] opacity-100";

  return (
    <div className="space-y-0 relative pb-[6px]">
      <button onClick={() => togglePath(path, depth === 0)} className={`${buttonClasses} hover:cursor-pointer transition-colors relative z-10`}>
        <div className={`flex items-center gap-3`}>
          {hasProducts && (
            <div className="w-[42px] h-[42px] bg-white rounded-md border border-[#DCE1EE] overflow-hidden flex items-center justify-center p-0.5 offer-products">
              <img src={node?.image || defaultBrandImagePath} alt="" className="w-[36px] h-[36px] object-contain" onError={handleBrandImageError} />
            </div>
          )}
          <span className={`font-bold ${nameTextColorClass}`}>{node.name}</span>
          {/* Render tags if provided */}
          {(() => {
            const raw = node.tags;
            const tags = Array.isArray(raw) ? raw : typeof raw === "string" && raw.trim().length ? raw.split(",").map((t) => t.trim()).filter(Boolean) : [];
            if (!tags.length) return null;
            return (
              <div className="flex items-center gap-1">
                {tags.map((tag, idx) => (
                  <span key={`${tag}-${idx}`} className="text-white bg-[#0AB386] text-[10px] font-black tracking-widest px-[6px] py-[2px] rounded-[4px] ml-1 uppercase">{tag}</span>
                ))}
              </div>
            );
          })()}
          {node.badge && <Badge className={`${node.badgeColor} text-white bg-[#0AB386] text-[10px] font-black tracking-widest px-[6px] py-[2px] rounded-[4px] ml-1 uppercase`}>{node.badge}</Badge>}
        </div>
        {isOpen ? <ChevronUp className={`w-6 h-6 ${iconColorClass}`} /> : <ChevronDown className={`w-6 h-6 ${iconColorClass}`} />}
      </button>

      {isOpen && (
        <div className="space-y-3 mt-2">
          {hasChildren &&
            node.subcategories!.map((child) => {
              const childPath = `${path}::${child.name}`;
              return <CategoryNode key={childPath} node={child} path={childPath} depth={depth + 1} expandedPaths={expandedPaths} togglePath={togglePath} cart={cart} onIncrement={onIncrement} onDecrement={onDecrement} isFavorite={isFavorite} onToggleFavorite={onToggleFavorite} cartQuantities={cartQuantities} topAncestorIsSpecial={topAncestorIsSpecial} />;
            })}

          {hasProducts && (
            <div className="grid grid-cols-3 w-[370px] gap-[5px] pb-[16px] mx-auto">
              {node.products!.map((product) => {
                const stock = Number((product as any)?.quantity ?? (product as any)?.available_qty ?? 0);
                const allowOutOfStock = Boolean((product as any)?.allow_out_of_stock);
                const isOut = !allowOutOfStock && stock <= 0;
                return (
                  <div key={product.id} className="group bg-white border border-[#E9ECF4] rounded-[10px] overflow-hidden shadow-[0_2px_4px_0_rgba(0,0,0,0.02)] flex flex-col w-[115px] h-[222px] relative mx-auto">
                    <div className="relative h-[105px] bg-white flex items-center justify-center pt-2 pb-1 px-1">
                      <img src={product.image || defaultImagePath} alt={product.name} className="w-full h-[95px] object-contain mix-blend-multiply" onError={handleImageError} />
                      {/* Heart Icon Top Right */}
                      <button type="button" onClick={(e) => { e.preventDefault(); e.stopPropagation(); void onToggleFavorite(product); }} className="absolute top-[4px] right-[4px] w-[22px] h-[22px] rounded-full border border-[#C7CFDE] flex items-center justify-center bg-[#EEF2F8] shadow-sm z-10 transition-colors cursor-pointer">
                        <Heart className={`w-[13px] h-[13px] ${isFavorite(product.id) ? "text-[#35D6EC] fill-[#35D6EC]" : "text-[#5B667E]"}`} strokeWidth={3} />
                      </button>

                      {/* Plus / Cart Floating Widget overlapping bottom boundary */}
                      <div className="absolute right-[6px] -bottom-[12px] z-10">
                        {(() => {
                          if (cartQuantities[product.id]) {
                            return (
                              <div className="flex bg-[#1E2A44] rounded-full shadow-md items-center h-[26px] px-[2px] gap-[1px]">
                                <button type="button" onClick={(e) => { e.preventDefault(); e.stopPropagation(); void onDecrement(product); }} className="w-[22px] h-[22px] text-[#4A90E5] flex items-center justify-center cursor-pointer">
                                  <Minus className="w-[14px] h-[14px]" strokeWidth={3} />
                                </button>
                                <span className="text-white text-[10px] font-bold min-w-[14px] text-center">{cartQuantities[product.id]}</span>
                                <button type="button" onClick={(e) => { e.preventDefault(); e.stopPropagation(); void onIncrement(product); }} className="w-[22px] h-[22px] text-[#4A90E5] flex items-center justify-center cursor-pointer">
                                  <Plus className="w-[14px] h-[14px]" strokeWidth={3} />
                                </button>
                              </div>
                            )
                          }
                          return (
                            <button type="button" disabled={isOut} onClick={(e) => { e.preventDefault(); e.stopPropagation(); if (!isOut) void onIncrement(product); }} className={`w-[26px] h-[26px] bg-[#1E2A44] border-[1.5px] border-[#35D6EC] shadow-md rounded-full flex items-center justify-center cursor-pointer ${isOut ? "opacity-50 pointer-events-none" : ""}`}>
                              <Plus className="w-4 h-4 text-[#35D6EC]" strokeWidth={3} />
                            </button>
                          )
                        })()}
                      </div>
                    </div>

                    <div className="flex-1 flex flex-col pt-4 px-[8px] pb-1 relative bg-white overflow-hidden">
                      <div className="h-[24px] -mx-[8px] px-[6px] mb-[4px] bg-[#EAF0FA] flex items-center justify-between">
                        <span className="font-bold text-[13px] text-[#131A44] leading-none">{symbol}{product.price}</span>
                        {typeof product.wallet_credit === "number" && (
                          <span className="inline-flex items-center gap-[2px] text-[#4A90E5] font-semibold text-[8px] whitespace-nowrap">
                            <Wallet className="w-[10px] h-[10px] opacity-90" strokeWidth={2.2} />
                            <span>{symbol}{product.wallet_credit.toFixed(2)}</span>
                          </span>
                        )}
                      </div>
                      <span className="text-[10px] text-[#64748B] font-bold leading-[1.3] uppercase tracking-tight" style={{ display: "-webkit-box", WebkitLineClamp: 3, WebkitBoxOrient: "vertical", overflow: "hidden" }}>
                        {product.name}
                      </span>
                    </div>

                    <button type="button" className="w-full h-[32px] bg-[#4A90E5] text-white text-[10px] font-bold flex items-center justify-center gap-1.5 transition-colors mt-auto flex-shrink-0 cursor-pointer">
                      <RefreshCw className="w-3 h-3 opacity-90" strokeWidth={2.5} />
                      Quick View
                    </button>
                  </div>
                )
              })}
            </div>
          )}
        </div>
      )}
    </div>
  );
}
