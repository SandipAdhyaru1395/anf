<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DeliveryMethod extends Model
{
    protected $table = 'delivery_methods';

    protected $fillable = [
        'name',
        'time',
        'rate',
        'minimum_amount',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'minimum_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Pick the active delivery method for a basket subtotal (excl. VAT & shipping).
     * A tier applies only when subtotal has reached its minimum (subtotal >= minimum_amount).
     * Among all qualifying tiers, the one with the highest minimum_amount wins (e.g. 100 vs mins 10,50,90,200 → 90).
     * If subtotal is below every tier’s minimum, returns null (no delivery charge).
     */
    public static function resolveForSubtotal(float $subtotal): ?self
    {
        /** @var Collection<int, self> $methods */
        $methods = static::query()
            ->whereRaw('LOWER(TRIM(COALESCE(status, \'\'))) = ?', ['active'])
            ->get();

        if ($methods->isEmpty()) {
            return null;
        }

        $eligible = $methods->filter(fn (self $m) => (float) ($m->minimum_amount ?? 0) <= $subtotal);

        if ($eligible->isEmpty()) {
            return null;
        }

        return static::sortEligibleByBestTier($eligible)->first();
    }

    /**
     * @param Collection<int, self> $eligible
     * @return Collection<int, self>
     */
    private static function sortEligibleByBestTier(Collection $eligible): Collection
    {
        return $eligible->sort(function (self $a, self $b) {
            $minA = (float) ($a->minimum_amount ?? 0);
            $minB = (float) ($b->minimum_amount ?? 0);
            $cmp = $minB <=> $minA;
            if ($cmp !== 0) {
                return $cmp;
            }
            $orderA = (int) ($a->sort_order ?? 9999);
            $orderB = (int) ($b->sort_order ?? 9999);
            $cmp = $orderA <=> $orderB;
            if ($cmp !== 0) {
                return $cmp;
            }

            return $b->id <=> $a->id;
        })->values();
    }
}
