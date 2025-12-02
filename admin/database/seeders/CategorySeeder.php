<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::truncate();


        ////////////////    Main Categories ///////////////////

        $categories = array(
            [
                'name' => 'Deals & Offers',
                'sort_order' => 1,
                'is_active' => 1,
                'is_special' => 1
            ],
            [
                'name' => 'Vaping',
                'sort_order' => 2,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Heated Tobacco',
                'sort_order' => 3,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Confectionery',
                'sort_order' => 4,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Nicotine Pouches',
                'sort_order' => 5,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Nicotine Strips',
                'sort_order' => 6,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Smoking Accessories',
                'sort_order' => 7,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Combustibles',
                'sort_order' => 8,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Incense',
                'sort_order' => 9,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Groceries',
                'sort_order' => 10,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Snacks',
                'sort_order' => 11,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Nutrition',
                'sort_order' => 12,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Drinks',
                'sort_order' => 13,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Health & Beauty',
                'sort_order' => 14,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Mobile Phone Accessories',
                'sort_order' => 15,
                'is_active' => 1,
                'is_special' => 0
            ],
             [
                'name' => 'Batteries',
                'sort_order' => 16,
                'is_active' => 1,
                'is_special' => 0
            ],
             [
                'name' => 'Retailer Essentials',
                'sort_order' => 17,
                'is_active' => 1,
                'is_special' => 0
            ],
             [
                'name' => 'Age Verification Solutions',
                'sort_order' => 18,
                'is_active' => 1,
                'is_special' => 0
            ],
            
           
        );

        Category::insert($categories);
        
        $categories = [

             //////////////////////  Child Categories /////////////////////////////

            [
                'name' => 'Big puff devices',
                'parent_id' => 2,
                'sort_order' => 1,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'New Compliant 600 Puffs',
                'parent_id' => 2,
                'sort_order' => 2,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'E-liquids',
                'parent_id' => 2,
                'sort_order' => 3,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Pre-filled POD Systems',
                'parent_id' => 2,
                'sort_order' => 4,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Open POD Systems',
                'parent_id' => 2,
                'sort_order' => 5,
                'is_active' => 1,
                'is_special' => 0
            ],
             [
                'name' => 'Cigarillos',
                'parent_id' => 8,
                'sort_order' => 1,
                'is_active' => 1,
                'is_special' => 0
            ],
            [
                'name' => 'Cigars',
                'parent_id' => 8,
                'sort_order' => 2,
                'is_active' => 1,
                'is_special' => 0
            ],
           
        ];

        Category::insert($categories);
    }
}
