<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'shipping_branch_id',
        'billing_branch_id',
        'delivery_method_id',
        'delivery_method_name',
        'delivery_time',
        'delivery_charge',
        'delivery_note',
        'customer_po_number',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function shippingBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'shipping_branch_id');
    }

    public function billingBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'billing_branch_id');
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

    // Compatibility accessors for legacy order address fields.
    public function getBranchNameAttribute(): ?string
    {
        return $this->shippingBranch?->name;
    }

    public function getCountryAttribute(): ?string
    {
        return $this->shippingBranch?->country;
    }

    public function getAddressLine1Attribute(): ?string
    {
        return $this->shippingBranch?->address_line1;
    }

    public function getAddressLine2Attribute(): ?string
    {
        return $this->shippingBranch?->address_line2;
    }

    public function getCityAttribute(): ?string
    {
        return $this->shippingBranch?->city;
    }

    public function getZipCodeAttribute(): ?string
    {
        return $this->shippingBranch?->zip_code;
    }
}
