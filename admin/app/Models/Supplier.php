<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\RecordsSyncUpdate;

class Supplier extends Model
{
    use SoftDeletes;
    use RecordsSyncUpdate;
    
    protected $table = 'suppliers';

    protected $fillable = [
        'company',
        'full_name',
        'vat_number',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'is_active',
    ];
}

