
"use client";

import React, { useEffect, useMemo, useState } from "react";
import { Search, Filter, X, ShoppingBag, ChevronDown, ChevronUp, Plus, Minus, Star, Home, User, Heart, Eye, Scan, SlidersHorizontal } from "lucide-react";
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

  const extractApiErrorMessage = (error: unknown, fallback: string) => {
    const err = error as {
      message?: string;
      response?: { data?: { message?: string; error?: string } };
    };
    return err?.response?.data?.message || err?.response?.data?.error || err?.message || fallback;
  };

  const handleIncrement = async (product: ProductItem) => {
    try {
      const parsedStep = Number(product?.step_quantity);
      const step = Number.isFinite(parsedStep) && parsedStep > 0 ? Math.max(1, Math.floor(parsedStep)) : 1;
      const productId = Number(product?.id);
      if (!Number.isFinite(productId) || productId <= 0) {
        toast({ title: 'Failed to add to cart', description: 'Invalid product id', variant: 'destructive' });
        return;
      }
      const allowOutOfStock = Boolean((product as any)?.allow_out_of_stock);
      const { known: stockKnown, stock } = getProductStockInfo(product);
      const current = Number(cartQuantities[product.id] || 0);
      if (!allowOutOfStock && stockKnown && stock > 0 && current + step > stock) {
        toast({ title: 'Quantity not available', description: `Only ${stock} in stock`, variant: 'destructive' });
        return;
      }
      const res = await api.post('/cart/add', { product_id: productId, quantity: step });
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
    } catch (e: unknown) {
      toast({
        title: 'Failed to add to cart',
        description: extractApiErrorMessage(e, 'Please try again'),
        variant: 'destructive',
      });
    }
  };

  const handleDecrement = async (product: ProductItem) => {
    try {
      const parsedStep = Number(product?.step_quantity);
      const step = Number.isFinite(parsedStep) && parsedStep > 0 ? Math.max(1, Math.floor(parsedStep)) : 1;
      const productId = Number(product?.id);
      if (!Number.isFinite(productId) || productId <= 0) return;
      const current = Number(cartQuantities[product.id] || 0);
      // Calculate new quantity after decrement
      const nextQty = Math.max(0, current - step);
      const decrementQty = current > 0 ? step : 0;

      if (decrementQty === 0) return;

      const res = await api.post('/cart/decrement', { product_id: productId, quantity: decrementQty });
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

  /** Header: #F8F7FC, logo left, search row; shadow 0 5 15 #555E58 9% */
  const shopTopChrome = (
    <div className="sticky top-0 z-[60] w-full shrink-0 bg-[#F8F7FC] shadow-[0_5px_15px_0_rgba(85,94,88,0.09)]">
      <div
        className="mx-auto flex w-full max-w-[402px] flex-col gap-4 px-4 pb-2 pt-8"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <div className="flex min-h-0 w-full max-w-[370px] flex-col items-stretch justify-between gap-4">
          <div className="flex w-full justify-start">
            <Thumbnail
              height={SHOP_LOGO_BOX.height}
              containerClassName="max-w-[168.8212432861328px] !bg-transparent"
              imgClassName="object-left object-contain"
            />
          </div>
          {/* Pill search (filter inside only) + separate circular scan — Figma */}
          <div className="flex w-full items-center gap-2">
            <div className="relative box-border flex h-[35px] min-h-[35px] min-w-0 flex-1 items-center justify-between gap-2 rounded-full border border-[#D0D5EB] bg-[#EAECF7] py-0 pl-4 pr-3">
              <div className="flex min-w-0 flex-1 items-center gap-2">
                <Search className="h-4 w-4 shrink-0 text-[#64748B]" strokeWidth={2.25} aria-hidden />
                <Input
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  readOnly={isLoading}
                  aria-busy={isLoading}
                  className="min-h-0 w-full min-w-0 border-none bg-transparent p-0 text-[14px] leading-none text-[#3D495E] shadow-none placeholder:text-[#8A94A6] focus-visible:ring-0 disabled:cursor-wait disabled:opacity-90"
                  style={{ border: "none", boxShadow: "none" }}
                  placeholder="Search products, brands, SKUs..."
                />
              </div>
              <div className="flex shrink-0 items-center gap-1.5">
                {!isLoading && searchQuery ? (
                  <button type="button" onClick={() => setSearchQuery("")} className="flex h-6 w-6 items-center justify-center rounded-full text-[#64748B] hover:bg-[#DCE1F0]" aria-label="Clear search">
                    <X className="h-3.5 w-3.5" strokeWidth={2.5} />
                  </button>
                ) : null}
                <SlidersHorizontal className={`h-[18px] w-[18px] shrink-0 text-[#64748B] ${isLoading ? "pointer-events-none" : "cursor-pointer"}`} strokeWidth={2.25} aria-hidden />
              </div>
            </div>
            <button
              type="button"
              className="box-border flex h-[35px] w-[35px] shrink-0 items-center justify-center rounded-full border border-[#D0D5EB] bg-[#EAECF7] text-[#64748B] transition-colors hover:bg-[#E0E4F5]"
              aria-label="Scan"
            >
              <Scan className="h-[18px] w-[18px]" strokeWidth={2.25} />
            </button>
          </div>
        </div>
      </div>
    </div>
  );

  if (isLoading) {
    return (
      <div className="mx-auto flex min-h-screen w-full max-w-[402px] flex-col bg-[#FAFBFD]">
        {shopTopChrome}
        <div className="flex flex-1 flex-col items-center justify-center px-6 pb-[100px]">
          {companyLogoSrc ? (
            <img src={companyLogoSrc} alt="" className="h-auto w-[200px] max-w-[85%] object-contain animate-pulse" />
          ) : (
            <div className="h-24 w-48 max-w-[85%] rounded-lg bg-[#F3F4F9] animate-pulse" />
          )}
        </div>
        <nav
          className="fixed bottom-0 left-1/2 z-50 box-border flex h-[64px] w-full max-w-[402px] -translate-x-1/2 flex-col rounded-t-[10px] bg-white px-[43px] pb-4 pt-2 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)]"
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
                <span className="text-center text-[10px] font-bold leading-none tracking-normal">Dashboard</span>
              </button>
              <button
                type="button"
                onClick={() => onNavigate("shop", false)}
                className={`flex h-full min-w-0 flex-col items-center justify-center gap-1 ${!showFavorites ? "text-[#4A90E5]" : "opacity-60 text-[#BDC7DE]"}`}
                aria-current={!showFavorites ? "page" : undefined}
              >
                <FontAwesomeIcon
                  icon={faShop}
                  className={`shrink-0 leading-none text-[#4A90E5] ${!showFavorites ? "h-[23px] w-[23px] text-[23px]" : "h-[20px] w-[20px] text-[20px] text-[#BDC7DE]"}`}
                  aria-hidden
                />
                <span className={`text-center text-[11px] leading-none tracking-normal ${!showFavorites ? "inline-flex h-[13px] min-w-[28px] items-center justify-center font-medium" : "text-[10px] font-bold"}`}>
                  Shop
                </span>
              </button>
              <button
                type="button"
                onClick={() => onNavigate("shop", true)}
                className={`flex h-full min-w-0 flex-col items-center justify-center gap-1 ${showFavorites ? "text-[#4A90E5]" : "opacity-60 text-[#BDC7DE]"}`}
                aria-current={showFavorites ? "page" : undefined}
              >
                <FontAwesomeIcon
                  icon={faHeart}
                  className={`shrink-0 leading-none ${showFavorites ? "h-[23px] w-[23px] text-[23px] text-[#4A90E5]" : "h-[20px] w-[20px] text-[20px] text-[#BDC7DE]"}`}
                  aria-hidden
                />
                <span className={`text-center text-[11px] leading-none tracking-normal ${showFavorites ? "inline-flex h-[13px] min-w-[46px] items-center justify-center font-medium" : "text-[10px] font-bold"}`}>
                  Favourites
                </span>
              </button>
              <button type="button" onClick={() => onNavigate("wallet")} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
                <FontAwesomeIcon icon={faWallet} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
                <span className="text-center text-[10px] font-bold leading-none tracking-normal">Wallet</span>
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

  return (
    <div className="mx-auto flex min-h-screen w-full max-w-[402px] flex-col bg-[#FAFBFD]">
      {shopTopChrome}

      {/* Category Menu: 370px column, 8px vertical gap; frame 402 + px-4 */}
      <div className="mx-auto min-h-0 w-full max-w-[402px] flex-1 overflow-y-auto px-4 pb-[150px] pt-4">
        <div className="mx-auto w-full max-w-[370px] space-y-2">
          {displayedCategories.map((node) => (
            <CategoryNode key={node.name} node={node} path={node.name} depth={0} expandedPaths={expandedPaths} togglePath={togglePath} cart={cart} onIncrement={handleIncrement} onDecrement={handleDecrement} isFavorite={isFavorite} onToggleFavorite={toggleFavorite} cartQuantities={cartQuantities} topAncestorIsSpecial={node.is_special === 1} />
          ))}
        </div>
      </div>

      {/* Mobile Sticky Cart — Figma: 402×60 hug, py-12 px-8, gradient #E8E8ED→#F4F2F9, shadow up; space-between */}
      <div
        className="fixed bottom-[64px] left-1/2 z-40 box-border flex min-h-[60px] w-full max-w-[402px] -translate-x-1/2 items-center bg-gradient-to-b from-[#E8E8ED] to-[#F4F2F9] px-2 py-3 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)]"
        aria-label="Mobile sticky cart"
      >
        <div className="flex min-h-0 w-full items-center gap-2">
          <div className="flex min-w-0 flex-col justify-center">
            <div
              className="flex min-w-0 items-center whitespace-nowrap"
              style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
            >
              <span className="inline-flex h-[15px] items-center text-[13px] font-bold leading-none tracking-normal text-[#3D495E]">
                {totals.units} Units
              </span>
              <span className="mx-1 h-[11px] w-px shrink-0 self-center bg-[#D2D0E1]" aria-hidden />
              <span className="inline-flex h-[15px] items-center text-[13px] font-bold leading-none tracking-normal text-[#3D495E]">
                {totals.skus} SKUs
              </span>
              <span className="mx-1 h-[11px] w-px shrink-0 self-center bg-[#D2D0E1]" aria-hidden />
              <span className="inline-flex h-[15px] shrink-0 items-center text-[13px] font-bold leading-none tracking-normal text-[#3D495E]">
                {symbol}
                {totals.total.toFixed(2)}
              </span>
              <span className="mx-1 h-[11px] w-px shrink-0 self-center bg-[#D2D0E1]" aria-hidden />
              <span className="inline-flex h-[15px] shrink-0 items-center gap-[5px] text-[13px] font-bold leading-none tracking-normal text-[#4A90E5]">
                <FontAwesomeIcon icon={faWallet} className="text-[12px] opacity-90" aria-hidden />
                <span>+{symbol}{totalWalletCredit.toFixed(2)}</span>
              </span>
            </div>
            <p
              className="mt-1 w-full text-center text-[12px] font-normal leading-none tracking-[0.05em] text-[#68676E]"
              style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
            >
              Includes FREE delivery
            </p>
          </div>

          <button
            type="button"
            onClick={() => onNavigate("basket")}
            className="ml-auto box-border flex h-[35px] w-[116px] max-w-[300px] shrink-0 items-center justify-center rounded-[8px] bg-[#4A90E5] p-[8px] text-center text-[16px] font-[700] leading-none tracking-normal text-white shadow-sm transition-colors hover:bg-[#3d7fd4] active:bg-[#3570c2]"
            style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
          >
            View Basket
          </button>
        </div>
      </div>

      {/* Bottom nav — Figma Mobile Navigation Bar; Shop or Favourites active */}
      <nav
        className="fixed bottom-0 left-1/2 z-50 box-border flex h-[64px] w-full max-w-[402px] -translate-x-1/2 flex-col rounded-t-[10px] bg-white px-[43px] pb-4 pt-2 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)]"
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
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Dashboard</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", false)}
              className={`flex h-full min-w-0 flex-col items-center justify-center gap-1 ${!showFavorites ? "text-[#4A90E5]" : "opacity-60 text-[#BDC7DE]"}`}
              aria-current={!showFavorites ? "page" : undefined}
            >
              <FontAwesomeIcon
                icon={faShop}
                className={`shrink-0 leading-none ${!showFavorites ? "h-[23px] w-[23px] text-[23px] text-[#4A90E5]" : "h-[20px] w-[20px] text-[20px] text-[#BDC7DE]"}`}
                aria-hidden
              />
              <span className={`text-center text-[11px] leading-none tracking-normal ${!showFavorites ? "inline-flex h-[13px] min-w-[28px] items-center justify-center font-medium text-[#4A90E5]" : "text-[10px] font-bold"}`}>
                Shop
              </span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", true)}
              className={`flex h-full min-w-0 flex-col items-center justify-center gap-1 ${showFavorites ? "text-[#4A90E5]" : "opacity-60 text-[#BDC7DE]"}`}
              aria-current={showFavorites ? "page" : undefined}
            >
              <FontAwesomeIcon
                icon={faHeart}
                className={`shrink-0 leading-none ${showFavorites ? "h-[23px] w-[23px] text-[23px] text-[#4A90E5]" : "h-[20px] w-[20px] text-[20px] text-[#BDC7DE]"}`}
                aria-hidden
              />
              <span className={`text-center text-[11px] leading-none tracking-normal ${showFavorites ? "inline-flex h-[13px] min-w-[46px] items-center justify-center font-medium text-[#4A90E5]" : "text-[10px] font-bold"}`}>
                Favourites
              </span>
            </button>
            <button type="button" onClick={() => onNavigate("wallet")} className="flex h-full min-w-0 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]">
              <FontAwesomeIcon icon={faWallet} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none tracking-normal">Wallet</span>
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
    "bg-[#ED008C]", // depth 0 — Figma "New In" magenta
    "bg-[#E2EFFF]", // depth 1 — light blue
    "border border-[#E6E8F2] bg-white", // depth 2 — Figma Subbrand: white + hairline border
    "bg-gray-100",
    "bg-gray-50",
    "",
  ];

  if (node.is_special == 1) {
    var bgClass = "bg-[#ED008C]";
  } else {
    var bgClass = depthColors[Math.min(depth, depthColors.length - 1)];
  }

  /* Figma Category bar: 370 fill, min-h 49, py 7 px 16, inner gap 10, radius 10 */
  const buttonClasses = `w-full min-h-[49px] mx-auto ${bgClass} flex items-center justify-between gap-[10px] py-[7px] px-4 rounded-[10px] font-bold shadow-sm`;

  const nameTextColorClass = depth === 0 || node.is_special === 1 ? "text-white text-[15.5px]" : "text-[#1E293B] text-[15.5px]";
  const iconColorClass = depth === 0 || node.is_special === 1 ? "text-white opacity-100" : "text-[#4A90E5] opacity-100";

  return (
    <div className="relative space-y-0 pb-2">
      <button onClick={() => togglePath(path, depth === 0)} className={`${buttonClasses} relative z-10 cursor-pointer transition-colors hover:opacity-[0.98]`}>
        <div className="flex min-w-0 items-center gap-[10px]">
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
        {isOpen ? <ChevronUp className={`h-6 w-6 shrink-0 ${iconColorClass}`} strokeWidth={2.5} /> : <ChevronDown className={`h-6 w-6 shrink-0 ${iconColorClass}`} strokeWidth={2.5} />}
      </button>

      {isOpen && (
        <div className="mt-2 space-y-2">
          {hasChildren &&
            node.subcategories!.map((child) => {
              const childPath = `${path}::${child.name}`;
              return <CategoryNode key={childPath} node={child} path={childPath} depth={depth + 1} expandedPaths={expandedPaths} togglePath={togglePath} cart={cart} onIncrement={onIncrement} onDecrement={onDecrement} isFavorite={isFavorite} onToggleFavorite={onToggleFavorite} cartQuantities={cartQuantities} topAncestorIsSpecial={topAncestorIsSpecial} />;
            })}

          {hasProducts && (
            <div className="mx-auto grid w-full grid-cols-3 gap-2 pb-4">
              {node.products!.map((product) => {
                const stock = Number((product as any)?.quantity ?? (product as any)?.available_qty ?? 0);
                const allowOutOfStock = Boolean((product as any)?.allow_out_of_stock);
                const isOut = !allowOutOfStock && stock <= 0;
                return (
                  <div key={product.id} className="group relative mx-auto flex h-[222px] w-full min-w-0 max-w-[118px] flex-col overflow-hidden rounded-[10px] border border-[#E9ECF4] bg-white shadow-[0_2px_4px_0_rgba(0,0,0,0.02)]">
                    <div className="relative flex h-[105px] items-center justify-center bg-white px-1 pb-1 pt-2">
                      {/* Image clicks do not add to cart — only explicit + button */}
                      <img src={product.image || defaultImagePath} alt={product.name} className="pointer-events-none h-[95px] w-full object-contain mix-blend-multiply" draggable={false} onError={handleImageError} />
                      <button
                        type="button"
                        onClick={(e) => {
                          e.preventDefault();
                          e.stopPropagation();
                          void onToggleFavorite(product);
                        }}
                        className={`pointer-events-auto absolute right-[4px] top-[4px] z-10 flex h-[32px] w-[32px] cursor-pointer items-center justify-center rounded-full border bg-white transition-colors ${isFavorite(product.id) ? "border-[#4A90E5]" : "border-[#D1D5DB]"}`}
                        aria-label={isFavorite(product.id) ? "Remove from favourites" : "Add to favourites"}
                      >
                        <Heart
                          className="h-[15px] w-[15px] shrink-0"
                          fill={isFavorite(product.id) ? "#4A90E5" : "#D1D5DB"}
                          color={isFavorite(product.id) ? "#4A90E5" : "#D1D5DB"}
                          strokeWidth={0}
                        />
                      </button>

                      <div className="pointer-events-auto absolute -bottom-[12px] right-[6px] z-10">
                        {(() => {
                          if (cartQuantities[product.id]) {
                            return (
                              <div className="flex h-[26px] items-center gap-px rounded-full bg-[#1E2A44] px-[2px] shadow-md">
                                <button type="button" onClick={(e) => { e.preventDefault(); e.stopPropagation(); void onDecrement(product); }} className="flex h-[22px] w-[22px] cursor-pointer items-center justify-center text-[#4A90E5]">
                                  <Minus className="h-[14px] w-[14px]" strokeWidth={3} />
                                </button>
                                <span className="min-w-[14px] text-center text-[10px] font-bold text-white">{cartQuantities[product.id]}</span>
                                <button type="button" onClick={(e) => { e.preventDefault(); e.stopPropagation(); void onIncrement(product); }} className="flex h-[22px] w-[22px] cursor-pointer items-center justify-center text-[#4A90E5]" aria-label="Add to basket">
                                  <Plus className="h-[14px] w-[14px]" strokeWidth={3} />
                                </button>
                              </div>
                            );
                          }
                          return (
                            <button
                              type="button"
                              disabled={isOut}
                              onClick={(e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                if (!isOut) void onIncrement(product);
                              }}
                              className={`flex h-[26px] w-[26px] cursor-pointer items-center justify-center rounded-full bg-[#4A90E5] shadow-md ring-1 ring-[#4A90E5]/35 ${isOut ? "pointer-events-none opacity-50" : ""}`}
                              aria-label="Add to basket"
                            >
                              <Plus className="h-3.5 w-3.5 text-white" strokeWidth={3} />
                            </button>
                          );
                        })()}
                      </div>
                    </div>

                    <div className="flex-1 flex flex-col pt-4 px-[8px] pb-1 relative bg-white overflow-hidden">
                      <div className="mb-[4px] flex h-[24px] min-h-[24px] -mx-[8px] items-center justify-between gap-1 bg-[#EAF0FA] px-[6px]">
                        <span className="truncate font-bold text-[13px] leading-none text-[#131A44]">
                          {symbol}
                          {product.price}
                        </span>
                        <span className="flex min-w-0 shrink-0 items-center gap-1">
                          {product.rrp != null && String(product.rrp).trim() !== "" && (
                            <span className="inline-flex max-w-[48px] items-center gap-0.5 truncate text-[9px] font-semibold text-[#4A90E5] line-through decoration-[#4A90E5] decoration-1">
                              <ShoppingBag className="h-2.5 w-2.5 shrink-0 opacity-90" strokeWidth={2.2} aria-hidden />
                              <span className="truncate">
                                {symbol}
                                {product.rrp}
                              </span>
                            </span>
                          )}
                          {typeof product.wallet_credit === "number" && (
                            <span className="inline-flex items-center gap-[2px] whitespace-nowrap text-[8px] font-semibold text-[#4A90E5]">
                              <ShoppingBag className="h-2.5 w-2.5 shrink-0 opacity-90" strokeWidth={2.2} aria-hidden />
                              <span>
                                {symbol}
                                {product.wallet_credit.toFixed(2)}
                              </span>
                            </span>
                          )}
                        </span>
                      </div>
                      <span
                        className="text-[10px] font-bold uppercase leading-[1.3] tracking-tight text-[#1E293B]"
                        style={{ display: "-webkit-box", WebkitLineClamp: 3, WebkitBoxOrient: "vertical", overflow: "hidden" }}
                      >
                        {product.name}
                      </span>
                    </div>

                    <button
                      type="button"
                      onClick={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                      }}
                      className="mt-auto flex min-h-[26px] w-full shrink-0 cursor-pointer items-center justify-center gap-1 bg-[#4A90E5] px-2 py-1.5 text-[9px] font-normal leading-none text-white shadow-[0_2px_6px_rgba(74,144,229,0.25)] transition-colors hover:bg-[#3d7fd4]"
                    >
                      <Eye className="h-2.5 w-2.5 shrink-0 opacity-95" strokeWidth={2.5} aria-hidden />
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
