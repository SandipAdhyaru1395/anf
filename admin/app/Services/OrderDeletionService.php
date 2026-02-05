<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WarehouseProductSyncService;

class OrderDeletionService
{
    public function delete(Order $order): void
    {
        DB::transaction(function () use ($order) {

            $order->load([
                'items',
                'creditNotes.items',
                'creditNotes.customer',
                'payments',
                'customer'
            ]);

            // If Credit Note
            if ($order->type === 'CN') {
                $this->handleCreditNoteDeletion($order);
            }

            // If Sales Order (delete related credit notes)
            if ($order->type === 'SO') {
                foreach ($order->creditNotes as $creditNote) {
                    $this->handleCreditNoteDeletion($creditNote);
                    $this->deleteOrderRelations($creditNote);
                    $creditNote->delete();
                }
            }

            // Delete order payments
            $order->payments()->delete();

            // Restore quantities (only for normal orders)
            if (!in_array($order->type, ['CN', 'EST'])) {
                $this->restoreProductQuantities($order);
            }

            $this->deleteOrderRelations($order);

            $order->delete();
        });
    }

    private function handleCreditNoteDeletion(Order $creditNote): void
    {
        $customer = $creditNote->customer;
        $walletCreditToReverse = 0;

        if ($creditNote->parent_order_id) {
            $parentOrder = Order::with('items')
                ->find($creditNote->parent_order_id);

            if ($parentOrder) {
                foreach ($creditNote->items as $item) {

                    $returnedQty = (float) $item->quantity;
                    $productId   = (int) $item->product_id;

                    $parentItem = $parentOrder->items
                        ->firstWhere('product_id', $productId);

                    if ($parentItem && $parentItem->quantity > 0) {

                        $proportionalWalletCredit =
                            ($parentItem->wallet_credit_earned ?? 0)
                            * ($returnedQty / $parentItem->quantity);

                        $walletCreditToReverse += round($proportionalWalletCredit, 2);
                    }
                }
            }
        }

        // Reverse wallet balance
        if ($walletCreditToReverse > 0 && $customer) {

            $customer->refresh();

            $customer->credit_balance =
                (float) $customer->credit_balance - $walletCreditToReverse;

            $customer->save();

            WalletTransaction::create([
                'customer_id'  => $customer->id,
                'order_id'     => $creditNote->id,
                'amount'       => $walletCreditToReverse,
                'type'         => 'debit',
                'description'  => 'Wallet credit reversed due to credit note deletion',
                'balance_after'=> $customer->credit_balance,
            ]);
        }

        // Reverse stock (subtract)
        foreach ($creditNote->items as $item) {

            if ($item->product_id && $item->quantity > 0) {
                try {
                    WarehouseProductSyncService::adjustQuantity(
                        $item->product_id,
                        'subtraction',
                        $item->quantity
                    );
                } catch (\Exception $e) {
                    Log::error("Stock adjustment failed: " . $e->getMessage());
                }
            }
        }
    }

    private function restoreProductQuantities(Order $order): void
    {
        foreach ($order->items as $item) {

            if ($item->product_id && $item->quantity > 0) {
                try {
                    WarehouseProductSyncService::adjustQuantity(
                        $item->product_id,
                        'addition',
                        $item->quantity
                    );
                } catch (\Exception $e) {
                    Log::error("Stock restore failed: " . $e->getMessage());
                }
            }
        }
    }

    private function deleteOrderRelations(Order $order): void
    {
        $order->items()->delete();
        $order->statusHistories()->delete();
        $order->payments()->delete();
    }
}
