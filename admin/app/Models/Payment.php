<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'date',
        'reference_no',
        'amount',
        'payment_method',
        'card_brand',
        'card_last4',
        'card_country',
        'card_expiry',
        'dna_token_id',
        'dna_transaction_id',
        'dna_rrn',
        'dna_scheme_reference',
        'note',
        'user_id',
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
