<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRef extends Model
{
    protected $table = 'order_ref';

    protected $fillable = [
        'qa',
        'po',
        'so',
        'est',
        'pay',
        'cn',
    ];
   
}
