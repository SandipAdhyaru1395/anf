<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuantityAdjustment extends Model
{
    protected $table = 'quantity_adjustments';

    protected $fillable = [
        'date',
        'reference_no',
        'document',
        'note',
        'user_id',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(QuantityAdjustmentItem::class, 'quantity_adjustment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
