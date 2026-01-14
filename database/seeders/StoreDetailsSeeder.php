<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreDetailsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('store_details')->updateOrInsert(
            // UNIQUE KEY (where condition)
            ['store_code' => 'SWF-LKO-001'],

            // DATA TO INSERT / UPDATE
            [
                'warehouse_id'  => 1,
                'store_user_id' => null, // optional

                'store_name' => 'Southwest Farmers – Lucknow',
                'email'      => 'lucknow@southwestfarmers.com',
                'phone'      => '9876543210',

                'address' => 'Hazratganj Main Road',
                'city'    => 'Lucknow',
                'state'   => 'Uttar Pradesh',
                'country' => 'India',
                'pincode' => '226001',

                'latitude'  => 26.846708,
                'longitude' => 80.946159,

                'is_active' => true,

                'updated_at' => now(),
                'created_at' => now(), // ignored on update
            ]
        );
    }
}
