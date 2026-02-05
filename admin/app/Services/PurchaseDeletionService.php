<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\WarehousesProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\WarehouseProductSyncService;

class PurchaseDeletionService
{
    public function delete(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {

            // Ensure items loaded
            if (!$purchase->relationLoaded('items')) {
                $purchase->load('items');
            }

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ Prepare stock adjustments (reverse stock addition)
            |--------------------------------------------------------------------------
            */
            $adjustments = $purchase->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'type'       => 'addition', // purchase added stock originally
                    'quantity'   => $item->quantity,
                ];
            })->toArray();


            /*
            |--------------------------------------------------------------------------
            | 2️⃣ Prepare cost reversion data
            |--------------------------------------------------------------------------
            */
            $purchaseItemsForCost = $purchase->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity'   => (float) $item->quantity,
                    'unit_cost'  => (float) $item->unit_cost,
                ];
            })->toArray();


            /*
            |--------------------------------------------------------------------------
            | 3️⃣ Get current warehouse state BEFORE reverting
            |--------------------------------------------------------------------------
            */
            $warehouseProducts = WarehousesProduct::whereIn(
                'product_id',
                array_column($purchaseItemsForCost, 'product_id')
            )->get()->keyBy('product_id');


            $itemsWithCurrentState = [];

            foreach ($purchaseItemsForCost as $item) {
                $wp = $warehouseProducts->get($item['product_id']);

                $itemsWithCurrentState[] = [
                    'product_id'   => $item['product_id'],
                    'quantity'     => $item['quantity'],
                    'unit_cost'    => $item['unit_cost'],
                    'old_quantity' => $wp ? (float) $wp->quantity : 0,
                    'old_avg_cost' => $wp ? (float) $wp->avg_cost : 0,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | 4️⃣ Revert average cost FIRST
            |--------------------------------------------------------------------------
            */
            WarehouseProductSyncService::revertAverageCostUpdates($itemsWithCurrentState);


            /*
            |--------------------------------------------------------------------------
            | 5️⃣ Revert stock changes
            |--------------------------------------------------------------------------
            */
            WarehouseProductSyncService::revertAdjustments($adjustments);


            /*
            |--------------------------------------------------------------------------
            | 6️⃣ Delete related records
            |--------------------------------------------------------------------------
            */

            // Delete items first (safe)
            $purchase->items()->delete();

            // Delete document if exists
            if ($purchase->document) {
                Storage::disk('public')->delete($purchase->document);
            }

            // Delete purchase
            $purchase->delete();
        });
    }
}
