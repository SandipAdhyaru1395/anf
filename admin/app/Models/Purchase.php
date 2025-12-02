<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'purchases';

    protected $fillable = [
        'date',
        'reference_no',
        'supplier_id',
        'deliver',
        'shipping_charge',
        'vat',
        'sub_total',
        'document',
        'note',
        'user_id',
        'total_amount',
    ];

    protected $casts = [
        'date' => 'datetime',
        'total_amount' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'vat' => 'decimal:2',
        'sub_total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}

