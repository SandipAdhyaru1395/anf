<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ProductVolumeDiscount;
use App\Models\ProductVolumeDiscountBreakPrice;
use App\Models\VolumeDiscountBreak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected function getOrCreateCartId(int $customerId): int
    {
        $cart = Cart::firstOrCreate(
            ['customer_id' => $customerId],
            ['subtotal' => 0, 'total_discount' => 0, 'total' => 0, 'units' => 0, 'skus' => 0]
        );
        return (int) $cart->id;
    }

    protected function recalcCartTotals(Cart $cart, Customer $customer): void
    {
        $items = CartItem::where('cart_id', $cart->id)->get();

        // Reprice each item using latest product price and volume discounts before totaling
        $subtotalOriginal = 0.0;
        $subtotalDiscounted = 0.0;
        $units = 0;
        $totalDiscount = 0.0;
        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $baseUnit = $this->getBaseUnitPrice($product, $customer);
                $effectiveUnit = $this->applyVolumeDiscount($product, $customer, (int) $item->quantity, $baseUnit);
            } else {
                $baseUnit = (float) $item->unit_price;
                $effectiveUnit = $baseUnit;
            }
            $quantity = (int) $item->quantity;
            $originalLine = $baseUnit * $quantity;
            $line = $effectiveUnit * $quantity;

            if ((float) $item->unit_price !== $effectiveUnit || (float) $item->line_total !== $line) {
                $item->unit_price = $effectiveUnit;
                $item->line_total = $line;
                $item->save();
            }
            $subtotalOriginal += $originalLine;
            $subtotalDiscounted += $line;
            $units += $quantity;
            $totalDiscount += max(0.0, $originalLine - $line);
        }
        $skus = (int) $items->count();
        // Expose cart subtotal/total as the discounted amounts (what customer pays),
        // and total_discount as the savings compared to original prices.
        $subtotal = $subtotalDiscounted;
        $total = $subtotalDiscounted;
        $cart->update([
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'total' => $total,
            'units' => $units,
            'skus' => $skus,
        ]);
    }

    public function get(Request $request)
    {
        $customer = $request->user();
        $cart = Cart::where('customer_id', $customer->id)->first();
        $items = [];
        $walletCreditTotal = 0.0;
        if ($cart) {
            // Ensure totals and line prices reflect latest product prices and discounts on every GET
            $this->recalcCartTotals($cart, $customer);
            $cart->refresh();
            $items = CartItem::where('cart_id', $cart->id)->get()->map(function (CartItem $item) use (&$walletCreditTotal, $customer) {
                $product = Product::find($item->product_id);
                $baseUnit = $product ? $this->getBaseUnitPrice($product, $customer) : (float) $item->unit_price;
                $effectiveUnit = (float) $item->unit_price;
                if ($product) {
                    $walletCreditTotal += ((float) ($product->wallet_credit ?? 0)) * ((int) $item->quantity);
                }
                $appliedDiscountPct = 0.0;
                if ($baseUnit > 0 && $effectiveUnit < $baseUnit) {
                    $appliedDiscountPct = round((($baseUnit - $effectiveUnit) / $baseUnit) * 100, 2);
                }
                return [
                    'product_id' => $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                    'original_unit_price' => $baseUnit,
                    'applied_discount_percentage' => $appliedDiscountPct,
                    'product' => $product ? [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image_url ?? null,
                        'price' => $baseUnit,
                        'effective_price' => $effectiveUnit,
                        'applied_discount_percentage' => $appliedDiscountPct,
                        'wallet_credit' => $product->wallet_credit,
                        'vat_amount' => $product->vat_amount,
                    ] : null,
                ];
            })->values();
        }
        return response()->json([
            'success' => true,
            'cart' => [
                'subtotal' => (float) ($cart->subtotal ?? 0),
                'total_discount' => (float) ($cart->total_discount ?? 0),
                'total' => (float) ($cart->total ?? 0),
                'units' => (int) ($cart->units ?? 0),
                'skus' => (int) ($cart->skus ?? 0),
                'items' => $items,
                'wallet_credit_total' => (float) $walletCreditTotal,
            ],
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);
        $qty = (int) ($data['quantity'] ?? 1);
        $customer = $request->user();
        $cartId = $this->getOrCreateCartId($customer->id);
        $product = Product::findOrFail($data['product_id']);
        $baseUnit = $this->getBaseUnitPrice($product, $customer);
        $unit = $this->applyVolumeDiscount($product, $customer, $qty, $baseUnit);
        $item = CartItem::where('cart_id', $cartId)->where('product_id', $product->id)->first();
        if ($item) {
            $newQty = (int) $item->quantity + $qty;
            // Stock check: if product has finite stock and requested exceeds available, warn
            if (
                !$product->allow_out_of_stock &&
                isset($product->available_qty) &&
                $product->available_qty !== null
            ) {
                if ($newQty > (int) $product->available_qty) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Requested quantity is not available in stock.',
                    ], 200);
                }
            }
            $item->quantity = $newQty;
            $item->unit_price = $unit; // keep latest unit price
            $item->line_total = $unit * $newQty;
            $item->save();
        } else {
            if (
                !$product->allow_out_of_stock &&
                isset($product->available_qty) &&
                $product->available_qty !== null
            ) {
                if ($qty > (int) $product->available_qty) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Requested quantity is not available in stock.',
                    ], 200);
                }
            }
            CartItem::create([
                'cart_id' => $cartId,
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_price' => $unit,
                'line_total' => $unit * $qty,
            ]);
        }
        $cart = Cart::findOrFail($cartId);
        $this->recalcCartTotals($cart, $customer);
        return $this->get($request);
    }

    public function decrement(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);
        $qty = (int) ($data['quantity'] ?? 1);
        $customer = $request->user();
        $cart = Cart::where('customer_id', $customer->id)->first();
        if (!$cart) return $this->get($request);
        $item = CartItem::where('cart_id', $cart->id)->where('product_id', $data['product_id'])->first();
        if ($item) {
            $newQty = max(0, (int) $item->quantity - $qty);
            if ($newQty === 0) {
                $item->delete();
            } else {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $baseUnit = $this->getBaseUnitPrice($product, $customer);
                        $unit = $this->applyVolumeDiscount($product, $customer, $newQty, $baseUnit);
                    } else {
                        $unit = (float) $item->unit_price;
                    }
                $item->quantity = $newQty;
                $item->line_total = $unit * $newQty;
                $item->save();
            }
                $this->recalcCartTotals($cart, $customer);
        }
        return $this->get($request);
    }

    public function set(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);
        $customer = $request->user();
        $cartId = $this->getOrCreateCartId($customer->id);
        $product = Product::findOrFail($data['product_id']);
        $baseUnit = $this->getBaseUnitPrice($product, $customer);
        $unit = $this->applyVolumeDiscount($product, $customer, (int) $data['quantity'], $baseUnit);
        if ($data['quantity'] === 0) {
            CartItem::where('cart_id', $cartId)->where('product_id', $product->id)->delete();
        } else {
            if (
                !$product->allow_out_of_stock &&
                isset($product->available_qty) &&
                $product->available_qty !== null
            ) {
                if ((int) $data['quantity'] > (int) $product->available_qty) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Requested quantity is not available in stock.',
                    ], 200);
                }
            }
            $item = CartItem::firstOrNew(['cart_id' => $cartId, 'product_id' => $product->id]);
            $item->unit_price = $unit;
            $item->quantity = (int) $data['quantity'];
            $item->line_total = $unit * (int) $data['quantity'];
            $item->save();
        }
        $cart = Cart::findOrFail($cartId);
        $this->recalcCartTotals($cart, $customer);
        return $this->get($request);
    }

    public function clear(Request $request)
    {
        $customer = $request->user();
        $cart = Cart::where('customer_id', $customer->id)->first();
        if ($cart) {
            CartItem::where('cart_id', $cart->id)->delete();
            $this->recalcCartTotals($cart, $customer);
        }
        return $this->get($request);
    }

    protected function getBaseUnitPrice(Product $product, Customer $customer): float
    {
        if (method_exists($product, 'getPrice')) {
            return $product->getPrice($customer);
        }
        return (float) $product->price;
    }

    protected function applyVolumeDiscount(Product $product, Customer $customer, int $quantity, float $baseUnit): float
    {
        if ($quantity <= 0) {
            return $baseUnit;
        }

        $priceListId = $customer->price_list_id ?? null;

        // Exact price list group, fallback to default group for product
        $pvd = null;
        if ($priceListId) {
            $pvd = ProductVolumeDiscount::with('group.breaks')
                ->where('product_id', $product->id)
                ->where('price_list_id', $priceListId)
                ->first();
        }
        if (!$pvd) {
            $pvd = ProductVolumeDiscount::with('group.breaks')
                ->where('product_id', $product->id)
                ->whereNull('price_list_id')
                ->first();
        }
        if (!$pvd || !$pvd->group) {
            return $baseUnit;
        }

        $breaks = $pvd->group->breaks->sortBy('from_quantity');
        $applicable = null;
        foreach ($breaks as $break) {
            if ($quantity > (int) $break->from_quantity) {
                $applicable = $break;
            }
        }
        if (!$applicable) {
            return $baseUnit;
        }
        
        // If admin set an override price for this exact break, use it.
        $override = ProductVolumeDiscountBreakPrice::where('product_id', $product->id)
            ->where('price_list_id', $priceListId)
            ->where('volume_discount_break_id', $applicable->id)
            ->value('override_price');
        if ($override === null) {
            $override = ProductVolumeDiscountBreakPrice::where('product_id', $product->id)
                ->whereNull('price_list_id')
                ->where('volume_discount_break_id', $applicable->id)
                ->value('override_price');
        }
        if ($override !== null && $override !== '') {
            $overrideFloat = (float) $override;
            if ($overrideFloat >= 0) {
                return $overrideFloat;
            }
        }

        $pct = (float) $applicable->discount_percentage;
        if ($pct <= 0) {
            return $baseUnit;
        }


        $discounted = $baseUnit * (1 - ($pct / 100));
        \Illuminate\Support\Facades\Log::info('test', [$discounted]);
        return $discounted >= 0 ? $discounted : $baseUnit;
    }
}


