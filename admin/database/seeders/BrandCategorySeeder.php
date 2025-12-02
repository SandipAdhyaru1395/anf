<?php

namespace Database\Seeders;

use App\Models\BrandCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BrandCategory::truncate();
        
        $brandCategories=[
            [
                'category_id' => 1,
                'brand_id' => 5
            ],
             [
                'category_id' => 1,
                'brand_id' => 20    
            ],
              [
                'category_id' => 19,
                'brand_id' => 1    
            ],
              [
                'category_id' => 19,
                'brand_id' => 2   
            ],
             [
                'category_id' => 19,
                'brand_id' => 3   
            ],
            [
                'category_id' => 19,
                'brand_id' => 4
            ],
             [
                'category_id' => 19,
                'brand_id' => 5
            ],
               [
                'category_id' => 16,
                'brand_id' => 6
            ],
             [
                'category_id' => 16,
                'brand_id' => 7
            ],
              [
                'category_id' => 16,
                'brand_id' => 8
            ],
              [
                'category_id' => 16,
                'brand_id' => 9
            ],
              [
                'category_id' => 17,
                'brand_id' => 10
            ],
               [
                'category_id' => 15,
                'brand_id' => 11
            ],
               [
                'category_id' => 15,
                'brand_id' => 12
            ],

              [
                'category_id' => 14,
                'brand_id' => 13
            ],
               [
                'category_id' => 14,
                'brand_id' => 14
            ],
              [
                'category_id' => 12,
                'brand_id' => 15
            ],
                [
                'category_id' => 24,
                'brand_id' => 21
            ],
                [
                'category_id' => 21,
                'brand_id' => 16
            ],
                 [
                'category_id' => 21,
                'brand_id' => 17
            ],
                 [
                'category_id' => 21,
                'brand_id' => 18
            ],
                 [
                'category_id' => 21,
                'brand_id' => 19
            ],
        ];

        BrandCategory::insert($brandCategories);
    }
}
