<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVolumeDiscount extends Model
{
    protected $fillable = [
        'product_id',
        'price_list_id',
        'volume_discount_group_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function group()
    {
        return $this->belongsTo(VolumeDiscountGroup::class, 'volume_discount_group_id');
    }
}

