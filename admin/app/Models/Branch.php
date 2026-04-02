<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{

    protected $fillable = [
        'customer_id',
        'is_default',
        'is_default_delivery',
        'is_default_billing',
        'name',
        'contact_name',
        'country',
        'address_line1',
        'address_line2',
        'city',
        'zip_code'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_default_delivery' => 'boolean',
        'is_default_billing' => 'boolean',
    ];
    

    /**
     * Get the customer that owns the address.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the full address as a single string.
     */
    public function getFullAddressAttribute(): string
    {
        $address = $this->address_line1;
        
        if ($this->address_line2) {
            $address .= ', ' . $this->address_line2;
        }
        
        if ($this->landmark) {
            $address .= ', ' . $this->landmark;
        }
        
        $address .= ', ' . $this->city . ', ' . $this->state . ' ' . $this->zip_code;
        $address .= ', ' . $this->country;
        
        return $address;
    }
}
