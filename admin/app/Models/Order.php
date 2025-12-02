<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\RecordsSyncUpdate;

class Order extends Model
{
    use RecordsSyncUpdate;
    
    protected $fillable = [
        'order_number',
        'type',
        'order_date',
        'customer_id',
        'parent_order_id',
        'subtotal',
        'vat_amount',
        'total_amount',
        'payment_amount',
        'wallet_credit_used',
        'paid_amount',
        'unpaid_amount',
        'units_count',
        'skus_count',
        'items_count',
        'payment_terms',
        'payment_status',
        'outstanding_amount',
        'estimated_delivery_date',
        'status',
        'branch_name',
        'country',
        'address_line1',
        'address_line2',
        'city',
        'zip_code',
        'delivery_method_id',
        'delivery_method_name',
        'delivery_time',
        'delivery_charge',
        'delivery_note',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id')->orderBy('created_at');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id')->orderBy('date', 'desc');
    }

    /**
     * Get the parent order (for credit notes, this is the original SO order)
     */
    public function parentOrder()
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    /**
     * Get credit notes for this order (if this is an SO order)
     */
    public function creditNotes()
    {
        return $this->hasMany(Order::class, 'parent_order_id')->where('type', 'CN');
    }
}
