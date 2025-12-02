"use client";

import { useEffect, useMemo, useState } from "react";
import { Search, Filter, X, ShoppingBag, ChevronDown, ChevronUp, Plus, Minus, Star, Home, Wallet, User, Bell } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { useToast } from "@/hooks/use-toast";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faStar, faSearch } from "@fortawesome/free-solid-svg-icons";
import api from "@/lib/axios";

interface MobileShopProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account", favorites?: boolean) => void;
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
  discount?: string;
  step_quantity?: number;
  wallet_credit?: number;
  quantity?: number;
  stock_quantity?: number;
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

import { useCurrency } from "@/components/currency-provider";
import { useCustomer } from "@/components/customer-provider";
import { Banner } from "@/components/banner";
import { startLoading, stopLoading } from "@/lib/loading";

export function MobileShop({ onNavigate, cart, increment, decrement, totals, showFavorites = false }: MobileShopProps) {
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
      } catch {}
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
            } catch {}

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
              try { sessionStorage.setItem("products_cache", JSON.stringify({ version: productVersion, categories: deduped })); } catch {}
            }
          } catch {}
        })();
      }
    } catch {}
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
      } catch {}
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
                const stock = Number((p as any)?.quantity ?? (p as any)?.stock_quantity ?? 0);
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
      // Front check against stock if provided in product payload
      const stock = Number((product as any)?.quantity ?? (product as any)?.stock_quantity ?? 0);
      const current = Number(cartQuantities[product.id] || 0);
      if (stock > 0 && current + step > stock) {
        toast({ title: 'Quantity not available', description: `Only ${stock} in stock`, variant: 'destructive' });
        return;
      }
      const res = await api.post('/cart/add', { product_id: product.id, quantity: step });
      if (res?.data && res.data.success === false) {
        const msg = res.data.message || 'Requested quantity is not available';
        toast({ title: 'Quantity not available', description: msg, variant: 'destructive' });
        return;
      }
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

  return (
    <div className="min-h-screen flex flex-col w-full max-w-[1000px] mx-auto">
      {/* Header */}
      <div className="h-[50px] bg-white flex items-center border-b">
        <div className="w-[66px] h-[25px] flex items-center justify-center">
          {showFavorites ? <FontAwesomeIcon icon={faShop} className="text-green-600" style={{ width: "30px", height: "24px" }} /> : <FontAwesomeIcon icon={faShop} className="text-green-600" style={{ width: "30px", height: "24px" }} />}
        </div>
        <h1 className="text-[16px] font-semibold">Shop</h1>
      </div>

      {/* Search Bar */}
      <div className="bg-white border-b box-shadow-bottom mb-2 sticky top-0 z-50 h-[50px]">
        <div className="relative flex items-center pt-[7px] pb-[6px]">
          <div className="flex-1 relative">
            <FontAwesomeIcon icon={faSearch} className="text-green-600 absolute top-1/2 transform -translate-y-1/2" style={{ width: "24px", height: "24px" }} />
            <Input value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="pl-[32px] pr-9 bg-transparent shadow-none border-none focus:border-none focus:ring-0 focus:ring-offset-0 focus:outline-none focus-visible:ring-0 focus-visible:ring-offset-0 focus-visible:outline-none py-0" style={{ border: 'none', outline: 'none', boxShadow: 'none' }} placeholder="Start typing to filter products..." />
            {searchQuery && (
              <button onClick={() => setSearchQuery("")} className="absolute right-3 top-1/2 transform -translate-y-1/2">
                <X className="cursor-pointer w-4 h-4 text-gray-400" />
              </button>
            )}
          </div>
        </div>
      </div>

      {/* Banner */}
      {/* <div className="mt-0">
        <Banner />
      </div> */}

      {/* Categories (recursive) */}
      <div className="space-y-2 overflow-y-auto pb-56">
        {displayedCategories.map((node) => (
          <CategoryNode key={node.name} node={node} path={node.name} depth={0} expandedPaths={expandedPaths} togglePath={togglePath} cart={cart} onIncrement={handleIncrement} onDecrement={handleDecrement} isFavorite={isFavorite} onToggleFavorite={toggleFavorite} cartQuantities={cartQuantities} topAncestorIsSpecial={node.is_special === 1} />
        ))}
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50">
        {/* Basket Summary (shows when items in cart) */}
        {cartTotals.units > 0 && (
          <div className="bg-white border-b px-4 py-2 box-shadow-top">
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
            <div className="text-sm font-semibold text-center text-[#999] py-1">Includes FREE delivery</div>
            <button onClick={() => onNavigate("basket")} className="w-full bg-green-600 text-white py-2 rounded-sm font-semibold hover:cursor-pointer text-lg box-shadow-bottom">
              View Basket
            </button>
          </div>
        )}
        <div className="flex flex-row items-center justify-between h-[72px] footer-nav-col px-[18px]">
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

  // Indentation only: increase left padding by depth
  const depthPad = ["", "", "", "", "", ""];
  // Left margin by depth to visually indent levels
  const depthMargin = ["", "", "", "", "", ""];
  const depthColors = [
    "bg-green-600", // depth 0
    "bg-green-50 !pl-[6px]", // depth 1
    "bg-gray-100", // depth 2
    "", // depth 3
    "", // depth 4
    "", // depth 5+
  ];

  const padClass = depthPad[Math.min(depth, depthPad.length - 1)];
  const marginClass = depthMargin[Math.min(depth, depthMargin.length - 1)];

  if (node.is_special == 1) {
    var bgClass = "bg-yellow-400";
  } else {
    var bgClass = depthColors[Math.min(depth, depthColors.length - 1)];
  }

  const buttonClasses = `w-full ${bgClass} mb-2 flex items-center justify-between ${depth === 0 ? "font-medium" : ""}`;
  // Vertical gap between levels increases with depth
  const depthGap = ["mt-2", "mt-2", "mt-2", "mt-2", "mt-2", "mt-2"];
  const gapClass = depthGap[Math.min(depth, depthGap.length - 1)];

  const nameTextColorClass = depth === 0 && node.is_special !== 1 ? "text-white" : "text-gray-800";
  const iconColorClass = nameTextColorClass;

  return (
    <div className="space-y-2">
      <button onClick={() => togglePath(path, depth === 0)} className={`${buttonClasses} ${marginClass} hover:cursor-pointer h-[50px]`}>
        <div className={`flex items-center gap-2 ${padClass}`}>
          {hasProducts && (
            <div className="w-[42px] h-[42px] bg-white rounded-md border overflow-hidden flex items-center justify-center offer-products">
              <img 
                src={node?.image || defaultBrandImagePath} 
                alt="" 
                className="w-[42px] h-[42px] object-cover" 
                onError={handleBrandImageError}
              />
            </div>
          )}
          <span className={`font-semibold ${nameTextColorClass}`}>{node.name}</span>
          {/* Render tags if provided */}
          {(() => {
            const raw = node.tags;
            const tags = Array.isArray(raw)
              ? raw
              : typeof raw === "string" && raw.trim().length
              ? raw
                  .split(",")
                  .map((t) => t.trim())
                  .filter(Boolean)
              : [];
            if (!tags.length) return null;
            const colors = ["bg-green-600", "bg-orange-500", "bg-red-600"];
            return (
              <div className="flex items-center gap-1">
                {tags.map((tag, idx) => (
                  <span key={`${tag}-${idx}`} className={`text-white text-[13px] leading-none px-4 py-2 rounded-full ${colors[idx % colors.length]}`}>
                    {tag.toUpperCase()}
                  </span>
                ))}
              </div>
            );
          })()}
          {node.badge && <Badge className={`${node.badgeColor} text-white text-xs px-2 py-1`}>{node.badge}</Badge>}
        </div>
        {isOpen ? <ChevronUp className={`w-9 h-9 ${iconColorClass}`} /> : <ChevronDown className={`w-9 h-9 ${iconColorClass}`} />}
      </button>

      {isOpen && (
        <div className={`space-y-3 ${gapClass}`}>
          {hasChildren &&
            node.subcategories!.map((child) => {
              const childPath = `${path}::${child.name}`;
              return <CategoryNode key={childPath} node={child} path={childPath} depth={depth + 1} expandedPaths={expandedPaths} togglePath={togglePath} cart={cart} onIncrement={onIncrement} onDecrement={onDecrement} isFavorite={isFavorite} onToggleFavorite={onToggleFavorite} cartQuantities={cartQuantities} topAncestorIsSpecial={topAncestorIsSpecial} />;
            })}

          {hasProducts && (
            <div className="product-grid-responsive gap-3 px-3">
              {node.products!.map((product) => {
                const stock = Number((product as any)?.quantity ?? (product as any)?.stock_quantity ?? 0);
                const isOut = stock <= 0;
                return (
                  <div key={product.id} className={`bg-white border-b relative pb-2 offer-plus-sign z-10 w-[113px] ${isOut ? 'opacity-60' : ''}`}>
                    {(() => {
                      if (isOut) {
                        return (
                          <div className="offer-increase-sign absolute z-10 right-0 flex items-center justify-center rounded-full w-8 h-8 bg-black">
                            <Bell className="cursor-pointer w-5 h-5 text-green-400" />
                          </div>
                        )
                      }
                      if (cartQuantities[product.id]) {
                        return (
                          <div className="offer-increase-sign absolute z-10 right-0 flex items-center gap-2 bg-black rounded-full px-1 shadow-sm w-[113px]">
                            <button onClick={() => onDecrement(product)} className="w-8 h-8 text-green-500 flex items-center justify-center hover:cursor-pointer">
                              <Minus className="w-6 h-6" />
                            </button>
                            <span className="min-w-[1.5rem] text-center text-lg font-medium text-white">{cartQuantities[product.id]}</span>
                            <button onClick={() => onIncrement(product)} className={`w-8 h-8 text-green-500 flex items-center justify-center hover:cursor-pointer`}>
                              <Plus className="w-6 h-6" />
                            </button>
                          </div>
                        )
                      }
                      return (
                        <button onClick={() => onIncrement(product)} className={`offer-plus-sign z-10 absolute right-0 w-8 h-8 bg-black rounded-full flex items-center justify-center hover:cursor-pointer`}>
                          <Plus className="w-6 h-6 text-green-500" />
                        </button>
                      )
                    })()}

                  <div className="aspect-square mb-2 flex items-center relative justify-center">
                    <img 
                      src={product.image || defaultImagePath} 
                      alt={product.name} 
                      className="w-full h-[113px] object-contain" 
                      onError={handleImageError}
                    />
                    {isOut && (
                      <div className="absolute inset-0 bg-white/60" />
                    )}
                    <div className="absolute right-0 bottom-0">
                      <button onClick={() => onToggleFavorite(product)} className="w-8 h-8 rounded-full border-2 flex items-center justify-center hover:cursor-pointer bg-white" aria-label="Toggle favourite" title="Toggle favourite">
                        <Star className={`w-[16px] h-[16px] ${isFavorite(product.id) ? "text-[#3dbe59] fill-[#3dbe59]" : "text-[#c0d3c4] fill-[#c0d3c4]"}`} />
                      </button>
                    </div>
                  </div>

                  <div className="space-y-1">
                    <div>
                      <span className="font-bold p-[2px] bg-[#f0f5f1] flex justify-between offer-product-price gap-3">
                        {product.price}
                        {typeof product.wallet_credit === "number" && (
                          <span className="inline-flex items-center text-green-600 text-xs font-semibold">
                            <Wallet className="w-3 h-3 mr-1" />
                            <span>
                              {symbol}
                              {product.wallet_credit.toFixed(2)}
                            </span>
                          </span>
                        )}
                      </span>
                    </div>
                    <div className="text-left offer-product-name p-1">
                      <span className="text-sm text-black">{product.name}</span>
                    </div>
                  </div>
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
