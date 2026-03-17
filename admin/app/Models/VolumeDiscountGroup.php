<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VolumeDiscountBreak;
use App\Models\ProductVolumeDiscount;

class VolumeDiscountGroup extends Model
{
    protected $fillable = [
        'name',
    ];

    public function breaks()
    {
        return $this->hasMany(VolumeDiscountBreak::class)->orderBy('from_quantity');
    }

    public function productVolumeDiscounts()
    {
        return $this->hasMany(ProductVolumeDiscount::class);
    }
}

