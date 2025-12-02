<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandTag extends Model
{
    protected $table = 'brand_tag';

    protected $fillable = [
        'brand_id',
        'tag_id',
    ];
}
