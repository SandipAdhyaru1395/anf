<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::truncate();
        
        $roles = [
            ['name' => 'Super Admin'],
            ['name' => 'Sales Person'],
            ['name' => 'Administrator'],
            ['name' => 'Manager'],
            ['name' => 'User']
        ];
        
        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
