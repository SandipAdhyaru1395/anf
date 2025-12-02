<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
      protected $table = 'sub_categories';

    protected$fillable = [
        'category_id',
        'name',
        'description',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function setDescriptionAttribute($value)
    {
        // Strip HTML tags and check if content is empty
        $clean = trim(strip_tags($value));
        // If content is empty or just <p><br></p>, store null
        $this->attributes['description'] = ($clean === '') ? null : $value;
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
