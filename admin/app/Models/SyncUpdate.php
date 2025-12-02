<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncUpdate extends Model
{
    protected $table = 'sync_updates';

    protected $fillable = [
        'entity',
        'version',
    ];
}


