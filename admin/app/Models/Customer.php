<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Concerns\RecordsSyncUpdate;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerPasswordResetMail;

class Customer extends Model implements CanResetPasswordContract
{
    use HasFactory, SoftDeletes, HasApiTokens;
    use RecordsSyncUpdate;
    use CanResetPassword;

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
        'is_approved',
        'remember_token',
        'last_login',
        'company_name',
        'company_country',
        'company_address_line1',
        'company_address_line2',
        'company_city',
        'company_zip_code',
        'vat_number',
        'eori_number',
        'is_part_of_group',
        'business_type',
        'average_monthly_spend_ex_vat',
        'stores_serviced_count',
        'contact_person_name',
        'rep_id',
        'customer_group_id',
        'price_list_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'is_approved' => 'integer',
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

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        try {
            Mail::to($this->getEmailForPasswordReset())->queue(new CustomerPasswordResetMail($this, $token));
        } catch (\Throwable $e) {
            Log::warning('Failed to queue customer password reset email', [
                'customer_id' => $this->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}


