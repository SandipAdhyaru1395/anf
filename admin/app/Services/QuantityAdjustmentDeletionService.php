<?php

namespace App\Services;

use App\Models\QuantityAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\WarehouseProductSyncService;

class QuantityAdjustmentDeletionService
{
    public function delete(QuantityAdjustment $adjustment): void
    {
        DB::transaction(function () use ($adjustment) {

            // Load items if not loaded
            if (!$adjustment->relationLoaded('items')) {
                $adjustment->load('items');
            }

            // Prepare adjustments for reversal
            $adjustments = $adjustment->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'type'       => $item->type,
                    'quantity'   => $item->quantity,
                ];
            })->toArray();

            // Revert stock
            WarehouseProductSyncService::revertAdjustments($adjustments);

            // Delete document
            if ($adjustment->document) {
                Storage::disk('public')->delete($adjustment->document);
            }

            // Delete related items first (safer)
            $adjustment->items()->delete();

            // Delete adjustment
            $adjustment->delete();
        });
    }
}
