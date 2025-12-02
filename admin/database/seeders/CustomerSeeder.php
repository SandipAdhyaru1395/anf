<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\FavoriteProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\WalletTransaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Customer::truncate();
        Branch::truncate();
        FavoriteProduct::truncate();
        Order::truncate();
        OrderItem::truncate();
        OrderStatusHistory::truncate();
        WalletTransaction::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $customers = [
            [
                'email' => 'customer1@example.com',
                'phone' => '1111111111',
                'password' => 'password',
                'company_name' => 'Company 1',
                'company_address_line1' => 'Company Address Line 1',
                'company_address_line2' => 'Company Address Line 2',
                'company_city' => 'Company Address City',
                'company_country' => 'Company Address Country',
                'company_zip_code' => 'Company Address Postcode',
                'is_active' => 1,
            ],
            [
                'email' => 'customer2@example.com',
                'phone' => '2222222222',
                'password' => 'password',
                'company_name' => 'Company 2',
                'company_address_line1' => 'Company Address Line 1',
                'company_address_line2' => 'Company Address Line 2',
                'company_city' => 'Company Address City',
                'company_country' => 'Company Address Country',
                'company_zip_code' => 'Company Address Postcode',
                'is_active' => 1,
            ],
        ];

        // Customer::insert($customers);
        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
