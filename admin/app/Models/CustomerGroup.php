<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{

    protected $table = 'customer_groups';

    public $fillable = [
        'name',
        'restrict_categories'
    ];
    
    public function categories()
    {
        return $this->morphedByMany(
            Category::class,
            'customer_groupable'
        );
    }

    public function brands()
    {
        return $this->morphedByMany(
            Brand::class,
            'customer_groupable'
        );
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_group_id');
    }
}
