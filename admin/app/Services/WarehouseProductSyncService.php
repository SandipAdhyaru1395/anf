<?php

namespace App\Services;

use App\Models\Product;
use App\Models\WarehousesProduct;

class WarehouseProductSyncService
{
    /**
     * Sync warehouse product quantity and cost with product
     *
     * @param int $productId
     * @param float|null $quantity Optional quantity. If null, uses product's stock_quantity
     * @param float|null $avgCost Optional avg_cost. If null, uses product's cost_price
     * @return WarehousesProduct
     */
    public static function sync(int $productId, ?float $quantity = null, ?float $avgCost = null): WarehousesProduct
    {
        $product = Product::find($productId);
        
        if (!$product) {
            throw new \Exception("Product with ID {$productId} not found");
        }

        $quantity = $quantity ?? $product->stock_quantity;
        $avgCost = $avgCost ?? $product->cost_price ?? 0;

        return WarehousesProduct::updateOrCreate(
            ['product_id' => $productId],
            [
                'quantity' => max(0, $quantity), // Ensure quantity doesn't go below 0
                'avg_cost' => $avgCost
            ]
        );
    }


    /**
     * Adjust warehouse product quantity based on adjustment type
     * Only updates quantity, preserves existing avg_cost
     *
     * @param int $productId
     * @param string $type 'addition' or 'subtraction'
     * @param float $quantity
     * @return WarehousesProduct
     */
    public static function adjustQuantity(int $productId, string $type, float $quantity): WarehousesProduct
    {
        $product = Product::find($productId);
        
        if (!$product) {
            throw new \Exception("Product with ID {$productId} not found");
        }

        // Get or create warehouse product to preserve existing avg_cost
        $warehouseProduct = WarehousesProduct::firstOrCreate(
            ['product_id' => $productId],
            [
                'quantity' => 0,
                'avg_cost' => $product->cost_price ?? 0
            ]
        );

        // Preserve existing avg_cost
        $existingAvgCost = (float) $warehouseProduct->avg_cost;

        // Update product stock quantity
        if ($type === 'addition') {
            $product->increment('stock_quantity', $quantity);
        } else {
            $product->decrement('stock_quantity', $quantity);
        }

        // Refresh to get updated value
        $product->refresh();

        // Update warehouse product quantity only, preserve avg_cost
        $warehouseProduct->update([
            'quantity' => max(0, (float) $product->stock_quantity),
            'avg_cost' => $existingAvgCost // Preserve existing avg_cost
        ]);

        return $warehouseProduct;
    }

    /**
     * Revert warehouse product quantity adjustment
     * Only updates quantity, preserves existing avg_cost
     *
     * @param int $productId
     * @param string $type 'addition' or 'subtraction'
     * @param float $quantity
     * @return WarehousesProduct
     */
    public static function revertQuantity(int $productId, string $type, float $quantity): WarehousesProduct
    {
        $product = Product::find($productId);
        
        if (!$product) {
            throw new \Exception("Product with ID {$productId} not found");
        }

        // Get warehouse product to preserve existing avg_cost
        $warehouseProduct = WarehousesProduct::where('product_id', $productId)->first();
        
        if (!$warehouseProduct) {
            // If warehouse product doesn't exist, create it
            $warehouseProduct = WarehousesProduct::create([
                'product_id' => $productId,
                'quantity' => max(0, (float) $product->stock_quantity),
                'avg_cost' => $product->cost_price ?? 0
            ]);
        }

        // Preserve existing avg_cost
        $existingAvgCost = (float) $warehouseProduct->avg_cost;

        // Revert product stock quantity (opposite of adjust)
        if ($type === 'addition') {
            $product->decrement('stock_quantity', $quantity);
        } else {
            $product->increment('stock_quantity', $quantity);
        }

        // Refresh to get updated value
        $product->refresh();

        // Update warehouse product quantity only, preserve avg_cost
        $warehouseProduct->update([
            'quantity' => max(0, (float) $product->stock_quantity),
            'avg_cost' => $existingAvgCost // Preserve existing avg_cost
        ]);

        return $warehouseProduct;
    }

    /**
     * Process quantity adjustments in batch
     * Note: Should be called within a transaction context
     *
     * @param array $adjustments Array of ['product_id' => int, 'type' => string, 'quantity' => float]
     * @return void
     */
    public static function processAdjustments(array $adjustments): void
    {
        foreach ($adjustments as $adjustment) {
            self::adjustQuantity(
                $adjustment['product_id'],
                $adjustment['type'],
                $adjustment['quantity']
            );
        }
    }

    /**
     * Revert quantity adjustments in batch
     * Note: Should be called within a transaction context
     *
     * @param array $adjustments Array of ['product_id' => int, 'type' => string, 'quantity' => float]
     * @return void
     */
    public static function revertAdjustments(array $adjustments): void
    {
        foreach ($adjustments as $adjustment) {
            self::revertQuantity(
                $adjustment['product_id'],
                $adjustment['type'],
                $adjustment['quantity']
            );
        }
    }

    /**
     * Update average cost when a purchase is made
     * Calculates weighted average: (old_quantity * old_avg_cost + new_quantity * new_unit_cost) / (old_quantity + new_quantity)
     * Only updates avg_cost in warehouses_products table, does not touch products.cost_price
     * Note: This should be called AFTER processAdjustments, but we need to preserve old avg_cost before sync overwrites it
     *
     * @param int $productId
     * @param float $newQuantity Quantity that was added
     * @param float $newUnitCost Unit cost of the new purchase
     * @param float $oldQuantity Old quantity before addition (must be provided)
     * @param float $oldAvgCost Old avg_cost before addition (must be provided)
     * @return WarehousesProduct
     */
    public static function updateAverageCost(int $productId, float $newQuantity, float $newUnitCost, float $oldQuantity, float $oldAvgCost): WarehousesProduct
    {
        $warehouseProduct = WarehousesProduct::where('product_id', $productId)->first();
        
        if (!$warehouseProduct) {
            // If warehouse product doesn't exist, create it with the new values
            return WarehousesProduct::create([
                'product_id' => $productId,
                'quantity' => $oldQuantity + $newQuantity,
                'avg_cost' => $newUnitCost
            ]);
        }

        // Calculate weighted average cost
        // If there's no existing quantity, use the new unit cost
        if ($oldQuantity <= 0) {
            $newAvgCost = $newUnitCost;
        } else {
            // Weighted average: (old_total_value + new_total_value) / (old_quantity + new_quantity)
            $oldTotalValue = $oldQuantity * $oldAvgCost;
            $newTotalValue = $newQuantity * $newUnitCost;
            $totalQuantity = $oldQuantity + $newQuantity;
            $newAvgCost = $totalQuantity > 0 ? ($oldTotalValue + $newTotalValue) / $totalQuantity : $newUnitCost;
        }

        // Update only the avg_cost, quantity is already updated by processAdjustments
        $warehouseProduct->update([
            'avg_cost' => round($newAvgCost, 2)
        ]);

        return $warehouseProduct;
    }

    /**
     * Revert average cost when a purchase is removed/edited
     * Recalculates what the avg_cost was before this purchase was applied
     *
     * @param int $productId
     * @param float $removedQuantity Quantity being removed
     * @param float $removedUnitCost Unit cost of the purchase being removed
     * @return WarehousesProduct
     */
    public static function revertAverageCost(int $productId, float $removedQuantity, float $removedUnitCost): WarehousesProduct
    {
        $warehouseProduct = WarehousesProduct::where('product_id', $productId)->first();
        
        if (!$warehouseProduct) {
            // If warehouse product doesn't exist, nothing to revert
            return WarehousesProduct::firstOrCreate(
                ['product_id' => $productId],
                ['quantity' => 0, 'avg_cost' => 0]
            );
        }

        $currentQuantity = (float) $warehouseProduct->quantity;
        $currentAvgCost = (float) $warehouseProduct->avg_cost;

        // Calculate what the avg_cost was before this purchase
        // If removing all quantity, set to 0
        if ($currentQuantity <= $removedQuantity) {
            $revertedAvgCost = 0;
        } else {
            // Reverse the weighted average calculation
            // current_total_value = old_total_value + removed_total_value
            // old_total_value = current_total_value - removed_total_value
            // old_avg_cost = old_total_value / old_quantity
            $currentTotalValue = $currentQuantity * $currentAvgCost;
            $removedTotalValue = $removedQuantity * $removedUnitCost;
            $oldTotalValue = $currentTotalValue - $removedTotalValue;
            $oldQuantity = $currentQuantity - $removedQuantity;
            $revertedAvgCost = $oldQuantity > 0 ? $oldTotalValue / $oldQuantity : 0;
        }

        // Update only the avg_cost, quantity is handled separately by revertAdjustments
        $warehouseProduct->update([
            'avg_cost' => round($revertedAvgCost, 2)
        ]);

        return $warehouseProduct;
    }

    /**
     * Process average cost updates for purchase items in batch
     * Note: Should be called within a transaction context, after processAdjustments
     * Old values should be captured BEFORE processAdjustments is called
     *
     * @param array $purchaseItems Array of ['product_id' => int, 'quantity' => float, 'unit_cost' => float, 'old_quantity' => float, 'old_avg_cost' => float]
     * @return void
     */
    public static function processAverageCostUpdates(array $purchaseItems): void
    {
        foreach ($purchaseItems as $item) {
            self::updateAverageCost(
                $item['product_id'],
                $item['quantity'],
                $item['unit_cost'],
                $item['old_quantity'] ?? 0,
                $item['old_avg_cost'] ?? 0
            );
        }
    }

    /**
     * Revert average cost updates for purchase items in batch
     * Note: Should be called within a transaction context, before revertAdjustments
     *
     * @param array $purchaseItems Array of ['product_id' => int, 'quantity' => float, 'unit_cost' => float]
     * @return void
     */
    public static function revertAverageCostUpdates(array $purchaseItems): void
    {
        foreach ($purchaseItems as $item) {
            self::revertAverageCost(
                $item['product_id'],
                $item['quantity'],
                $item['unit_cost']
            );
        }
    }
}

