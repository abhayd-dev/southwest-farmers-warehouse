<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreDetailsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('store_details')->insert([
            [
                // Warehouse Mapping
                'warehouse_id' => 1, // must exist in ware_details
                'store_user_id' => 1, // optional, can be null if not created yet

                // Store Info
                'store_name' => 'Southwest Farmers – Lucknow',
                'store_code' => 'SWF-LKO-001',
                'email' => 'lucknow@southwestfarmers.com',
                'phone' => '9876543210',

                // Address
                'address' => 'Hazratganj Main Road',
                'city' => 'Lucknow',
                'state' => 'Uttar Pradesh',
                'country' => 'India',
                'pincode' => '226001',

                // Map Location
                'latitude' => 26.846708,
                'longitude' => 80.946159,

                // Status
                'is_active' => true,

                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
