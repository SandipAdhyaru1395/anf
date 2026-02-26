<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currencies';

    protected $fillable = [
        'currency_code',
        'currency_name',
        'symbol',
        'exchange_rate',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
    ];
}
