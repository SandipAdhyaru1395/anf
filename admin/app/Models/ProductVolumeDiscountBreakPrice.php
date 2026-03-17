<?php

namespace App\Models;

use App\Models\Concerns\RecordsSyncUpdate;
use Illuminate\Database\Eloquent\Model;

class ProductVolumeDiscountBreakPrice extends Model
{
    
    protected $fillable = [
        'product_id',
        'price_list_id',
        'volume_discount_break_id',
        'override_price',
    ];

    protected $casts = [
        'override_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function break()
    {
        return $this->belongsTo(VolumeDiscountBreak::class, 'volume_discount_break_id');
    }
}

