<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Branch;
use App\Models\WalletTransaction;
use App\Models\OrderRef;
use App\Services\WarehouseProductSyncService;
use App\traits\BulkDeletes;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Services\OrderDeletionService;

class OrderController extends Controller
{
  protected $orderDeletionService;

    public function __construct(OrderDeletionService $orderDeletionService)
    {
        $this->orderDeletionService = $orderDeletionService;
    }
  
  public function index()
  {
    $customers = \App\Models\Customer::where('is_active', 1)
      ->orderBy('company_name')
      ->orderBy('email')
      ->get();
    return view('content.order.list', ['customers' => $customers]);
  }

  public function add()
  {
    $customers = \App\Models\Customer::where('is_active', 1)
      ->orderBy('company_name')
      ->orderBy('email')
      ->get();
    $products = Product::where('is_active', 1)->select('id', 'name', 'sku', 'price', 'wallet_credit', 'image_url')->get();

    return view('content.order.add', [
      'customers' => $customers,
      'products' => $products
    ]);
  }

  public function create(Request $request)
  {
    // Main form create (from add page)
    $validated = $request->validate([
      'customer_id' => ['required', 'integer', 'exists:customers,id'],
      'date' => [
        'required',
        function ($attribute, $value, $fail) {
          if (empty($value)) {
            $fail('Date is required');
            return;
          }
          // Try to parse d/m/Y H:i format (e.g., "12/11/2025 12:06")
          if (strpos($value, '/') !== false) {
            try {
              $parsed = Carbon::createFromFormat('d/m/Y H:i', $value);
              if ($parsed === false) {
                $fail('Date must be in dd/mm/yyyy hh:mm format');
              }
            } catch (\Exception $e) {
              try {
                $parsed = Carbon::createFromFormat('d/m/Y', $value);
                if ($parsed === false) {
                  $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
                }
              } catch (\Exception $e2) {
                $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
              }
            }
          } else {
            // Try standard date formats
            try {
              $parsed = Carbon::parse($value);
              if ($parsed === false) {
                $fail('Date must be a valid date');
              }
            } catch (\Exception $e) {
              $fail('Date must be a valid date');
            }
          }
        }
      ],
      'shipping_charge' => ['nullable', 'numeric', 'min:0'],
      'delivery_note' => ['nullable', 'string'],
      'is_est' => ['nullable', 'boolean'],
      'address_id' => ['required', 'integer', 'exists:branches,id'],
      'products' => ['required', 'array', 'min:1'],
      'products.*.product_id' => ['required', 'exists:products,id'],
      'products.*.quantity' => ['required', 'numeric', 'min:1'],
      'products.*.unit_cost' => ['required', 'numeric', 'min:0'],
    ], [
      'customer_id.required' => 'Customer is required',
      'customer_id.integer' => 'Customer must be a valid selection',
      'customer_id.exists' => 'Selected customer does not exist',
      'date.required' => 'Date is required',
      'address_id.required' => 'Address is required',
      'address_id.integer' => 'Address must be a valid selection',
      'address_id.exists' => 'Selected address does not exist',
      'products.required' => 'At least one product is required',
      'products.min' => 'At least one product is required',
      'products.*.product_id.required' => 'Product is required',
      'products.*.product_id.exists' => 'Selected product does not exist',
      'products.*.quantity.required' => 'Quantity is required',
      'products.*.quantity.numeric' => 'Quantity must be a number',
      'products.*.quantity.min' => 'Quantity must be at least 1',
      'products.*.unit_cost.required' => 'Sale price is required',
      'products.*.unit_cost.numeric' => 'Sale price must be a number',
      'products.*.unit_cost.min' => 'Sale price must be 0 or greater',
      'shipping_charge.numeric' => 'Shipping charge must be a number',
      'shipping_charge.min' => 'Shipping charge must be 0 or greater',
    ]);

    // Parse date (support d/m/Y H:i and d/m/Y)
    $date = $validated['date'];
    if (strpos($date, '/') !== false) {
      try {
        $date = Carbon::createFromFormat('d/m/Y H:i', $date);
      } catch (\Exception $e) {
        $date = Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
      }
    } else {
      $date = Carbon::parse($date);
    }

    // Normalize products array (in case keys are product IDs)
    $products = array_filter($validated['products'], function ($productData) {
      return isset($productData['product_id']);
    });

    // Validate product stock availability
    $stockErrors = [];
    foreach ($products as $index => $productData) {
      $productId = (int) ($productData['product_id'] ?? 0);
      $orderedQuantity = (float) ($productData['quantity'] ?? 0);

      if ($productId > 0 && $orderedQuantity > 0) {
        $product = Product::find($productId);
        if ($product) {
          $availableQuantity = (float) ($product->stock_quantity ?? 0);
          if ($orderedQuantity > $availableQuantity) {
            $productName = $product->name ?? 'Product #' . $productId;
            $stockErrors["products.{$index}.quantity"] = "Insufficient stock for {$productName}. Available: {$availableQuantity}, Requested: {$orderedQuantity}";
          }
        }
      }
    }

    if (!empty($stockErrors)) {
      return redirect()->back()
        ->withErrors($stockErrors)
        ->withInput();
    }

    // Get branch/address if address_id is provided
    $branch = null;
    $addressData = [];
    if (!empty($validated['address_id'])) {
      $branch = Branch::find($validated['address_id']);
      if ($branch) {
        $addressData = [
          'branch_name' => $branch->name,
          'address_line1' => $branch->address_line1,
          'address_line2' => $branch->address_line2,
          'city' => $branch->city,
          'zip_code' => $branch->zip_code,
          'country' => $branch->country,
        ];
      }
    }

    // Get customer for wallet credit calculations
    $customer = \App\Models\Customer::findOrFail($validated['customer_id']);

    // Determine order type based on is_est checkbox
    $orderType = (isset($validated['is_est']) && $validated['is_est']) ? 'EST' : 'SO';

    // Get order number from order_ref table based on order type
    $orderRef = OrderRef::orderBy('id', 'desc')->first();

    if (!$orderRef) {
      // Create initial order_ref record if it doesn't exist
      $orderRef = OrderRef::create([
        'so' => 1,
        'est' => 1,
        'qa' => 1,
        'po' => 1,
      ]);
    }

    // Use order_ref.est for EST orders, order_ref.so for SO orders
    if ($orderType === 'EST') {
      $orderNumber = $orderRef->est ?? 1;
      // Increment est for next EST order
      $orderRef->update([
        'est' => ($orderRef->est ?? 0) + 1,
      ]);
    } else {
      $orderNumber = $orderRef->so ?? 1;
      // Increment so for next SO order
      $orderRef->update([
        'so' => ($orderRef->so ?? 0) + 1,
      ]);
    }

    // Process create in transaction
    $order = DB::transaction(function () use ($validated, $products, $date, $addressData, $customer, $orderNumber, $orderType) {

      // Create order
      $orderData = [
        'customer_id' => $validated['customer_id'],
        'order_date' => $date,
        'order_number' => $orderNumber,
        'type' => $orderType,
        'delivery_charge' => $validated['shipping_charge'] ?? 0,
        'delivery_note' => $validated['delivery_note'] ?? null,
        'is_est' => isset($validated['is_est']) && $validated['is_est'] ? true : false,
        'status' => 'Completed',
        'payment_status' => 'Due',
        'subtotal' => 0,
        'vat_amount' => 0,
        'total_amount' => 0,
        'paid_amount' => 0,
        'unpaid_amount' => 0,
        'outstanding_amount' => 0,
        'items_count' => 0,
        'units_count' => 0,
        'skus_count' => 0,
        'wallet_credit_used' => 0,
      ];

      // Add address data if branch was selected
      if (!empty($addressData)) {
        $orderData = array_merge($orderData, $addressData);
      }

      $order = Order::create($orderData);

      // Create order items and calculate wallet credit earned
      $walletCreditEarned = 0;
      foreach ($products as $productData) {
        $quantity = (int) $productData['quantity'];
        $unitPrice = (float) ($productData['unit_cost'] ?? 0);

        // Get fresh product data to ensure we have the latest wallet_credit value
        $product = Product::with('unit')->findOrFail($productData['product_id']);

        // Get product unit name
        $productUnit = $product->unit ? $product->unit->name : null;

        // Calculate wallet credit: unit_wallet_credit = product wallet_credit, wallet_credit_earned = unit_wallet_credit * quantity
        $unitWalletCredit = round((float) ($product->wallet_credit ?? 0), 2);
        $walletCreditEarnedPerItem = round($unitWalletCredit * $quantity, 2);
        $walletCreditEarned += $walletCreditEarnedPerItem;

        // Calculate total price
        $totalPrice = round($unitPrice * $quantity, 2);

        // Calculate VAT: if is_est is checked, VAT is 0 (included in price), otherwise use product vat_amount
        if ($orderType === 'EST') {
          $unitVat = 0; // VAT is included in the sale price for EST orders
        } else {
          $unitVat = round((float) ($product->vat_amount ?? 0), 2);
        }
        $totalVat = round($unitVat * $quantity, 2);

        // Calculate total: total_price + total_vat
        $total = round($totalPrice + $totalVat, 2);

        // Create order item
        OrderItem::create([
          'order_id' => $order->id,
          'type' => $order->type,
          'product_id' => $productData['product_id'],
          'product_unit' => $productUnit,
          'quantity' => $quantity,
          'unit_price' => $unitPrice,
          'unit_vat' => $unitVat,
          'unit_wallet_credit' => $unitWalletCredit,
          'wallet_credit_earned' => $walletCreditEarnedPerItem,
          'total_price' => $totalPrice,
          'total_vat' => $totalVat,
          'total' => $total,
        ]);
      }

      // Refresh order to get new items
      $order->refresh();
      $order->load('items');

      // Calculate subtotal
      $subtotal = $order->items->sum('total_price');

      // Calculate wallet_credit_used based on subtotal and available credit
      // EST orders do not affect wallet credit or credit balance
      $walletCreditUsed = 0;
      if ($orderType !== 'EST') {
        $currentBalance = (float) ($customer->credit_balance ?? 0);
        $walletCreditUsed = min($subtotal, $currentBalance);

        // Update customer credit balance
        if ($walletCreditUsed > 0) {
          $customer->credit_balance = $currentBalance - $walletCreditUsed;
          $customer->save();

          WalletTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => $walletCreditUsed,
            'type' => 'debit',
            'description' => 'Wallet credit applied to order',
            'balance_after' => $customer->credit_balance,
          ]);
        }

        // Apply wallet credit earned
        if ($walletCreditEarned > 0) {
          $customer->credit_balance = ($customer->credit_balance ?? 0) + $walletCreditEarned;
          $customer->save();

          WalletTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => $walletCreditEarned,
            'type' => 'credit',
            'description' => 'Wallet credit earned on order',
            'balance_after' => $customer->credit_balance,
          ]);
        }
      }

      // Update order with wallet_credit_used
      $order->wallet_credit_used = $walletCreditUsed;
      $order->save();

      // Update order totals
      $this->updateOrderTotals($order->id);

      // Decrement product quantities after order is created
      foreach ($products as $productData) {
        $quantity = (float) ($productData['quantity'] ?? 0);
        $productId = (int) ($productData['product_id'] ?? 0);
        if ($productId > 0 && $quantity > 0) {
          try {
            WarehouseProductSyncService::adjustQuantity($productId, 'subtraction', $quantity);
          } catch (\Exception $e) {
            // Log error but don't fail the transaction
            Log::error("Failed to adjust product quantity for product {$productId}: " . $e->getMessage());
          }
        }
      }

      return $order;
    });

    Toastr::success('Order created successfully!');
    // Flag to clear add-order localStorage on next page load
    return redirect()->route('order.list')->with('order_add_clear_storage', true);
  }

  public function edit($id)
  {
    $order = Order::with(['customer.branches', 'items.product', 'statusHistories', 'creditNotes'])->findOrFail($id);

    // Check if order has credit notes - if so, prevent edit access
    if ($order->type === 'SO' && $order->creditNotes()->exists()) {
      Toastr::error('This order cannot be edited because a credit note has been generated for it.');
      return redirect()->route('order.list');
    }

    $products = Product::where('is_active', 1)->select('id', 'name', 'sku', 'price', 'wallet_credit', 'image_url')->get();

    return view('content.order.edit', [
      'order' => $order,
      'products' => $products
    ]);
  }

  public function itemsAjax(Request $request)
  {
    $orderId = $request->get('id');
    $query = Order::where('orders.id', $orderId)
      ->join('order_items', 'order_items.order_id', '=', 'orders.id')
      ->join('products', 'products.id', '=', 'order_items.product_id')
      ->whereNull('products.deleted_at')
      ->select([
        'order_items.id',
        'order_items.product_id',
        'products.name as product_name',
        'products.image_url',
        'order_items.quantity',
        'order_items.unit_price',
        'order_items.wallet_credit_earned'
      ]);

    return DataTables::of($query)
      ->toJson();
  }

  public function update(Request $request)
  {
    // Check if order has credit notes - if so, prevent update
    $orderId = $request->input('id');
    if ($orderId) {
      $order = Order::with('creditNotes')->find($orderId);
      if ($order && $order->type === 'SO' && $order->creditNotes()->exists()) {
        Toastr::error('This order cannot be updated because a credit note has been generated for it.');
        return redirect()->back()->withInput();
      }
    }

    // Main form update (from details page)
    $validated = $request->validate([
      'id' => ['required', 'exists:orders,id'],
      'date' => [
        'required',
        function ($attribute, $value, $fail) {
          if (empty($value)) {
            $fail('Date is required');
            return;
          }
          // Try to parse d/m/Y H:i format (e.g., "12/11/2025 12:06")
          if (strpos($value, '/') !== false) {
            try {
              $parsed = Carbon::createFromFormat('d/m/Y H:i', $value);
              if ($parsed === false) {
                $fail('Date must be in dd/mm/yyyy hh:mm format');
              }
            } catch (\Exception $e) {
              try {
                $parsed = Carbon::createFromFormat('d/m/Y', $value);
                if ($parsed === false) {
                  $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
                }
              } catch (\Exception $e2) {
                $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
              }
            }
          } else {
            // Try standard date formats
            try {
              $parsed = Carbon::parse($value);
              if ($parsed === false) {
                $fail('Date must be a valid date');
              }
            } catch (\Exception $e) {
              $fail('Date must be a valid date');
            }
          }
        }
      ],
      'shipping_charge' => ['nullable', 'numeric', 'min:0'],
      'delivery_note' => ['nullable', 'string'],
      'address_id' => ['required', 'integer', 'exists:branches,id'],
      'products' => ['required', 'array', 'min:1'],
      'products.*.product_id' => ['required', 'exists:products,id'],
      'products.*.quantity' => ['required', 'numeric', 'min:1'],
      'products.*.unit_cost' => ['required', 'numeric', 'min:0'],
    ], [
      'id.required' => 'Order ID is required',
      'id.exists' => 'Order not found',
      'date.required' => 'Date is required',
      'address_id.required' => 'Address is required',
      'address_id.integer' => 'Address must be a valid selection',
      'address_id.exists' => 'Selected address does not exist',
      'products.required' => 'At least one product is required',
      'products.min' => 'At least one product is required',
      'products.*.product_id.required' => 'Product is required',
      'products.*.product_id.exists' => 'Selected product does not exist',
      'products.*.quantity.required' => 'Quantity is required',
      'products.*.quantity.numeric' => 'Quantity must be a number',
      'products.*.quantity.min' => 'Quantity must be at least 1',
      'products.*.unit_cost.required' => 'Sale price is required',
      'products.*.unit_cost.numeric' => 'Sale price must be a number',
      'products.*.unit_cost.min' => 'Sale price must be 0 or greater',
      'shipping_charge.numeric' => 'Shipping charge must be a number',
      'shipping_charge.min' => 'Shipping charge must be 0 or greater',
    ]);

    $order = Order::with('items')->findOrFail($validated['id']);

    // Parse date (support d/m/Y H:i and d/m/Y)
    $date = $validated['date'];
    if (strpos($date, '/') !== false) {
      try {
        $date = Carbon::createFromFormat('d/m/Y H:i', $date);
      } catch (\Exception $e) {
        $date = Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
      }
    } else {
      $date = Carbon::parse($date);
    }

    // Normalize products array (in case keys are product IDs)
    $products = array_filter($validated['products'], function ($productData) {
      return isset($productData['product_id']);
    });

    // Validate product stock availability (accounting for quantities that will be restored)
    // Build a map of old quantities by product_id
    $oldQuantitiesByProduct = [];
    foreach ($order->items as $oldItem) {
      $oldProductId = (int) ($oldItem->product_id ?? 0);
      $oldQuantity = (float) ($oldItem->quantity ?? 0);
      if ($oldProductId > 0) {
        $oldQuantitiesByProduct[$oldProductId] = ($oldQuantitiesByProduct[$oldProductId] ?? 0) + $oldQuantity;
      }
    }

    $stockErrors = [];
    foreach ($products as $index => $productData) {
      $productId = (int) ($productData['product_id'] ?? 0);
      $orderedQuantity = (float) ($productData['quantity'] ?? 0);

      if ($productId > 0 && $orderedQuantity > 0) {
        $product = Product::find($productId);
        if ($product) {
          $currentStock = (float) ($product->stock_quantity ?? 0);
          // Add back the old quantity that will be restored
          $oldQuantity = (float) ($oldQuantitiesByProduct[$productId] ?? 0);
          $availableQuantity = $currentStock + $oldQuantity;

          if ($orderedQuantity > $availableQuantity) {
            $productName = $product->name ?? 'Product #' . $productId;
            $stockErrors["products.{$index}.quantity"] = "Insufficient stock for {$productName}. Available: {$availableQuantity}, Requested: {$orderedQuantity}";
          }
        }
      }
    }

    if (!empty($stockErrors)) {
      return redirect()->back()
        ->withErrors($stockErrors)
        ->withInput();
    }

    // Get branch/address if address_id is provided
    $branch = null;
    $addressData = [];
    if (!empty($validated['address_id'])) {
      $branch = Branch::find($validated['address_id']);
      if ($branch) {
        $addressData = [
          'branch_name' => $branch->name,
          'address_line1' => $branch->address_line1,
          'address_line2' => $branch->address_line2,
          'city' => $branch->city,
          'zip_code' => $branch->zip_code,
          'country' => $branch->country,
        ];
      }
    }

    // Process update in transaction
    DB::transaction(function () use ($order, $products, $validated, $date, $addressData) {
      // Calculate old wallet credit earned before deleting items
      $oldWalletCreditEarned = $order->items->sum('wallet_credit_earned');
      $oldWalletCreditUsed = (float) ($order->wallet_credit_used ?? 0);

      // Get customer for wallet credit adjustments
      $customer = $order->customer;

      // Restore old product quantities before deleting items
      foreach ($order->items as $oldItem) {
        $oldQuantity = (float) ($oldItem->quantity ?? 0);
        $oldProductId = (int) ($oldItem->product_id ?? 0);
        if ($oldProductId > 0 && $oldQuantity > 0) {
          try {
            WarehouseProductSyncService::adjustQuantity($oldProductId, 'addition', $oldQuantity);
          } catch (\Exception $e) {
            // Log error but don't fail the transaction
            Log::error("Failed to restore product quantity for product {$oldProductId}: " . $e->getMessage());
          }
        }
      }

      // Delete old items
      $order->items()->delete();

      // Create new items and calculate new wallet credit earned
      // This handles: adding products, removing products, and changing quantities
      $newWalletCreditEarned = 0;
      foreach ($products as $productData) {
        $quantity = (int) $productData['quantity'];
        $unitPrice = (float) ($productData['unit_cost'] ?? 0); // Note: form uses unit_cost but it's actually unit_price for orders

        // Get fresh product data to ensure we have the latest wallet_credit value
        $product = Product::with('unit')->findOrFail($productData['product_id']);

        // Get product unit name
        $productUnit = $product->unit ? $product->unit->name : null;

        // Calculate wallet credit: unit_wallet_credit = product wallet_credit, wallet_credit_earned = unit_wallet_credit * quantity
        // This automatically recalculates when quantity changes
        $unitWalletCredit = round((float) ($product->wallet_credit ?? 0), 2);
        $walletCreditEarned = round($unitWalletCredit * $quantity, 2);
        $newWalletCreditEarned += $walletCreditEarned;

        // Calculate total price
        $totalPrice = round($unitPrice * $quantity, 2);

        // Calculate VAT: if order type is EST, VAT is 0 (included in price), otherwise use product vat_amount
        if ($order->type === 'EST') {
          $unitVat = 0; // VAT is included in the sale price for EST orders
        } else {
          $unitVat = round((float) ($product->vat_amount ?? 0), 2);
        }
        $totalVat = round($unitVat * $quantity, 2);

        // Calculate total: total_price + total_vat
        $total = round($totalPrice + $totalVat, 2);

        // Create order item with recalculated wallet_credit_earned
        // This ensures order_items.wallet_credit_earned is always correct for the current quantity
        OrderItem::create([
          'order_id' => $order->id,
          'type' => $order->type,
          'product_id' => $productData['product_id'],
          'product_unit' => $productUnit,
          'quantity' => $quantity,
          'unit_price' => $unitPrice,
          'unit_vat' => $unitVat,
          'unit_wallet_credit' => $unitWalletCredit,
          'wallet_credit_earned' => $walletCreditEarned, // Recalculated based on current quantity
          'total_price' => $totalPrice,
          'total_vat' => $totalVat,
          'total' => $total,
        ]);
      }

      // Refresh order to get new items
      $order->refresh();
      $order->load('items');

      // Calculate new subtotal
      $newSubtotal = $order->items->sum('total_price');

      // Update order totals first to get new total_amount
      $this->updateOrderTotals($order->id);
      $order->refresh();

      // Recalculate wallet_credit_used based on new subtotal
      // EST orders do not affect wallet credit or credit balance
      // Note: wallet_credit_earned = credit customer earns (added to balance)
      //       wallet_credit_used = credit customer uses (subtracted from balance)
      $newWalletCreditUsed = 0;
      if ($order->type !== 'EST' && $customer) {
        // Reload customer to get fresh balance data
        $customer->refresh();
        $currentBalance = (float) ($customer->credit_balance ?? 0);

        // To calculate available credit for new order, we need to:
        // 1. Add back the old wallet_credit_used (it was subtracted before)
        // 2. Subtract the old wallet_credit_earned (it was added before)
        // This gives us the balance as if the old order never happened
        $balanceBeforeOldOrder = $currentBalance - $oldWalletCreditEarned + $oldWalletCreditUsed;

        // Calculate new wallet_credit_used based on new subtotal and available credit
        $newWalletCreditUsed = min($newSubtotal, $balanceBeforeOldOrder);

        // Adjust customer credit balance step by step
        // Step 1: Reverse old wallet_credit_earned (subtract - it was added to balance)
        if ($oldWalletCreditEarned > 0) {
          $customer->credit_balance = $currentBalance - $oldWalletCreditEarned;
          $customer->save();
          $currentBalance = $customer->credit_balance;

          WalletTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => $oldWalletCreditEarned,
            'type' => 'debit',
            'description' => 'Wallet credit earned reversed due to order update',
            'balance_after' => $currentBalance,
          ]);
        }

        // Step 2: Reverse old wallet_credit_used (add back - it was subtracted from balance)
        if ($oldWalletCreditUsed > 0) {
          $customer->credit_balance = $currentBalance + $oldWalletCreditUsed;
          $customer->save();
          $currentBalance = $customer->credit_balance;

          WalletTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => $oldWalletCreditUsed,
            'type' => 'credit',
            'description' => 'Wallet credit used reversed due to order update',
            'balance_after' => $currentBalance,
          ]);
        }

        // Step 3: Apply new wallet_credit_used (subtract from balance)
        if ($newWalletCreditUsed > 0) {
          $customer->credit_balance = $currentBalance - $newWalletCreditUsed;
          $customer->save();
          $currentBalance = $customer->credit_balance;

          WalletTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => $newWalletCreditUsed,
            'type' => 'debit',
            'description' => 'Wallet credit applied to updated order',
            'balance_after' => $currentBalance,
          ]);
        }

        // Step 4: Apply new wallet_credit_earned (add to balance)
        if ($newWalletCreditEarned > 0) {
          $customer->credit_balance = $currentBalance + $newWalletCreditEarned;
          $customer->save();

          WalletTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => $newWalletCreditEarned,
            'type' => 'credit',
            'description' => 'Wallet credit earned on updated order',
            'balance_after' => $customer->credit_balance,
          ]);
        }
      }

      // Update order with new wallet_credit_used
      // Note: order_number (reference_no) is not updated - keep original value
      $updateData = [
        'order_date' => $date,
        'order_number' => $order->order_number, // Keep original reference number, don't update
        'delivery_charge' => $validated['shipping_charge'] ?? 0,
        'delivery_note' => $validated['delivery_note'] ?? null,
        'wallet_credit_used' => $newWalletCreditUsed,
      ];

      // Add address data if branch was selected
      if (!empty($addressData)) {
        $updateData = array_merge($updateData, $addressData);
      }

      $order->update($updateData);

      // Update order totals again to recalculate with new wallet_credit_used
      $this->updateOrderTotals($order->id);

      // Decrement new product quantities after order is updated
      foreach ($products as $productData) {
        $quantity = (float) ($productData['quantity'] ?? 0);
        $productId = (int) ($productData['product_id'] ?? 0);
        if ($productId > 0 && $quantity > 0) {
          try {
            WarehouseProductSyncService::adjustQuantity($productId, 'subtraction', $quantity);
          } catch (\Exception $e) {
            // Log error but don't fail the transaction
            Log::error("Failed to adjust product quantity for product {$productId}: " . $e->getMessage());
          }
        }
      }
    });

    Toastr::success('Order updated successfully!');
    return redirect()->route('order.list');
  }

  public function getCustomerBranches($customerId)
  {
    $customer = \App\Models\Customer::with('branches')->findOrFail($customerId);
    $branches = $customer->branches->map(function ($branch) {
      $addressText = $branch->name . ' - ' . $branch->address_line1;
      if ($branch->address_line2) {
        $addressText .= ', ' . $branch->address_line2;
      }
      $addressText .= ', ' . $branch->city;
      if ($branch->zip_code) {
        $addressText .= ' ' . $branch->zip_code;
      }
      if ($branch->country) {
        $addressText .= ', ' . $branch->country;
      }
      return [
        'id' => $branch->id,
        'text' => $addressText
      ];
    });

    return response()->json($branches);
  }

  public function ajaxList(Request $request)
  {
    $query = Order::select([
      'orders.id',
      'orders.order_number',
      'orders.type',
      'orders.parent_order_id',
      'orders.order_date',
      'orders.total_amount',
      'orders.paid_amount',
      'orders.unpaid_amount',
      'orders.vat_amount',
      'orders.payment_status',
      'orders.status as order_status',
      'customers.email as customer_email',
      'customers.company_name as customer_name',
      'parent_orders.type as parent_order_type',
      'parent_orders.order_number as parent_order_number',
      'credit_notes.type as credit_note_type',
      'credit_notes.order_number as credit_note_number'
    ])
      ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
      ->leftJoin('orders as parent_orders', 'parent_orders.id', '=', 'orders.parent_order_id')
      ->leftJoin('orders as credit_notes', function ($join) {
        $join->on('credit_notes.parent_order_id', '=', 'orders.id')
          ->where('credit_notes.type', '=', 'CN');
      })
      ->withCount(['creditNotes as has_credit_note_count']);

    // Apply filters here (reference_no, customer, date ranges)
    if ($request->filled('reference_no')) {

      $reference = ltrim($request->reference_no, '#');

      if (preg_match('/^(CN|SO|EST)(.*)$/i', $reference, $matches)) {

        $type = strtoupper($matches[1]);
        $number = $matches[2];

        $query->where(function ($q) use ($type, $number) {

          // Match main order
          $q->where(function ($sub) use ($type, $number) {
            $sub->where('orders.type', $type);

            if (!empty($number)) {
              $sub->where('orders.order_number', 'like', "%{$number}%");
            }
          })

            // OR match parent order
            ->orWhere(function ($sub) use ($type, $number) {
              $sub->where('parent_orders.type', $type);

              if (!empty($number)) {
                $sub->where('parent_orders.order_number', 'like', "%{$number}%");
              }
            })

            // OR match credit note
            ->orWhere(function ($sub) use ($type, $number) {
              $sub->where('credit_notes.type', $type);

              if (!empty($number)) {
                $sub->where('credit_notes.order_number', 'like', "%{$number}%");
              }
            });

        });

      } else {

        // No prefix → search by number everywhere
        $query->where(function ($q) use ($reference) {
          $q->where('orders.order_number', 'like', "%{$reference}%")
            ->orWhere('parent_orders.order_number', 'like', "%{$reference}%")
            ->orWhere('credit_notes.order_number', 'like', "%{$reference}%");
        });
      }
    }


    if ($request->has('customer') && !empty($request->customer)) {
      $query->where(function ($q) use ($request) {
        $q->where('customers.company_name', 'like', '%' . $request->customer . '%')
          ->orWhere('customers.email', 'like', '%' . $request->customer . '%');
      });
    }

    if ($request->has('start_date') && !empty($request->start_date)) {
      $startDate = Carbon::createFromFormat('d/m/Y', $request->start_date)
        ->format('Y-m-d');
      $query->where('orders.order_date', '>=', $startDate);
    }

    if ($request->has('end_date') && !empty($request->end_date)) {
      $endDate = Carbon::createFromFormat('d/m/Y', $request->end_date)
        ->format('Y-m-d');
      $query->where('orders.order_date', '<=', $endDate);
    }

    return DataTables::eloquent($query)
      ->filter(function ($query) use ($request) {

        $searchValue = $request->get('search')['value'] ?? '';

        if (!empty($searchValue)) {

          $searchValue = ltrim($searchValue, '#'); // remove #
  
          $query->where(function ($q) use ($searchValue) {

            $reference = $searchValue;

            if (preg_match('/^(CN|SO|EST)(.*)$/i', $reference, $matches)) {

              $type = strtoupper($matches[1]);
              $number = $matches[2];

              $q->where(function ($q) use ($type, $number) {

                // Match main order
                $q->where(function ($sub) use ($type, $number) {
                  $sub->where('orders.type', $type);

                  if (!empty($number)) {
                    $sub->where('orders.order_number', 'like', "%{$number}%");
                  }
                })

                  // OR match parent order
                  ->orWhere(function ($sub) use ($type, $number) {
                  $sub->where('parent_orders.type', $type);

                  if (!empty($number)) {
                    $sub->where('parent_orders.order_number', 'like', "%{$number}%");
                  }
                })

                  // OR match credit note
                  ->orWhere(function ($sub) use ($type, $number) {
                  $sub->where('credit_notes.type', $type);

                  if (!empty($number)) {
                    $sub->where('credit_notes.order_number', 'like', "%{$number}%");
                  }
                });

              });

            } else {

              // No prefix → search by number everywhere
              $q->where(function ($q) use ($reference) {
                $q->where('orders.order_number', 'like', "%{$reference}%")
                  ->orWhere('parent_orders.order_number', 'like', "%{$reference}%")
                  ->orWhere('credit_notes.order_number', 'like', "%{$reference}%");
              });
            }

            // Detect prefix
            // if (preg_match('/^(SO|CN|EST)(.*)$/i', $searchValue, $matches)) {
  
            //   $type = strtoupper($matches[1]); // SO / CN / EST
            //   $number = $matches[2];             // remaining number part
  
            //   $q->where(function ($sub) use ($type, $number) {
            //     $sub->where('orders.type', $type)
            //       ->where('orders.order_number', 'like', "%{$number}%");
            //   });
  
            // } else {
            //   // Normal search fallback
            //   $q->where('orders.order_number', 'like', "%{$searchValue}%");
            // }
  
            // Other searchable columns
            $q->orWhere('customers.email', 'like', "%{$searchValue}%")
              ->orWhere('customers.company_name', 'like', "%{$searchValue}%")
              ->orWhere(function ($dateQuery) use ($searchValue) {

              try {

                // Check if it contains time
                if (preg_match('/\d{1,2}\/\d{1,2}\/\d{4}\s+\d{1,2}:\d{2}(:\d{2})?/', $searchValue)) {

                  // Format: d/m/Y H:i:s
                  $date = Carbon::createFromFormat('d/m/Y H:i:s', $searchValue);

                  $dateQuery->where('orders.order_date', $date->format('Y-m-d H:i:s'));

                } elseif (preg_match('/\d{1,2}\/\d{1,2}\/\d{4}/', $searchValue, $matches)) {

                  // Date only
                  $date = Carbon::createFromFormat('d/m/Y', $matches[0]);

                  $dateQuery->whereDate('orders.order_date', $date->format('Y-m-d'));

                } else {

                  // Try generic parsing
                  $date = Carbon::parse($searchValue);

                  $dateQuery->whereDate('orders.order_date', $date->format('Y-m-d'));
                }

              } catch (\Exception $e) {

                $dateQuery->whereRaw(
                  "DATE_FORMAT(orders.order_date, '%d/%m/%Y %H:%i:%s') LIKE ?",
                  ["%{$searchValue}%"]
                );
              }
            });


          });
        }
      })

      ->addColumn('has_credit_note', function ($order) {
        return ($order->type === 'SO' && ($order->has_credit_note_count ?? 0) > 0) ? 1 : 0;
      })
      ->addColumn('parent_order_display', function ($order) {
        return ($order->type === 'CN' && $order->parent_order_type && $order->parent_order_number)
          ? $order->parent_order_type . $order->parent_order_number
          : null;
      })
      ->addColumn('credit_note_display', function ($order) {
        return ($order->type === 'SO' && $order->credit_note_type && $order->credit_note_number)
          ? $order->credit_note_type . $order->credit_note_number
          : null;
      })
      // Dynamic column-wise ordering
      ->order(function ($query) use ($request) {
        if ($request->has('order')) {
          $columnIndex = $request->order[0]['column'];
          $dir = $request->order[0]['dir'];

          // Map your column indexes to database columns
          $columns = [
            0 => 'orders.id',              // id
            // 1 => not orderable (select), skip
            2 => 'orders.order_date',      // order_date
            3 => 'orders.order_number',    // order_number
            4 => 'customers.company_name', // customer_name
            5 => 'orders.total_amount',    // total_amount
            6 => 'orders.paid_amount',     // paid_amount
            7 => 'orders.unpaid_amount',   // unpaid_amount
            8 => 'orders.vat_amount',      // vat_amount
            9 => 'orders.status',          // order_status
            10 => 'orders.payment_status',  // payment_status
            // 11 => has_credit_note (computed), skip
            12 => 'orders.id'               // last id column
          ];

          // If the requested column is 2 (order_date), override to id
          if ($columnIndex == 2) {
            $query->orderBy('orders.id', $dir);
          } elseif (isset($columns[$columnIndex])) {
            $query->orderBy($columns[$columnIndex], $dir);
          } else {
            $query->orderBy('orders.id', 'desc');
          }
        } else {
          $query->orderBy('orders.id', 'desc');
        }
      })

      ->toJson();
  }


  public function showAjax($id)
  {
    $order = Order::with(['items.product', 'customer', 'parentOrder', 'creditNotes.items.product', 'payments'])->findOrFail($id);

    // Get settings for store information
    $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();

    // Get currency symbol
    $currencySymbol = $settings['currency_symbol'] ?? '£';

    $html = view('_partials._modals.modal-order-show', compact('order', 'settings', 'currencySymbol'))->render();
    return response()->json(['html' => $html]);
  }

  public function showInvoice($id)
  {
    $order = Order::with(['items.product', 'customer', 'parentOrder', 'creditNotes.items.product', 'payments'])->findOrFail($id);

    // Get settings for store information
    $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();

    // Get currency symbol
    $currencySymbol = $settings['currency_symbol'] ?? '£';

    // Use EST invoice template for EST orders
    if ($order->type === 'EST') {
      return view('content.order.est-invoice', compact('order', 'settings', 'currencySymbol'));
    }

    return view('content.order.invoice', compact('order', 'settings', 'currencySymbol'));
  }

  /**
   * Generate PDF for invoice
   */
  public function generateInvoicePdf($id)
  {
    $order = Order::with(['items.product', 'customer', 'parentOrder', 'creditNotes.items.product', 'payments'])->findOrFail($id);

    // Get settings for store information
    $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();

    // Get currency symbol
    $currencySymbol = $settings['currency_symbol'] ?? '£';

    // Convert logo to base64 if exists
    $logoBase64 = null;
    if (isset($settings['company_logo']) && $settings['company_logo']) {
      $logoPath = storage_path('app/public/' . $settings['company_logo']);
      if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoMime = mime_content_type($logoPath);
        $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
      }
    }

    // Render the PDF view - use EST template for EST orders
    $viewName = ($order->type === 'EST') ? 'content.order.est-invoice-pdf' : 'content.order.invoice-pdf';
    $html = view($viewName, compact('order', 'settings', 'currencySymbol', 'logoBase64'))->render();

    // Configure Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = ($order->type === 'CN' ? 'CreditNote' : ($order->type === 'EST' ? 'Invoice' : 'Invoice')) . '_' . $order->order_number . '.pdf';

    return response($dompdf->output(), 200)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
  }

  /**
   * Send invoice email to customer
   */
  public function sendInvoiceEmail(Request $request, $id)
  {
    try {
      $order = Order::with(['items.product', 'customer', 'parentOrder', 'creditNotes.items.product', 'payments'])->findOrFail($id);

      // Get email addresses from request, or use customer email as fallback
      $emails = $request->input('emails', []);

      // If emails is a string (from JSON), convert to array
      if (is_string($emails)) {
        $emails = json_decode($emails, true) ?? [$emails];
      }

      // If no emails provided, try to use customer email
      if (empty($emails) || !is_array($emails)) {
        if ($order->customer && $order->customer->email) {
          $emails = [$order->customer->email];
        } else {
          return response()->json([
            'success' => false,
            'message' => 'No email addresses provided and customer email is not available.'
          ], 400);
        }
      }

      // Validate and clean email addresses
      $validEmails = [];
      foreach ($emails as $email) {
        $email = trim($email);
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $validEmails[] = $email;
        }
      }

      if (empty($validEmails)) {
        return response()->json([
          'success' => false,
          'message' => 'No valid email addresses provided.'
        ], 400);
      }

      // Get settings for store information
      $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();

      // Get currency symbol
      $currencySymbol = $settings['currency_symbol'] ?? '£';

      // Convert logo to base64 if exists
      $logoBase64 = null;
      if (isset($settings['company_logo']) && $settings['company_logo']) {
        $logoPath = storage_path('app/public/' . $settings['company_logo']);
        if (file_exists($logoPath)) {
          $logoData = file_get_contents($logoPath);
          $logoMime = mime_content_type($logoPath);
          $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
        }
      }

      // Generate PDF - use EST template for EST orders
      $viewName = ($order->type === 'EST') ? 'content.order.est-invoice-pdf' : 'content.order.invoice-pdf';
      $html = view($viewName, compact('order', 'settings', 'currencySymbol', 'logoBase64'))->render();
      // return $html;
      $options = new Options();
      $options->set('isHtml5ParserEnabled', true);
      $options->set('isRemoteEnabled', false);
      $options->set('defaultFont', 'DejaVu Sans');

      $dompdf = new Dompdf($options);
      $dompdf->loadHtml($html);
      $dompdf->setPaper('A4', 'portrait');
      $dompdf->render();

      $pdfContent = $dompdf->output();
      $filename = ($order->type === 'CN' ? 'CreditNote' : ($order->type === 'EST' ? 'Order Details' : 'Invoice')) . '_' . $order->order_number . '.pdf';

      // Get company email from settings or use default
      $fromEmail = $settings['company_email'] ?? config('mail.from.address');
      $fromName = $settings['company_name'] ?? config('mail.from.name');

      // Prepare email subject
      $invoiceType = $order->type === 'CN' ? 'Credit Note' : ($order->type === 'EST' ? 'Order Details' : 'Invoice');
      $invoiceNumber = $order->type === 'CN' ? 'CN' . $order->order_number : ($order->type === 'EST' ? 'EST' . $order->order_number : 'SO' . $order->order_number);
      $subject = $invoiceType . ' #' . $invoiceNumber;

      // Prepare email body
      $invoiceDate = optional($order->order_date)->format('d/m/Y') ?? optional($order->created_at)->format('d/m/Y');
      $body = "Dear " . ($order->customer->company_name ?? 'Customer') . ",\n\n";
      if ($order->type !== 'EST') {
        $body .= "Please find attached your " . strtolower($invoiceType) . " #" . $invoiceNumber . " dated " . $invoiceDate . ".\n\n";
      } else {
        $body .= "Please find attached your Order Details dated " . $invoiceDate . ".\n\n";
      }

      $body .= "Total Amount: " . $currencySymbol . number_format($order->total_amount ?? 0, 2) . "\n\n";
      $body .= "Thank you for your business!\n\n";
      $body .= "Best regards,\n" . ($settings['company_name'] ?? '');

      // Send email to all provided email addresses
      Mail::raw($body, function ($message) use ($validEmails, $fromEmail, $fromName, $subject, $pdfContent, $filename) {
        $message->from($fromEmail, $fromName)
          ->to($validEmails)
          ->subject($subject)
          ->attachData($pdfContent, $filename, [
            'mime' => 'application/pdf',
          ]);
      });

      $emailList = implode(', ', $validEmails);
      return response()->json([
        'success' => true,
        'message' => 'Invoice email sent successfully to: ' . $emailList
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error sending email: ' . $e->getMessage()
      ], 500);
    }
  }

  // public function delete($id)
  // {
  //   $order = Order::with(['items', 'creditNotes', 'payments', 'customer'])->findOrFail($id);

  //   DB::transaction(function () use ($order) {
  //     // Handle credit note deletion separately
  //     if ($order->type === 'CN') {
  //       // Get customer for credit balance adjustment
  //       $customer = $order->customer;

  //       // Calculate wallet credit that was added when credit note was created
  //       // This needs to be reversed (subtracted) when credit note is deleted
  //       $walletCreditToReverse = 0;

  //       if ($order->parent_order_id) {
  //         $parentOrder = Order::with('items')->find($order->parent_order_id);
  //         if ($parentOrder) {
  //           // Calculate wallet credit for returned items in credit note
  //           foreach ($order->items as $creditNoteItem) {
  //             $returnedQty = (float) ($creditNoteItem->quantity ?? 0);
  //             $productId = (int) ($creditNoteItem->product_id ?? 0);

  //             // Find corresponding item in parent order
  //             $parentOrderItem = $parentOrder->items->firstWhere('product_id', $productId);
  //             if ($parentOrderItem) {
  //               $originalQty = (float) ($parentOrderItem->quantity ?? 1);
  //               if ($originalQty > 0) {
  //                 // Calculate proportional wallet credit earned for returned items
  //                 $proportionalWalletCredit = ($parentOrderItem->wallet_credit_earned ?? 0) * ($returnedQty / $originalQty);
  //                 $walletCreditToReverse += round($proportionalWalletCredit, 2);
  //               }
  //             }
  //           }
  //         }
  //       }

  //       // Reverse the wallet credit that was added when credit note was created
  //       if ($walletCreditToReverse > 0 && $customer) {
  //         $customer->refresh();
  //         $currentBalance = (float) ($customer->credit_balance ?? 0);

  //         // Subtract the wallet credit (reverse what was added)
  //         $customer->credit_balance = $currentBalance - $walletCreditToReverse;
  //         $customer->save();

  //         // Create wallet transaction to record the reversal
  //         WalletTransaction::create([
  //           'customer_id' => $customer->id,
  //           'order_id' => $order->id,
  //           'amount' => $walletCreditToReverse,
  //           'type' => 'debit',
  //           'description' => 'Wallet credit reversed due to credit note deletion',
  //           'balance_after' => $customer->credit_balance,
  //         ]);
  //       }

  //       // Delete credit note items and decrease product quantities
  //       // When credit note is deleted, we need to reverse the addition that was done when it was created
  //       // So we subtract (decrease) the quantity
  //       foreach ($order->items as $item) {
  //         $quantity = (float) ($item->quantity ?? 0);
  //         $productId = (int) ($item->product_id ?? 0);
  //         if ($productId > 0 && $quantity > 0) {
  //           try {
  //             WarehouseProductSyncService::adjustQuantity($productId, 'subtraction', $quantity);
  //           } catch (\Exception $e) {
  //             Log::error("Failed to adjust product quantity for product {$productId}: " . $e->getMessage());
  //           }
  //         }
  //       }
  //     }

  //     // Delete all credit notes associated with this order (if SO order)
  //     if ($order->type === 'SO') {
  //       foreach ($order->creditNotes as $creditNote) {
  //         // Get customer for credit balance adjustment
  //         $customer = $creditNote->customer;

  //         // Calculate wallet credit that was added when credit note was created
  //         $walletCreditToReverse = 0;

  //         if ($creditNote->parent_order_id) {
  //           $parentOrder = Order::with('items')->find($creditNote->parent_order_id);
  //           if ($parentOrder) {
  //             // Calculate wallet credit for returned items in credit note
  //             foreach ($creditNote->items as $creditNoteItem) {
  //               $returnedQty = (float) ($creditNoteItem->quantity ?? 0);
  //               $productId = (int) ($creditNoteItem->product_id ?? 0);

  //               // Find corresponding item in parent order
  //               $parentOrderItem = $parentOrder->items->firstWhere('product_id', $productId);
  //               if ($parentOrderItem) {
  //                 $originalQty = (float) ($parentOrderItem->quantity ?? 1);
  //                 if ($originalQty > 0) {
  //                   // Calculate proportional wallet credit earned for returned items
  //                   $proportionalWalletCredit = ($parentOrderItem->wallet_credit_earned ?? 0) * ($returnedQty / $originalQty);
  //                   $walletCreditToReverse += round($proportionalWalletCredit, 2);
  //                 }
  //               }
  //             }
  //           }
  //         }

  //         // Reverse the wallet credit that was added when credit note was created
  //         if ($walletCreditToReverse > 0 && $customer) {
  //           $customer->refresh();
  //           $currentBalance = (float) ($customer->credit_balance ?? 0);

  //           // Subtract the wallet credit (reverse what was added)
  //           $customer->credit_balance = $currentBalance - $walletCreditToReverse;
  //           $customer->save();

  //           // Create wallet transaction to record the reversal
  //           WalletTransaction::create([
  //             'customer_id' => $customer->id,
  //             'order_id' => $creditNote->id,
  //             'amount' => $walletCreditToReverse,
  //             'type' => 'debit',
  //             'description' => 'Wallet credit reversed due to credit note deletion',
  //             'balance_after' => $customer->credit_balance,
  //           ]);
  //         }

  //         // Delete credit note items and decrease product quantities
  //         // When credit note is deleted, we need to reverse the addition that was done when it was created
  //         // So we subtract (decrease) the quantity
  //         foreach ($creditNote->items as $item) {
  //           $quantity = (float) ($item->quantity ?? 0);
  //           $productId = (int) ($item->product_id ?? 0);
  //           if ($productId > 0 && $quantity > 0) {
  //             try {
  //               WarehouseProductSyncService::adjustQuantity($productId, 'subtraction', $quantity);
  //             } catch (\Exception $e) {
  //               Log::error("Failed to adjust product quantity for product {$productId}: " . $e->getMessage());
  //             }
  //           }
  //         }
  //         // Delete credit note payments
  //         $creditNote->payments()->delete();
  //         // Delete credit note items
  //         $creditNote->items()->delete();
  //         // Delete credit note status histories
  //         $creditNote->statusHistories()->delete();
  //         // Delete credit note
  //         $creditNote->delete();
  //       }
  //     }

  //     // Delete all payments for this order
  //     $order->payments()->delete();

  //     // Restore product quantities before deleting items (skip CN and EST orders)
  //     // CN orders are handled above, EST orders should not affect quantities
  //     if ($order->type !== 'CN' && $order->type !== 'EST') {
  //       foreach ($order->items as $item) {
  //         $quantity = (float) ($item->quantity ?? 0);
  //         $productId = (int) ($item->product_id ?? 0);
  //         if ($productId > 0 && $quantity > 0) {
  //           try {
  //             WarehouseProductSyncService::adjustQuantity($productId, 'addition', $quantity);
  //           } catch (\Exception $e) {
  //             // Log error but don't fail the transaction
  //             Log::error("Failed to restore product quantity for product {$productId}: " . $e->getMessage());
  //           }
  //         }
  //       }
  //     }

  //     // Delete order items
  //     $order->items()->delete();
  //     // Delete status histories
  //     $order->statusHistories()->delete();
  //     // Delete the order
  //     $order->delete();
  //   });

  //   Toastr::success('Order deleted successfully');
  //   return redirect()->back();
  // }

  /**
   * Create a new order item
   */

  public function delete($id)
{
    $order = Order::findOrFail($id);

    $this->orderDeletionService->delete($order);

    Toastr::success('Order deleted successfully');

    return redirect()->back();
}

  public function createItem(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'order_id' => ['required', 'integer', 'exists:orders,id'],
        'product_id' => ['required', 'integer', 'exists:products,id'],
        'quantity' => ['required', 'integer', 'min:1'],
        'unit_price' => ['required', 'numeric', 'min:0.01'],
      ]);

      if ($validator->fails()) {
        return back()
          ->withErrors($validator, 'addItemModal')
          ->withInput();
      }

      $validated = $validator->validated();

      // Check if order has credit notes - if so, prevent update
      $order = Order::with('creditNotes')->find($validated['order_id']);
      if ($order && $order->type === 'SO' && $order->creditNotes()->exists()) {
        Toastr::error('This order cannot be updated because a credit note has been generated for it.');
        return back()->withInput();
      }

      $product = Product::with('unit')->findOrFail($validated['product_id']);

      // Get product unit name
      $productUnit = $product->unit ? $product->unit->name : null;

      // Calculate wallet credit: unit_wallet_credit = product wallet_credit, wallet_credit_earned = unit_wallet_credit * quantity
      $unitWalletCredit = round((float) ($product->wallet_credit ?? 0), 2);
      $walletCreditEarned = round($unitWalletCredit * $validated['quantity'], 2);

      // Calculate total price
      $totalPrice = $validated['unit_price'] * $validated['quantity'];

      DB::beginTransaction();

      // Get order to determine type
      $order = Order::findOrFail($validated['order_id']);
      $itemType = $order->type ?? 'SO';

      // Calculate VAT: if order type is EST, VAT is 0 (included in price), otherwise use product vat_amount
      if ($itemType === 'EST') {
        $unitVat = 0; // VAT is included in the sale price for EST orders
      } else {
        $unitVat = round((float) ($product->vat_amount ?? 0), 2);
      }
      $totalVat = round($unitVat * $validated['quantity'], 2);

      // Calculate total: total_price + total_vat
      $total = round($totalPrice + $totalVat, 2);

      // Create the order item
      $orderItem = OrderItem::create([
        'order_id' => $validated['order_id'],
        'type' => $itemType,
        'product_id' => $validated['product_id'],
        'product_unit' => $productUnit,
        'quantity' => $validated['quantity'],
        'unit_price' => $validated['unit_price'],
        'unit_vat' => $unitVat,
        'unit_wallet_credit' => $unitWalletCredit,
        'wallet_credit_earned' => $walletCreditEarned,
        'total_price' => $totalPrice,
        'total_vat' => $totalVat,
        'total' => $total,
      ]);

      // Update order totals
      $this->updateOrderTotals($validated['order_id']);

      DB::commit();

      Toastr::success('Order item added successfully!');
      return back();

    } catch (\Exception $e) {
      DB::rollBack();
      Toastr::error('Error adding order item: ' . $e->getMessage());
      return back()->withInput();
    }
  }

  /**
   * Update an existing order item
   */
  public function updateItem(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'id' => ['required', 'integer', 'exists:order_items,id'],
        'quantity' => ['required', 'integer', 'min:1'],
        'unit_price' => ['required', 'numeric', 'min:0.01'],
      ]);

      if ($validator->fails()) {
        return back()
          ->withErrors($validator, 'editItemModal')
          ->withInput();
      }

      $validated = $validator->validated();

      $orderItem = OrderItem::findOrFail($validated['id']);

      // Check if order has credit notes - if so, prevent update
      $order = $orderItem->order;
      if ($order && $order->type === 'SO' && $order->creditNotes()->exists()) {
        Toastr::error('This order cannot be updated because a credit note has been generated for it.');
        return back()->withInput();
      }

      $product = $orderItem->product;

      // Get product unit name
      $productUnit = $product && $product->unit ? $product->unit->name : ($orderItem->product_unit ?? null);

      // Calculate wallet credit: unit_wallet_credit = product wallet_credit, wallet_credit_earned = unit_wallet_credit * quantity
      $unitWalletCredit = round((float) ($product->wallet_credit ?? 0), 2);
      $walletCreditEarned = round($unitWalletCredit * $validated['quantity'], 2);

      // Calculate total price
      $totalPrice = $validated['unit_price'] * $validated['quantity'];

      // Calculate VAT: if order type is EST, VAT is 0 (included in price), otherwise use product vat_amount
      if ($order && $order->type === 'EST') {
        $unitVat = 0; // VAT is included in the sale price for EST orders
      } else {
        $unitVat = round((float) ($product->vat_amount ?? 0), 2);
      }
      $totalVat = round($unitVat * $validated['quantity'], 2);

      // Calculate total: total_price + total_vat
      $total = round($totalPrice + $totalVat, 2);

      DB::beginTransaction();

      // Update the order item
      $orderItem->update([
        'quantity' => $validated['quantity'],
        'unit_price' => $validated['unit_price'],
        'product_unit' => $productUnit,
        'unit_vat' => $unitVat,
        'unit_wallet_credit' => $unitWalletCredit,
        'wallet_credit_earned' => $walletCreditEarned,
        'total_price' => $totalPrice,
        'total_vat' => $totalVat,
        'total' => $total,
      ]);

      // Update order totals
      $this->updateOrderTotals($orderItem->order_id);

      DB::commit();

      Toastr::success('Order item updated successfully!');
      return back();

    } catch (\Exception $e) {
      DB::rollBack();
      Toastr::error('Error updating order item: ' . $e->getMessage());
      return back()->withInput();
    }
  }

  /**
   * Delete an order item
   */
  public function deleteItem($id)
  {
    try {
      $orderItem = OrderItem::findOrFail($id);
      $orderId = $orderItem->order_id;

      // Check if order has credit notes - if so, prevent update
      $order = Order::with('creditNotes')->find($orderId);
      if ($order && $order->type === 'SO' && $order->creditNotes()->exists()) {
        return response()->json([
          'success' => false,
          'message' => 'This order cannot be updated because a credit note has been generated for it.'
        ], 403);
      }

      DB::beginTransaction();

      $orderItem->delete();

      // Update order totals
      $this->updateOrderTotals($orderId);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Order item deleted successfully!'
      ]);

    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Error deleting order item: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Update order totals based on order items
   */
  private function updateOrderTotals($orderId, $preservePaymentAmounts = false)
  {
    $order = Order::findOrFail($orderId);
    $items = $order->items()->with('product')->get();

    // Calculate totals
    $subtotal = $items->sum('total_price');
    $vatAmount = $items->sum('total_vat'); // Use total_vat from order_items
    $walletCreditEarned = $items->sum('wallet_credit_earned');
    $itemsCount = $items->count();
    $unitsCount = $items->sum('quantity');
    $skusCount = $items->pluck('product_id')->unique()->count();

    $totalAmount = $subtotal + $vatAmount;

    // If preserving payment amounts (for credit notes), keep original payment values
    if ($preservePaymentAmounts) {
      // Update only non-payment related fields
      $order->update([
        'subtotal' => $subtotal,
        'vat_amount' => $vatAmount,
        'total_amount' => $totalAmount,
        'items_count' => $itemsCount,
        'units_count' => $unitsCount,
        'skus_count' => $skusCount,
      ]);
      return;
    }

    // Calculate paid and unpaid amounts
    // outstanding_amount = total_amount - wallet_credit_used (always)
    // payment_amount = outstanding_amount (always)
    // payment_amount = paid_amount + unpaid_amount
    $walletCreditUsed = (float) ($order->wallet_credit_used ?? 0);
    $paymentsTotal = (float) ($order->payments()->sum('amount') ?? 0);
    $outstandingAmount = $totalAmount - $walletCreditUsed;
    $paymentAmount = $outstandingAmount; // payment_amount always equals outstanding_amount
    $paidAmount = $paymentsTotal; // paid_amount only includes actual payments, not wallet_credit_used
    $unpaidAmount = $outstandingAmount - $paidAmount; // unpaid_amount = outstanding_amount - paid_amount

    // Determine payment status
    $paymentStatus = 'Due';
    if ($unpaidAmount <= 0) {
      $paymentStatus = 'Paid';
    } elseif ($paidAmount > 0) {
      $paymentStatus = 'Partial';
    }

    // Update order
    $order->update([
      'subtotal' => $subtotal,
      'vat_amount' => $vatAmount,
      'total_amount' => $totalAmount,
      'payment_amount' => $paymentAmount,
      'paid_amount' => $paidAmount,
      'unpaid_amount' => $unpaidAmount,
      'outstanding_amount' => $outstandingAmount,
      'payment_status' => $paymentStatus,
      'items_count' => $itemsCount,
      'units_count' => $unitsCount,
      'skus_count' => $skusCount,
    ]);
  }

  /**
   * Add payment to an order
   */
  public function addPayment(Request $request)
  {
    try {
      $validated = $request->validate([
        'order_id' => ['required', 'integer', 'exists:orders,id'],
        'date' => [
          'required',
          function ($attribute, $value, $fail) {
            if (empty($value)) {
              return;
            }
            // Try to parse d/m/Y H:i format (e.g., "12/11/2025 12:06")
            if (strpos($value, '/') !== false) {
              try {
                $parsed = Carbon::createFromFormat('d/m/Y H:i', $value);
                if ($parsed === false) {
                  try {
                    $parsed = Carbon::createFromFormat('d/m/Y', $value);
                    if ($parsed === false) {
                      $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format.');
                    }
                  } catch (\Exception $e) {
                    $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format.');
                  }
                }
              } catch (\Exception $e) {
                try {
                  $parsed = Carbon::createFromFormat('d/m/Y', $value);
                  if ($parsed === false) {
                    $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format.');
                  }
                } catch (\Exception $e2) {
                  $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format.');
                }
              }
            } else {
              // Try standard date formats
              try {
                $parsed = Carbon::parse($value);
                if ($parsed === false) {
                  $fail('Date must be a valid date.');
                }
              } catch (\Exception $e) {
                $fail('Date must be a valid date.');
              }
            }
          }
        ],
        'amount' => ['required', 'numeric', 'min:1'],
        'payment_method' => ['required', 'string', 'in:Cash,Bank,Outstanding'],
        'note' => ['nullable', 'string'],
      ], [
        'order_id.required' => 'Order ID is required',
        'order_id.exists' => 'Order not found',
        'date.required' => 'Date is required',
        'amount.required' => 'Amount is required',
        'amount.numeric' => 'Amount must be a number',
        'amount.min' => 'Amount must be at least 1',
        'payment_method.required' => 'Paying by is required',
        'payment_method.in' => 'Paying by must be Cash, Bank, or Outstanding',
      ]);

      $order = Order::findOrFail($validated['order_id']);

      // Refresh order to ensure we have the latest unpaid_amount
      $order->refresh();

      // Calculate current unpaid amount (before adding this payment)
      // outstanding_amount = total_amount - wallet_credit_used (always)
      // payment_amount = outstanding_amount (always)
      // unpaid_amount = outstanding_amount - paid_amount
      $walletCreditUsed = (float) ($order->wallet_credit_used ?? 0);
      $existingPaymentsTotal = (float) ($order->payments()->sum('amount') ?? 0);
      $outstandingAmount = ($order->total_amount ?? 0) - $walletCreditUsed;
      $paidAmount = $existingPaymentsTotal; // paid_amount only includes actual payments, not wallet_credit_used
      $unpaidAmount = $outstandingAmount - $paidAmount;

      // Validate that payment amount doesn't exceed unpaid amount
      if ($validated['amount'] > $unpaidAmount) {
        return response()->json([
          'success' => false,
          'message' => 'Validation failed',
          'errors' => [
            'amount' => ['Amount cannot be greater than payable amount (' . number_format($unpaidAmount, 2, '.', '') . ')']
          ]
        ], 422);
      }

      // Parse date
      $date = $validated['date'];
      if (strpos($date, '/') !== false) {
        try {
          $date = Carbon::createFromFormat('d/m/Y H:i', $date);
        } catch (\Exception $e) {
          $date = Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
        }
      } else {
        $date = Carbon::parse($date);
      }

      // Get reference number from order_ref table (pay column)
      $orderRef = OrderRef::orderBy('id', 'desc')->first();

      if (!$orderRef) {
        // Create initial order_ref record if it doesn't exist
        $orderRef = OrderRef::create([
          'so' => 1,
          'qa' => 1,
          'po' => 1,
          'pay' => 1,
        ]);
      }

      $referenceNo = $orderRef->pay ?? 1;

      // Increment pay for next payment
      $orderRef->update([
        'pay' => ($orderRef->pay ?? 0) + 1,
      ]);

      // Create payment in transaction
      DB::transaction(function () use ($validated, $date, $order, $referenceNo) {
        // Create payment
        $payment = \App\Models\Payment::create([
          'order_id' => $validated['order_id'],
          'date' => $date,
          'reference_no' => $referenceNo,
          'amount' => $validated['amount'],
          'payment_method' => $validated['payment_method'],
          'note' => $validated['note'] ?? null,
          'user_id' => auth()->id(),
        ]);

        // Refresh order to ensure we have latest data
        $order->refresh();

        // Update order totals (this will recalculate paid_amount including the new payment)
        $this->updateOrderTotals($order->id);
      });

      return response()->json([
        'success' => true,
        'message' => 'Payment added successfully!'
      ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error adding payment: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get payments for an order
   */
  public function getPayments($orderId)
  {
    try {
      $order = Order::findOrFail($orderId);

      $payments = \App\Models\Payment::where('order_id', $orderId)
        ->orderBy('date', 'desc')
        ->get()
        ->map(function ($payment) {
          return [
            'id' => $payment->id,
            'order_id' => $payment->order_id,
            'date' => $payment->date->toISOString(),
            'reference_no' => $payment->reference_no,
            'amount' => (float) $payment->amount,
            'payment_method' => $payment->payment_method,
            'note' => $payment->note,
          ];
        });

      return response()->json([
        'success' => true,
        'payments' => $payments
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error fetching payments: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Delete a payment
   */
  public function deletePayment($paymentId)
  {
    try {
      $payment = \App\Models\Payment::findOrFail($paymentId);
      $orderId = $payment->order_id;

      DB::transaction(function () use ($payment, $orderId) {
        // Delete payment
        $payment->delete();

        // Update order totals to recalculate paid_amount
        $this->updateOrderTotals($orderId);
      });

      return response()->json([
        'success' => true,
        'message' => 'Payment deleted successfully!'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error deleting payment: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get order statistics for dashboard widgets
   */
  public function getStatistics()
  {
    try {
      // Calculate statistics for SO orders
      $soStats = Order::selectRaw('
        COALESCE(SUM(total_amount), 0) as grand_total,
        COALESCE(SUM(paid_amount), 0) as paid,
        COALESCE(SUM(unpaid_amount), 0) as balance,
        SUM(CASE WHEN payment_status = "Due" THEN 1 ELSE 0 END) as due_count,
        SUM(CASE WHEN payment_status = "Partial" THEN 1 ELSE 0 END) as partial_count,
        SUM(CASE WHEN payment_status = "Paid" THEN 1 ELSE 0 END) as paid_count
      ')
        ->where('type', 'SO')
        ->first();

      // Calculate statistics for CN orders
      $cnStats = Order::selectRaw('
        COALESCE(SUM(total_amount), 0) as grand_total,
        COALESCE(SUM(paid_amount), 0) as paid,
        COALESCE(SUM(unpaid_amount), 0) as balance,
        SUM(CASE WHEN payment_status = "Due" THEN 1 ELSE 0 END) as due_count,
        SUM(CASE WHEN payment_status = "Partial" THEN 1 ELSE 0 END) as partial_count,
        SUM(CASE WHEN payment_status = "Paid" THEN 1 ELSE 0 END) as paid_count
      ')
        ->where('type', 'CN')
        ->first();

      // Calculate statistics as SO - CN
      $soGrandTotal = (float) ($soStats->grand_total ?? 0);
      $cnGrandTotal = (float) ($cnStats->grand_total ?? 0);
      $soPaid = (float) ($soStats->paid ?? 0);
      $cnPaid = (float) ($cnStats->paid ?? 0);
      $soBalance = (float) ($soStats->balance ?? 0);
      $cnBalance = (float) ($cnStats->balance ?? 0);

      return response()->json([
        'success' => true,
        'statistics' => [
          'grand_total' => $soGrandTotal - $cnGrandTotal,
          'paid' => $soPaid - $cnPaid,
          'balance' => $soBalance - $cnBalance,
          'due_count_so' => (int) ($soStats->due_count ?? 0),
          'partial_count_so' => (int) ($soStats->partial_count ?? 0),
          'paid_count_so' => (int) ($soStats->paid_count ?? 0),
          'due_count_cn' => (int) ($cnStats->due_count ?? 0),
          'partial_count_cn' => (int) ($cnStats->partial_count ?? 0),
          'paid_count_cn' => (int) ($cnStats->paid_count ?? 0),
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error fetching statistics: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Show credit note add form
   */
  public function creditNoteAdd($id)
  {
    $order = Order::with(['customer', 'items.product', 'creditNotes'])->findOrFail($id);

    // Validate that this is an SO order
    if ($order->type !== 'SO') {
      Toastr::error('Credit notes can only be created for Sales Orders (SO).');
      return redirect()->route('order.list');
    }

    // Validate that this SO order doesn't already have a credit note
    if ($order->creditNotes()->exists()) {
      Toastr::error('A credit note has already been generated for this order.');
      return redirect()->route('order.list');
    }

    return view('content.order.credit-note-add', [
      'order' => $order
    ]);
  }

  /**
   * Store credit note
   */
  public function creditNoteStore(Request $request)
  {
    $validated = $request->validate([
      'order_id' => ['required', 'integer', 'exists:orders,id'],
      'products' => ['required', 'array', 'min:1'],
      'products.*.product_id' => ['required', 'exists:products,id'],
      'products.*.returned_quantity' => ['required', 'numeric', 'min:0'],
      'products.*.order_quantity' => ['required', 'numeric', 'min:1'],
      'products.*.unit_price' => ['required', 'numeric', 'min:0'],
    ], [
      'order_id.required' => 'Order ID is required',
      'order_id.exists' => 'Order not found',
      'products.required' => 'At least one product is required',
      'products.min' => 'At least one product is required',
      'products.*.product_id.required' => 'Product is required',
      'products.*.product_id.exists' => 'Selected product does not exist',
      'products.*.returned_quantity.required' => 'Returned quantity is required',
      'products.*.returned_quantity.numeric' => 'Returned quantity must be a number',
      'products.*.returned_quantity.min' => 'Returned quantity must be 0 or greater',
      'products.*.order_quantity.required' => 'Order quantity is required',
      'products.*.unit_price.required' => 'Unit price is required',
    ]);

    $order = Order::with(['items', 'creditNotes'])->findOrFail($validated['order_id']);

    // Validate that this is an SO order
    if ($order->type !== 'SO') {
      Toastr::error('Credit notes can only be created for Sales Orders (SO).');
      return redirect()->back()->withInput();
    }

    // Validate that this SO order doesn't already have a credit note
    if ($order->creditNotes()->exists()) {
      Toastr::error('A credit note has already been generated for this order.');
      return redirect()->back()->withInput();
    }

    // Validate that returned quantity doesn't exceed order quantity
    $validationErrors = [];
    foreach ($validated['products'] as $index => $productData) {
      $productId = (int) ($productData['product_id'] ?? 0);
      $returnedQty = (float) ($productData['returned_quantity'] ?? 0);
      $orderQty = (float) ($productData['order_quantity'] ?? 0);

      // Find the original order item
      $orderItem = $order->items->firstWhere('product_id', $productId);
      if (!$orderItem) {
        $validationErrors["products.{$index}.product_id"] = "Product not found in order";
        continue;
      }

      if ($returnedQty > $orderQty) {
        $product = Product::find($productId);
        $productName = $product ? $product->name : 'Product #' . $productId;
        $validationErrors["products.{$index}.returned_quantity"] = "Returned quantity for {$productName} cannot exceed order quantity ({$orderQty})";
      }
    }

    if (!empty($validationErrors)) {
      return redirect()->back()
        ->withErrors($validationErrors)
        ->withInput();
    }

    // Check if at least one returned quantity is greater than 0
    $hasReturnedItems = false;
    foreach ($validated['products'] as $productData) {
      if ((float) ($productData['returned_quantity'] ?? 0) > 0) {
        $hasReturnedItems = true;
        break;
      }
    }

    if (!$hasReturnedItems) {
      return redirect()->back()
        ->withErrors(['products' => 'At least one returned quantity must be greater than 0'])
        ->withInput();
    }

    // Get reference number from order_ref table (cn column)
    $orderRef = OrderRef::orderBy('id', 'desc')->first();

    if (!$orderRef) {
      // Create initial order_ref record if it doesn't exist
      $orderRef = OrderRef::create([
        'so' => 1,
        'qa' => 1,
        'po' => 1,
        'pay' => 1,
        'cn' => 1,
      ]);
    }

    $referenceNo = $orderRef->cn ?? 1;

    // Increment cn for next credit note
    $orderRef->update([
      'cn' => ($orderRef->cn ?? 0) + 1,
    ]);

    // Get customer for wallet credit adjustments
    $customer = \App\Models\Customer::findOrFail($order->customer_id);

    // Process credit note creation in transaction
    DB::transaction(function () use ($validated, $order, $referenceNo, $customer) {
      // Get original order display number (with type prefix if exists)
      $originalOrderDisplay = $order->type ? $order->type . $order->order_number : $order->order_number;

      // Create credit note order (similar structure to regular order)
      $creditNoteData = [
        'customer_id' => $order->customer_id,
        'parent_order_id' => $order->id, // Link to parent SO order
        'order_date' => now(),
        'order_number' => $referenceNo, // Store without prefix
        'type' => 'CN',
        'delivery_charge' => 0,
        'delivery_note' => 'Credit Note for Order #' . $originalOrderDisplay,
        'status' => 'Returned',
        'payment_status' => 'Due',
        'subtotal' => 0,
        'vat_amount' => 0,
        'total_amount' => 0,
        'paid_amount' => 0,
        'unpaid_amount' => 0,
        'outstanding_amount' => 0,
        'items_count' => 0,
        'units_count' => 0,
        'skus_count' => 0,
        'wallet_credit_used' => 0,
        'branch_name' => $order->branch_name,
        'country' => $order->country,
        'address_line1' => $order->address_line1,
        'address_line2' => $order->address_line2,
        'city' => $order->city,
        'zip_code' => $order->zip_code,
      ];

      $creditNote = Order::create($creditNoteData);

      // Calculate total wallet credit to reverse (for returned items)
      $walletCreditToReverse = 0;

      // Create credit note items (do not modify original SO order or order_items)
      $subtotal = 0;
      foreach ($validated['products'] as $productData) {
        $productId = (int) ($productData['product_id'] ?? 0);
        $returnedQty = (float) ($productData['returned_quantity'] ?? 0);
        $unitPrice = (float) ($productData['unit_price'] ?? 0);

        if ($returnedQty <= 0) {
          continue; // Skip items with zero returned quantity
        }

        // Verify the product exists in the original order (for validation only)
        $orderItem = $order->items->firstWhere('product_id', $productId);
        if (!$orderItem) {
          continue;
        }

        // Calculate wallet credit to reverse for returned items
        // Get the proportion of returned quantity to original quantity
        $originalQty = (float) ($orderItem->quantity ?? 1);
        $returnedQtyFloat = (float) $returnedQty;
        if ($originalQty > 0) {
          // Calculate proportional wallet credit earned for returned items
          $proportionalWalletCredit = ($orderItem->wallet_credit_earned ?? 0) * ($returnedQtyFloat / $originalQty);
          $walletCreditToReverse += round($proportionalWalletCredit, 2);
        }

        // Calculate total price for returned items
        $totalPrice = round($unitPrice * $returnedQty, 2);

        // Calculate VAT: unit_vat = product vat_amount, total_vat = unit_vat * quantity
        $product = Product::with('unit')->find($productId);
        $unitVat = $product ? round((float) ($product->vat_amount ?? 0), 2) : 0;
        $totalVat = round($unitVat * $returnedQty, 2);

        // Get product unit name (use from product or from original order item)
        $productUnit = $product && $product->unit ? $product->unit->name : ($orderItem->product_unit ?? null);

        // Calculate wallet credit from original order item (proportional to returned quantity)
        $unitWalletCredit = 0;
        $walletCreditEarned = 0;

        if ($originalQty > 0) {
          // Get unit_wallet_credit from original order item
          $unitWalletCredit = round((float) ($orderItem->unit_wallet_credit ?? 0), 2);

          // Calculate proportional wallet_credit_earned for returned items
          $originalWalletCreditEarned = (float) ($orderItem->wallet_credit_earned ?? 0);
          $walletCreditEarned = round($originalWalletCreditEarned * ($returnedQtyFloat / $originalQty), 2);
        }

        $subtotal += $totalPrice;

        // Calculate total: total_price + total_vat
        $total = round($totalPrice + $totalVat, 2);

        // Create credit note item (independent of original order)
        OrderItem::create([
          'order_id' => $creditNote->id,
          'type' => 'CN',
          'product_id' => $productId,
          'product_unit' => $productUnit,
          'quantity' => $returnedQty,
          'unit_price' => $unitPrice,
          'unit_vat' => $unitVat,
          'unit_wallet_credit' => $unitWalletCredit,
          'wallet_credit_earned' => $walletCreditEarned,
          'total_price' => $totalPrice,
          'total_vat' => $totalVat,
          'total' => $total,
        ]);

        // Restore product quantity to warehouse (inventory management, not order modification)
        if ($productId > 0 && $returnedQty > 0) {
          try {
            WarehouseProductSyncService::adjustQuantity($productId, 'addition', $returnedQty);
          } catch (\Exception $e) {
            Log::error("Failed to restore product quantity for product {$productId}: " . $e->getMessage());
          }
        }
      }

      // Refresh credit note to get new items
      $creditNote->refresh();
      $creditNote->load('items');

      // Update credit note totals using the same method for consistency
      // Note: Credit notes have no wallet credit

      $creditNoteSubtotal = $creditNote->items->sum('total_price');
      $creditNoteVatAmount = $creditNote->items->sum('total_vat'); // Sum total_vat from order_items
      $creditNoteTotalAmount = $creditNoteSubtotal + $creditNoteVatAmount;
      $creditNote->subtotal = $creditNoteSubtotal;
      $creditNote->vat_amount = $creditNoteVatAmount;
      $creditNote->total_amount = $creditNoteTotalAmount;
      // For credit notes: outstanding_amount = total_amount
      $creditNote->outstanding_amount = $creditNoteTotalAmount;
      $creditNote->payment_amount = $creditNoteTotalAmount; // payment_amount = outstanding_amount
      $creditNote->paid_amount = 0; // No payments yet
      $creditNote->unpaid_amount = $creditNoteTotalAmount; // unpaid_amount = outstanding_amount - paid_amount
      $creditNote->items_count = $creditNote->items->count();
      $creditNote->units_count = $creditNote->items->sum('quantity');
      $creditNote->skus_count = $creditNote->items->pluck('product_id')->unique()->count();
      $creditNote->save();

      // Do not apply wallet credit earning when credit note is created
      // Wallet credit information is stored in order_items for reference only
      // The customer's credit_balance should not be modified when creating a credit note
      $customer->credit_balance = $customer->credit_balance - ($creditNote?->items?->sum('wallet_credit_earned') ?? 0);
      $customer->save();

      WalletTransaction::create([
        'customer_id' => $customer->id,
        'order_id' => $creditNote->id,
        'amount' => $creditNote?->items?->sum('wallet_credit_earned') ?? 0,
        'type' => 'debit',
        'description' => 'Wallet credit reversed due to credit note creation',
        'balance_after' => $customer->credit_balance,
      ]);
      // Do not modify original SO order or order_items - credit notes are independent records
    });

    Toastr::success('Credit note created successfully!');
    return redirect()->route('order.list');
  }

  public function deleteMultiple(Request $request){

    DB::transaction(function () use ($request) {

        $orders = Order::whereIn('id', $request->ids)->get();

        foreach ($orders as $order) {
            $this->orderDeletionService->delete($order);
        }
    });
     return response()->json(['success' => true]);
  }
}
