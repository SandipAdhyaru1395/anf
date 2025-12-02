<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\RecordsSyncUpdate;

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
}
