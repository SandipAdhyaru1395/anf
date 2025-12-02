<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Brand::truncate();

        $brands = [
            [
                'name' => 'Hyola Ultra 30K',
                'is_active' => 1
            ],
            [
                'name' => 'PIXL 8000',
                'is_active' => 1
            ],
            [
                'name' => 'PIXL Duo',
                'is_active' => 1
            ],
            [
                'name' => 'IVG Pro',
                'is_active' => 1
            ],
            [
                'name' => 'O Pro by Insta 10k',
                'is_active' => 1
            ],
            [
                'name' => 'Fuji Energy',
                'is_active' => 1
            ],
            [
                'name' => 'Duracell',
                'is_active' => 1
            ],
             [
                'name' => 'Panasonic',
                'is_active' => 1
             ],
              [
                'name' => 'Energizer',
                'is_active' => 1
              ],
              [
                'name' => 'Till Rolls',
                'is_active' => 1
              ],
              [
                'name' => 'FIFO',
                'is_active' => 1
            ],
             [
                'name' => 'Reach On USB',
                'is_active' => 1
            ],
            [
                'name' => 'Designer Fragrances',
                'is_active' => 1

            ],
             [
                'name' => 'Skins',
                'is_active' => 1
             ],
             [
                'name' => 'Warrior',
                'is_active' => 1
             ],
             [
                'name' => 'IVG Intense Nic Salts',
                'is_active' => 1
             ],
             [
                'name' => 'Elux Legend Nic Salt',
                'is_active' => 1
             ],
             [
                'name' => 'Elux Firerose',
                'is_active' => 1
             ],
              [
                'name' => 'Edge Liq Nic Salts',
                'is_active' => 1
              ],
               [
                'name' => 'Nordic Spirit!',
                'is_active' => 1
               ],
                  [
                'name' => 'Signature',
                'is_active' => 1
             ]
        ];

        Brand::insert($brands);
    }
}
