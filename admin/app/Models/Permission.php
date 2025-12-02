<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    
    public$timestamps = false;
    protected $table = 'permissions';

    protected $fillable = [
        'role_id',
        'slug',
        'action',
        'route'
    ];
}
