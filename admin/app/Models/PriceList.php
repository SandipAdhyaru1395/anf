<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    protected $fillable = [
        'name',
        'conversion_rate',
        'price_list_type',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'price_list_id');
    }

}
