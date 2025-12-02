<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehousesProduct extends Model
{
    protected $table = 'warehouses_products';

    protected $fillable = [
        'product_id',
        'quantity',
        'avg_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'avg_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
