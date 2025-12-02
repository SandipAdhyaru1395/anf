<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
   public function run()
    {
       
        $menuJson = file_get_contents(__DIR__ . '/../../resources/menu/verticalMenu.json');

        $menuData = json_decode($menuJson, true);
    
        DB::table('menus')->truncate();

        $this->seedMenu($menuData['menu']);
    }

    private function seedMenu(array $items, $parentId = null)
    {
        foreach ($items as $item) {

            $menuId = DB::table('menus')->insertGetId([
                'name'      => $item['name'],
                'slug'      => $item['slug'],
                'icon'      => $item['icon'] ?? null,
                'url'       => $item['url'] ?? null,
                'parent_id' => $parentId,
                'created_at'=> now(),
                'updated_at'=> now(),
            ]);

            if (isset($item['submenu'])) {
                $this->seedMenu($item['submenu'], $menuId);
            }
        }
    }
}
