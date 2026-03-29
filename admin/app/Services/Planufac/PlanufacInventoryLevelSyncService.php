<?php

namespace App\Services\Planufac;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

/**
 * Applies Planufac webhook inventory_levels to local products (match by SKU).
 *
 * Planufac "available" is treated as sellable quantity. We set {@see Product::$available_qty}
 * to that value and adjust {@see Product::$onhand_qty} so
 * available ≈ onhand − ordered remains consistent: onhand = available + ordered_qty.
 */
class PlanufacInventoryLevelSyncService
{
    /**
     * @param  array<int, mixed>  $inventoryLevels
     * @return array{updated: int, skipped_invalid: int, sku_not_found: int, distinct_skus: int}
     */
    public function applyLevels(array $inventoryLevels): array
    {
        $skippedInvalid = 0;
        /** @var array<string, int> $skuToAvailable last wins on duplicate SKU */
        $skuToAvailable = [];

        foreach ($inventoryLevels as $row) {
            if (! is_array($row)) {
                $skippedInvalid++;

                continue;
            }
            $sku = trim((string) ($row['sku'] ?? ''));
            if ($sku === '' || str_contains($sku, '{{')) {
                $skippedInvalid++;

                continue;
            }
            $available = $row['available'] ?? 0;
            if ($available === null || $available === '') {
                $available = 0;
            }
            $available = max(0, (int) $available);
            $skuToAvailable[$sku] = $available;
        }

        $distinctSkus = count($skuToAvailable);
        if ($distinctSkus === 0) {
            return [
                'updated' => 0,
                'skipped_invalid' => $skippedInvalid,
                'sku_not_found' => 0,
                'distinct_skus' => 0,
            ];
        }

        $products = Product::query()
            ->whereIn('sku', array_keys($skuToAvailable))
            ->get(['id', 'sku', 'ordered_qty', 'onhand_qty', 'available_qty']);

        $matchedSkus = [];
        $updated = 0;

        foreach ($products as $product) {
            $matchedSkus[$product->sku] = true;
            $available = $skuToAvailable[$product->sku] ?? null;
            if ($available === null) {
                continue;
            }
            $ordered = (float) ($product->ordered_qty ?? 0);
            $newOnHand = $available + $ordered;

            $sameAvailable = (int) $product->available_qty === $available;
            $sameOnHand = abs((float) $product->onhand_qty - $newOnHand) < 0.00001;
            if ($sameAvailable && $sameOnHand) {
                continue;
            }

            Product::query()->whereKey($product->id)->update([
                'onhand_qty' => $newOnHand,
                'available_qty' => $available,
                'updated_at' => now(),
            ]);
            $updated++;
        }

        $skuNotFound = 0;
        foreach (array_keys($skuToAvailable) as $sku) {
            if (! isset($matchedSkus[$sku])) {
                $skuNotFound++;
            }
        }

        return [
            'updated' => $updated,
            'skipped_invalid' => $skippedInvalid,
            'sku_not_found' => $skuNotFound,
            'distinct_skus' => $distinctSkus,
        ];
    }

    /**
     * @param  array<int, mixed>  $inventoryLevels
     */
    public function applyLevelsWithLogging(array $inventoryLevels, ?string $requestId = null): void
    {
        $stats = $this->applyLevels($inventoryLevels);
        Log::info('Planufac inventory_levels sync finished', array_merge($stats, [
            'request_id' => $requestId,
            'rows_in_payload' => count($inventoryLevels),
        ]));
    }
}
