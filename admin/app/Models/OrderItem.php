<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{

    protected $fillable = [
        'order_id',
        'type',
        'product_id',
        'product_unit',
        'quantity',
        'unit_price',
        'unit_vat',
        'unit_wallet_credit',
        'wallet_credit_earned',
        'total_price',
        'total_vat',
        'total',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
