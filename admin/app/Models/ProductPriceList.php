<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceList extends Model
{
    protected $table = 'product_price_list';

    protected $fillable = [
        'product_id',
        'price_list_id',
        'unit_price',
        'rrp',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'rrp' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }
}
