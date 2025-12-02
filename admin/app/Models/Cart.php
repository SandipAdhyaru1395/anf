<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'customer_id',
        'subtotal',
        'total_discount',
        'total',
        'units',
        'skus',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}


