<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuantityAdjustmentItem extends Model
{
    protected $table = 'quantity_adjustment_items';

    protected $fillable = [
        'quantity_adjustment_id',
        'product_id',
        'type',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function adjustment()
    {
        return $this->belongsTo(QuantityAdjustment::class, 'quantity_adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
