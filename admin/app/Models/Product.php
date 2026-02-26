<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\RecordsSyncUpdate;
use App\Models\PriceList;
use App\Models\ProductPriceList;
use App\Models\Customer;

class Product extends Model
{
    use SoftDeletes;
    use RecordsSyncUpdate;
    protected $table = 'products';

    protected $fillable = [
        'name',
        'sku',
        'product_unit_sku',
        'description',
        'price',
        'cost_price',
        'image_url',
        'wallet_credit',
        'weight',
        'rrp',
        'expiry_date',
        'stock_quantity',
        'step_quantity',
        'notification_request_count',
        'vat_percentage',
        'vat_method_id',
        'vat_amount',
        'vat_method_name',
        'vat_method_type',
        'unit_id',
        'is_active',
    ];

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'product_brand', 'product_id', 'brand_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function productPriceLists()
    {
        return $this->hasMany(ProductPriceList::class);
    }

    public function priceLists()
    {
        return $this->belongsToMany(PriceList::class, 'product_price_list')
            ->withPivot('unit_price', 'rrp')
            ->withTimestamps();
    }

    /**
     * Get effective unit price for a given customer using the shared pricing rules:
     * 1. If ProductPriceList has an explicit unit_price for the customer's price_list_id, use it.
     * 2. Else if the PriceList has a conversion_rate, apply it to the product's base price.
     * 3. Else fall back to the product's default price.
     */
    public function getPrice(?Customer $customer): float
    {
        $basePrice = is_numeric($this->price) ? (float) $this->price : 0.0;
        if (!$customer || !$customer->price_list_id) {
            return $basePrice;
        }

        $priceListId = (int) $customer->price_list_id;

        // 1) Explicit override in product_price_list
        $productPriceList = ProductPriceList::where('product_id', $this->id)
            ->where('price_list_id', $priceListId)
            ->first();

        if ($productPriceList && $productPriceList->unit_price !== null) {
            return (float) $productPriceList->unit_price;
        }

        // 2) Price list conversion rate
        $priceList = PriceList::find($priceListId);
        if ($priceList && $priceList->conversion_rate !== null) {
            $conversion = (float) $priceList->conversion_rate;
            return $basePrice * ($conversion / 100);
        }

        // 3) Default price
        return $basePrice;
    }

    /**
     * Get RRP value for a given price list:
     * 1. If product_price_list has an explicit rrp for that price list, use it.
     * 2. Otherwise fall back to the product's own rrp field.
     */
    public function getRrpForPriceList(?PriceList $priceList): ?float
    {
        if (!$priceList) {
            return null;
        }

        $pivot = ProductPriceList::where('product_id', $this->id)
            ->where('price_list_id', $priceList->id)
            ->first();

        if ($pivot && $pivot->rrp !== null) {
            return (float) $pivot->rrp;
        }

        if ($this->rrp === null || $this->rrp === '') {
            return null;
        }

        return is_numeric($this->rrp) ? (float) $this->rrp : null;
    }
}
