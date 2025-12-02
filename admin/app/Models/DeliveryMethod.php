<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryMethod extends Model
{
    protected $table = 'delivery_methods';

    protected $fillable = [
        'name',
        'time',
        'rate',
        'status',
        'sort_order'
    ];

}
