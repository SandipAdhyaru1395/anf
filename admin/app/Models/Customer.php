<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Concerns\RecordsSyncUpdate;

class Customer extends Model
{
    use HasFactory, SoftDeletes, HasApiTokens;
    use RecordsSyncUpdate;

    protected $table = 'customers';

    protected $fillable = [
        'email',
        'phone',
        'password',
        'approved_at',
        'approved_by',
        'credit_balance',
        'email_verified_at',
        'is_active',
        'remember_token',
        'last_login',
        'company_name',
        'company_country',
        'company_address_line1',
        'company_address_line2',
        'company_city',
        'company_zip_code',
        'rep_id',
        'customer_group_id',
        'price_list_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'credit_balance' => 'decimal:2',
        'password' => 'hashed',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all addresses for the customer.
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'rep_id', 'id');
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}


