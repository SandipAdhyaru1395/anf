<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
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

    protected function recalcCartTotals(int $cartId): void
    {
        $items = CartItem::where('cart_id', $cartId)->get();

        // Reprice each item using the latest product price before totaling
        $subtotal = 0.0;
        $units = 0;
        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            $currentUnit = $product ? (float) $product->price : (float) $item->unit_price;
            $quantity = (int) $item->quantity;
            $line = $currentUnit * $quantity;
            if ((float) $item->unit_price !== $currentUnit || (float) $item->line_total !== $line) {
                $item->unit_price = $currentUnit;
                $item->line_total = $line;
                $item->save();
            }
            $subtotal += $line;
            $units += $quantity;
        }
        $skus = (int) $items->count();
        $totalDiscount = 0; // extend later
        $total = $subtotal - $totalDiscount;
        Cart::where('id', $cartId)->update([
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
            // Ensure totals and line prices reflect latest product prices on every GET
            $this->recalcCartTotals($cart->id);
            $cart->refresh();
            $items = CartItem::where('cart_id', $cart->id)->get()->map(function (CartItem $item) use (&$walletCreditTotal) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $walletCreditTotal += ((float) ($product->wallet_credit ?? 0)) * ((int) $item->quantity);
                }
                return [
                    'product_id' => $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                    'product' => $product ? [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image_url ?? null,
                        'price' => $product->price,
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
        $unit = (float) $product->price;
        $item = CartItem::where('cart_id', $cartId)->where('product_id', $product->id)->first();
        if ($item) {
            $newQty = (int) $item->quantity + $qty;
            // Stock check: if product has finite stock and requested exceeds available, warn
            if (isset($product->stock_quantity) && $product->stock_quantity !== null) {
                if ($newQty > (int) $product->stock_quantity) {
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
            if (isset($product->stock_quantity) && $product->stock_quantity !== null) {
                if ($qty > (int) $product->stock_quantity) {
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
        $this->recalcCartTotals($cartId);
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
                $unit = (float) $item->unit_price;
                $item->quantity = $newQty;
                $item->line_total = $unit * $newQty;
                $item->save();
            }
            $this->recalcCartTotals($cart->id);
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
        $unit = (float) $product->price;
        if ($data['quantity'] === 0) {
            CartItem::where('cart_id', $cartId)->where('product_id', $product->id)->delete();
        } else {
            if (isset($product->stock_quantity) && $product->stock_quantity !== null) {
                if ((int)$data['quantity'] > (int) $product->stock_quantity) {
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
        $this->recalcCartTotals($cartId);
        return $this->get($request);
    }

    public function clear(Request $request)
    {
        $customer = $request->user();
        $cart = Cart::where('customer_id', $customer->id)->first();
        if ($cart) {
            CartItem::where('cart_id', $cart->id)->delete();
            $this->recalcCartTotals($cart->id);
        }
        return $this->get($request);
    }
}


