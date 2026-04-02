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
                'vat_number' => 'GB123456789',
                'eori_number' => 'GB123456789000',
                'is_part_of_group' => 1,
                'business_type' => 'Wholesaler',
                'average_monthly_spend_ex_vat' => '5000',
                'stores_serviced_count' => 3,
                'contact_person_name' => 'Customer One',
                'is_active' => 1,
                'is_approved' => 1,
                'approved_at' => now(),
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
                'vat_number' => null,
                'eori_number' => null,
                'is_part_of_group' => 0,
                'business_type' => 'Retailer',
                'average_monthly_spend_ex_vat' => '1500-2500',
                'stores_serviced_count' => 1,
                'contact_person_name' => 'Customer Two',
                'is_active' => 1,
                'is_approved' => 1,
                'approved_at' => now(),
            ],
        ];

        // Customer::insert($customers);
        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
