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
        'vat',
        'minimum_amount',
        'maximum_amount',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'vat' => 'decimal:2',
            'minimum_amount' => 'decimal:2',
            'maximum_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Pick the active delivery method for a basket subtotal (excl. VAT & shipping).
     * A tier applies when subtotal >= minimum_amount and (maximum_amount is null or subtotal <= maximum_amount).
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

        $eligible = $methods->filter(function (self $m) use ($subtotal) {
            if ((float) ($m->minimum_amount ?? 0) > $subtotal) {
                return false;
            }
            if ($m->maximum_amount === null) {
                return true;
            }

            return $subtotal <= (float) $m->maximum_amount;
        });

        if ($eligible->isEmpty()) {
            return null;
        }

        return static::sortEligibleByBestTier($eligible)->first();
    }

    /**
     * Active tiers are selectable at checkout (same rule as resolveForSubtotal query).
     */
    public function isActive(): bool
    {
        return strtolower(trim((string) ($this->status ?? ''))) === 'active';
    }

    /**
     * Whether this method’s min/max band includes the given subtotal (excl. VAT & shipping).
     */
    public function matchesSubtotal(float $subtotal): bool
    {
        if ((float) ($this->minimum_amount ?? 0) > $subtotal) {
            return false;
        }
        if ($this->maximum_amount === null) {
            return true;
        }

        return $subtotal <= (float) $this->maximum_amount;
    }

    /**
     * DNA callback / legacy: use frozen client id when it still matches cart subtotal; else server default tier.
     */
    public static function resolveForCheckout(float $subtotal, ?int $preferredId): ?self
    {
        if ($preferredId !== null && $preferredId > 0) {
            $preferred = static::query()->where('id', $preferredId)->first();
            if ($preferred instanceof self && $preferred->isActive() && $preferred->matchesSubtotal($subtotal)) {
                return $preferred;
            }
        }

        return static::resolveForSubtotal($subtotal);
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
