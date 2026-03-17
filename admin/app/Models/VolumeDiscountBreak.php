<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VolumeDiscountBreak extends Model
{
    protected $fillable = [
        'volume_discount_group_id',
        'from_quantity',
        'discount_percentage',
    ];

    protected $casts = [
        'from_quantity' => 'integer',
        'discount_percentage' => 'decimal:2',
    ];

    public function group()
    {
        return $this->belongsTo(VolumeDiscountGroup::class, 'volume_discount_group_id');
    }
}

