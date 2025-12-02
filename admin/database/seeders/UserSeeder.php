<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Upsert a predictable test user
    User::withTrashed()->updateOrCreate(
      ['email' => 'superadmin@example.com'],
      [
        'name' => 'superadmin',
        'role_id' => 1,
        'image' => asset('assets/img/avatars/5.png'),
        'password' => 'password', // hashed by model cast
        'status' => 'active'
      ]
    );

    User::withTrashed()->updateOrCreate(
      ['email' => 'salesperson1@example.com'],
      [
        'name' => 'Sales Person1',
        'role_id' => 2,
        'image' => asset('assets/img/avatars/1.png'),
        'password' => 'password', // hashed by model cast
        'phone' => '9999999999',
        'status' => 'active'
      ]
    );

    User::withTrashed()->updateOrCreate(
      ['email' => 'salesperson2@example.com'],
      [
        'name' => 'Sales Person2',
        'role_id' => 2,
        'image' => asset('assets/img/avatars/2.png'),
        'password' => 'password', // hashed by model cast
        'phone' => '9999999999',
        'status' => 'active'
      ]
    );

    User::withTrashed()->updateOrCreate(
      ['email' => 'admin@example.com'],
      [
        'name' => 'admin',
        'role_id' => 3,
        'image' => asset('assets/img/avatars/5.png'),
        'password' => 'password', // hashed by model cast
        'phone' => '1111111111',
        'status' => 'active'
      ]
    );


    User::withTrashed()->updateOrCreate(
      ['email' => 'manager1@example.com'],
      [
        'name' => 'manager1',
        'role_id' => 4,
         'image' => asset('assets/img/avatars/12.png'),
        'password' => 'password', // hashed by model cast
        'phone' => '2222222222',
        'status' => 'active'
      ]
    );

    User::withTrashed()->updateOrCreate(
      ['email' => 'manager2@example.com'],
      [
        'name' => 'manager2',
        'role_id' => 4,
         'image' => asset('assets/img/avatars/6.png'),
        'password' => 'password', // hashed by model cast
        'phone' => '3333333333',
        'status' => 'active'
      ]
    );
    
    User::withTrashed()->updateOrCreate(
      ['email' => 'manager3@example.com'],
      [
        'name' => 'manager3',
        'role_id' => 4,
         'image' => asset('assets/img/avatars/3.png'),
        'password' => 'password', // hashed by model cast
        'phone' => '4444444444',
        'status' => 'active'
      ]
    );

    User::withTrashed()->updateOrCreate(
      ['email' => 'manager4@example.com'],
      [
        'name' => 'manager4',
        'role_id' => 4,
         'image' => asset('assets/img/avatars/5.png'),
        'password' => 'password', // hashed by model cast
        'phone' => '5555555555',
        'status' => 'active'
      ]
    );
    
    User::withTrashed()->updateOrCreate(
      ['email' => 'manager5@example.com'],
      [
        'name' => 'manager5',
        'role_id' => 4,
         'image' => asset('assets/img/avatars/12.png'),
        'password' => 'password', // hashed by model cast
        'phone' => '6666666666',
        'status' => 'active'
      ]
    );

     User::withTrashed()->updateOrCreate(
      ['email' => 'manager6@example.com'],
      [
        'name' => 'manager6',
        'role_id' => 4,
         'image' => asset('assets/img/avatars/6.png'),
        'password' => 'password', // hashed by model cast
        'phone' => '7777777777',
        'status' => 'active'
      ]
    );

     User::withTrashed()->updateOrCreate(
      ['email' => 'user@example.com'],
      [
        'name' => 'user',
        'role_id' => 5,
         'image' => asset('assets/img/avatars/3.png'),
        'password' => 'password', // hashed by model cast
        'phone' => '8888888888',
        'status' => 'active'
      ]
    );
  }
}
