<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // Prevent duplicate warehouse
        if (Warehouse::count() > 0) {
            return;
        }

        Warehouse::create([
            'warehouse_name' => 'Central Warehouse',
            'code'           => 'WH-001',
            'email'          => 'warehouse@company.com',
            'phone'          => '9999999999',
            'address'        => 'Main Industrial Area',
            'city'           => 'Lucknow',
            'state'          => 'Uttar Pradesh',
            'country'        => 'India',
            'pincode'        => '226010',
            'is_active'      => true,
        ]);
    }
}
