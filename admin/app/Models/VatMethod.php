<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VatMethod extends Model
{
    protected $table = 'vat_methods';

    protected $fillable = [
        'name',
        'type', // Percentage or Fixed
        'amount',
        'status',
    ];
}


