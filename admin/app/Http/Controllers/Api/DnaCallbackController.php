<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderRef;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use DNAPayments\DNAPayments;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Crypt;
use App\Jobs\SendPlanufacOrderWebhookJob;
use App\Services\OrderConfirmationEmailService;

class DnaCallbackController extends Controller
{
    public function handle(Request $request, OrderConfirmationEmailService $orderConfirmationEmailService)
    {
        $payload = $request->all();

        // Minimal logging only (no card/token details)
        Log::info('DNA callback received', [
            'id'        => $payload['id']        ?? null,
            'invoiceId' => $payload['invoiceId'] ?? null,
            'success'   => $payload['success']   ?? null,
            'errorCode' => $payload['errorCode'] ?? null,
        ]);

        // Verify signature to ensure payload integrity
        $settings = Helpers::setting();
        $secretEnc = $settings['dna_payments_client_secret'] ?? null;
        $secret = null;
        if (is_string($secretEnc) && $secretEnc !== '') {
            try {
                $secret = Crypt::decryptString($secretEnc);
            } catch (\Throwable) {
                $secret = null;
            }
        }
        if (empty($secret) || !isset($payload['signature']) || !DNAPayments::isValidSignature($payload, $secret)) {
            Log::warning('DNA callback invalid signature', [
                'id'        => $payload['id']        ?? null,
                'invoiceId' => $payload['invoiceId'] ?? null,
            ]);
            return response()->json(['ok' => true]);
        }

        $successFlag  = (bool)($payload['success'] ?? false);
        $errorCode    = isset($payload['errorCode']) ? (int)$payload['errorCode'] : 0;
        $invoiceId    = $payload['invoiceId'] ?? null;
        $paidAmount   = isset($payload['amount']) ? (float)$payload['amount'] : 0.0;
        $paidCurrency = $payload['currency'] ?? null;

        $dnaId         = $payload['id'] ?? null;
        $dnaRrn        = $payload['rrn'] ?? null;
        $dnaScheme     = $payload['schemeReferenceData'] ?? null;
        $cardBrand     = $payload['cardSchemeName'] ?? null;
        $cardCountry   = $payload['cardIssuingCountry'] ?? null;
        $cardMaskedPan = $payload['cardPanStarred'] ?? null;
        $cardExpiry    = $payload['cardExpiryDate'] ?? null;
        $cardTokenId   = $payload['cardTokenId'] ?? null;

        if (!$successFlag || $errorCode !== 0 || !$invoiceId) {
            return response()->json(['ok' => true]);
        }

        // Load checkout context created on /checkout gateway request
        $context = Cache::pull('dna_invoice_'.$invoiceId) ?? [];
        $customerId = $context['customer_id'] ?? null;
        $shippingBranchId = $context['shipping_branch_id'] ?? null;
        $billingBranchId  = $context['billing_branch_id'] ?? null;

        if (!$customerId || !$shippingBranchId || !$billingBranchId) {
            return response()->json(['ok' => true]);
        }

        $customer = Customer::find($customerId);
        if (!$customer) {
            return response()->json(['ok' => true]);
        }

        // Map cached context into "inputs" equivalent to store()
        $deliveryMethodId = $context['delivery_method_id'] ?? null;
        $deliveryNote     = $context['delivery_note'] ?? null;
        $customerPoNumber = trim((string) ($context['customer_po_number'] ?? ''));
        $customerPoNumber = $customerPoNumber !== '' ? $customerPoNumber : null;
        $cachedWalletUsed = isset($context['wallet_credit_used']) ? (float)$context['wallet_credit_used'] : null;

        try {
            // Validate that the selected branches belong to the customer
            $shippingBranch = Branch::where('id', (int)$shippingBranchId)
                ->where('customer_id', (int)$customer->id)
                ->first();
            $billingBranch = Branch::where('id', (int)$billingBranchId)
                ->where('customer_id', (int)$customer->id)
                ->first();

            if (!$shippingBranch || !$billingBranch) {
                return response()->json(['ok' => true]);
            }

            // Build order from server cart (authoritative)
            $cart = \App\Models\Cart::where('customer_id', (int)$customer->id)->first();
            if (!$cart) {
                return response()->json(['ok' => true]);
            }

            // Use existing cart item prices (already include volume discounts from CartController)
            $cartItems = \App\Models\CartItem::where('cart_id', $cart->id)->get();
            if ($cartItems->isEmpty()) {
                return response()->json(['ok' => true]);
            }

            $planufacOrderWebhookId = null;
            $createdOrderId = null;

            DB::transaction(function () use (
                $cart,
                $cartItems,
                $customer,
                $shippingBranch,
                $billingBranch,
                $deliveryMethodId,
                $deliveryNote,
                $customerPoNumber,
                $invoiceId,
                $paidAmount,
                $paidCurrency,
                $cachedWalletUsed,
                $dnaId,
                $dnaRrn,
                $dnaScheme,
                $cardBrand,
                $cardCountry,
                $cardMaskedPan,
                $cardExpiry,
                $cardTokenId,
                $payload,
                &$planufacOrderWebhookId,
                &$createdOrderId
            ) {
                $subtotal = 0;
                $units = 0;
                $skus = 0;
                $items = [];
                $totalWalletCreditEarned = 0;
                $pendingOrderItems = [];
                $vatAmount = 0;

                $adjustments = [];
                $adjusted = false;
                foreach ($cartItems as $ci) {
                    $product = Product::with('unit')->find($ci->product_id);
                    if (!$product) { continue; }
                    $qty = (int) $ci->quantity;

                    // Stock availability check at checkout time - reconcile cart quantities
                    $available = null;
                    if (isset($product->available_qty) && $product->available_qty !== null) {
                        $available = (int) $product->available_qty;
                    } elseif (isset($product->stock_quantity) && $product->stock_quantity !== null) {
                        $available = (int) $product->stock_quantity;
                    }
                    if ($available !== null && $qty > $available) {
                        $adjusted = true;
                        $adjustments[] = [
                            'product_id' => (int) $product->id,
                            'product_name' => (string) $product->name,
                            'old_quantity' => $qty,
                            'new_quantity' => max(0, $available),
                        ];
                        if ($available <= 0) {
                            \App\Models\CartItem::where('id', $ci->id)->delete();
                        } else {
                            $ci->quantity = $available;
                            // keep existing discounted unit_price, just update line_total
                            $ci->line_total = ((float)$ci->unit_price) * $available;
                            $ci->save();
                        }
                        continue;
                    }

                    // Use effective cart unit price (already includes any volume discounts)
                    $price = (float) $ci->unit_price;
                    $totalPrice = round($price * $qty, 2);
                    $unitVat = round((float)($product->vat_amount ?? 0), 2);
                    $totalVat = round($unitVat * $qty, 2);
                    $unitWalletCredit = round((float)($product->wallet_credit ?? 0), 2);
                    $walletCreditEarned = round($unitWalletCredit * $qty, 2);
                    $productUnit = $product->unit ? $product->unit->name : null;
                    $itemTotal = round($totalPrice + $totalVat, 2);

                    $pendingOrderItems[] = [
                        'type' => 'SO',
                        'product_id' => $product->id,
                        'product_unit' => $productUnit,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'unit_vat' => $unitVat,
                        'unit_wallet_credit' => $unitWalletCredit,
                        'wallet_credit_earned' => $walletCreditEarned,
                        'total_price' => $totalPrice,
                        'total_vat' => $totalVat,
                        'total' => $itemTotal,
                    ];

                    $subtotal += $totalPrice;
                    $vatAmount += $totalVat;
                    $units += $qty;
                    $skus += 1;
                    $totalWalletCreditEarned += (float)($product->wallet_credit ?? 0) * $qty;
                    $items[] = ['product_id' => (int)$product->id, 'quantity' => $qty];
                }

                if ($adjusted) {
                    // Mirror store() behaviour: after reconciliation just stop (DNA already paid; we log and exit).
                    Log::warning('DNA callback stock adjusted after payment', [
                        'invoiceId' => $invoiceId,
                        'adjustments' => $adjustments,
                    ]);
                    return;
                }

                // Auto-apply available wallet credit to this purchase (partial or full)
                $availableCredit = (float)($customer->credit_balance ?? 0.0);
                $deliveryMethod = $deliveryMethodId ? \App\Models\DeliveryMethod::find($deliveryMethodId) : null;
                $deliveryCharge = $deliveryMethod ? (float)$deliveryMethod->rate : 0;
                $totalAmount = $subtotal + $vatAmount + $deliveryCharge;
                $walletCreditUsed = min($subtotal, $availableCredit);
                if (is_float($cachedWalletUsed) || is_int($cachedWalletUsed)) {
                    $walletCreditUsed = (float) $cachedWalletUsed;
                }
                $outstandingAmount = $totalAmount - $walletCreditUsed;

                // DNA charged amount must match outstanding (after wallet)
                $expectedCurrency = config('services.dna_payments.currency', 'GBP');
                if (round($outstandingAmount, 2) !== round($paidAmount, 2)
                    || ($paidCurrency && strtoupper($paidCurrency) !== strtoupper($expectedCurrency))) {
                    Log::warning('DNA callback amount/currency mismatch', [
                        'invoiceId' => $invoiceId,
                        'total_amount' => $totalAmount,
                        'wallet_credit_used' => $walletCreditUsed,
                        'expected_outstanding' => $outstandingAmount,
                        'dna_amount' => $paidAmount,
                        'dna_currency' => $paidCurrency,
                        'expected_currency' => $expectedCurrency,
                    ]);
                    return;
                }

                // Get order number
                $orderRef = OrderRef::orderBy('id', 'desc')->first();
                if (!$orderRef) {
                    $orderRef = OrderRef::create(['so' => 1, 'qa' => 1, 'po' => 1]);
                }
                $orderNumber = $orderRef->so ?? 1;
                $orderRef->update(['so' => ($orderRef->so ?? 0) + 1]);

                // Amounts for DNA (card / bank) checkout:
                // - paid_amount: what DNA actually charged
                // - outstanding_amount: total_amount - wallet_credit_used (amount due from DNA)
                // - payment_amount: same as outstanding_amount
                $paidAmountInternal = round($paidAmount, 2);
                $outstandingAmount = max(0, round($totalAmount - $walletCreditUsed, 2));
                $paymentAmount = $outstandingAmount;
                $unpaidAmount = 0;

                $paymentStatus = 'Paid';

                $isPayByBank = (isset($payload['paymentMethod']) && strtolower((string) $payload['paymentMethod']) === 'ecospend');
                $checkoutMode = $isPayByBank ? 'gateway_bank' : 'gateway';

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'type' => 'SO',
                    'order_date' => now(),
                    'customer_id' => optional($customer)->id ?? null,
                    'subtotal' => $subtotal,
                    'vat_amount' => $vatAmount,
                    'total_amount' => $totalAmount,
                    'payment_amount' => $paymentAmount,
                    'wallet_credit_used' => $walletCreditUsed,
                    'paid_amount' => $paidAmountInternal,
                    'unpaid_amount' => $unpaidAmount,
                    'units_count' => $units,
                    'skus_count' => $skus,
                    'items_count' => count($items),
                    'payment_terms' => 'net_30',
                    'payment_status' => $paymentStatus,
                    'checkout_payment_mode' => $checkoutMode,
                    'outstanding_amount' => $outstandingAmount,
                    'estimated_delivery_date' => now()->addDays(7),
                    'status' => 'New',
                    'shipping_branch_id' => $shippingBranch->id,
                    'billing_branch_id' => $billingBranch->id,
                    'delivery_method_id' => $deliveryMethod?->id,
                    'delivery_method_name' => $deliveryMethod?->name,
                    'delivery_time' => $deliveryMethod?->time,
                    'delivery_charge' => $deliveryCharge,
                    'delivery_note' => $deliveryNote,
                    'customer_po_number' => $customerPoNumber,
                ]);

                Cache::put(
                    'dna_invoice_order_'.$invoiceId,
                    (string) $order->order_number,
                    now()->addHours(24)
                );

                // Payment record + basic card/DNA info (card or Pay by bank / ecospend)
                $reference = $dnaId ?: $dnaRrn ?: $dnaScheme ?: ('DNA-' . $invoiceId);
                $cardLast4 = null;
                if (is_string($cardMaskedPan)) {
                    $digits = preg_replace('/\D+/', '', $cardMaskedPan);
                    if ($digits && strlen($digits) >= 4) $cardLast4 = substr($digits, -4);
                }
                $paymentNote = $isPayByBank ? 'Pay by bank' : ($dnaScheme ? ('DNA scheme reference: '.$dnaScheme) : null);

                Payment::create([
                    'order_id'            => $order->id,
                    'date'                => now(),
                    'reference_no'        => $reference,
                    'amount'              => $paidAmount,
                    'payment_method'      => 'DNA Gateway',
                    'card_brand'          => $cardBrand,
                    'card_last4'          => $cardLast4,
                    'card_country'        => $cardCountry,
                    'card_expiry'         => $cardExpiry,
                    'dna_token_id'        => $cardTokenId,
                    'dna_transaction_id'  => $dnaId,
                    'dna_rrn'             => $dnaRrn,
                    'dna_scheme_reference'=> $dnaScheme,
                    'note'                => $paymentNote,
                    'user_id'             => null,
                ]);

                foreach ($pendingOrderItems as $poi) {
                    $poi['order_id'] = $order->id;
                    OrderItem::create($poi);
                }

                // Reduce product stock quantities after successful order item creation
                foreach ($pendingOrderItems as $poi) {
                    $qty = (float) ($poi['quantity'] ?? 0);
                    $productId = (int) ($poi['product_id'] ?? 0);
                    if ($productId > 0 && $qty > 0) {
                        \App\Services\WarehouseProductSyncService::adjustOrdered($productId, 'addition', $qty);
                    }
                }

                // Clear the cart after successful checkout
                \App\Models\CartItem::where('cart_id', $cart->id)->delete();
                \App\Models\Cart::where('id', $cart->id)->update([
                    'subtotal' => 0,
                    'total_discount' => 0,
                    'total' => 0,
                    'units' => 0,
                    'skus' => 0,
                ]);

                // Wallet credit debit (applied to this order) and earning credit
                if ($customer) {
                    if ($walletCreditUsed > 0) {
                        $customer->credit_balance = (float)($customer->credit_balance ?? 0) - $walletCreditUsed;
                        $customer->save();
                        \App\Models\WalletTransaction::create([
                            'customer_id' => $customer->id,
                            'order_id' => $order->id,
                            'amount' => $walletCreditUsed,
                            'type' => 'debit',
                            'description' => 'Wallet credit applied to order',
                            'balance_after' => $customer->credit_balance,
                        ]);
                    }
                    $customer->credit_balance = (float)($customer->credit_balance ?? 0) + $totalWalletCreditEarned;
                    $customer->save();
                    \App\Models\WalletTransaction::create([
                        'customer_id' => $customer->id,
                        'order_id' => $order->id,
                        'amount' => $totalWalletCreditEarned,
                        'type' => 'credit',
                        'description' => 'Wallet credit earned on order',
                        'balance_after' => $customer->credit_balance,
                    ]);
                }

                $planufacOrderWebhookId = $order->id;
                $createdOrderId = $order->id;
            });

            if ($planufacOrderWebhookId !== null) {
                SendPlanufacOrderWebhookJob::dispatch($planufacOrderWebhookId);
            }
            if ($createdOrderId !== null) {
                $createdOrder = Order::with(['customer', 'items.product', 'shippingBranch'])
                    ->whereKey($createdOrderId)
                    ->first();
                if ($createdOrder) {
                    $orderConfirmationEmailService->sendForOrder($createdOrder);
                }
            }

            return;
        } catch (\Exception $e) {
            Log::error('DNA callback failed: '.$e->getMessage(), ['invoiceId' => $invoiceId]);
            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => true]);
    }
}

