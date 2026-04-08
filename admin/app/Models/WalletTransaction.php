<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $table = 'wallet_transactions';

    protected $fillable = [
        'customer_id',
        'order_id',
        'amount',
        'type',
        'description',
        'balance_after',
    ];

    protected static function booted(): void
    {
        static::creating(function (WalletTransaction $walletTransaction) {
            return (float) ($walletTransaction->amount ?? 0) !== 0.0;
        });
    }
}


