<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Super Admin / Base
            AssignSuperAdminRoleSeeder::class,

            // Warehouse
            WarehouseSeeder::class,
            WareRolePermissionSeeder::class,
            WareStockControlPermissionsSeeder::class,
            WareUserSeeder::class,

            // Store
            StoreDetailsSeeder::class,
            StoreRolePermissionSeeder::class,
            StoreUserSeeder::class,

            // Products
            ProductCategoryAndSubcategorySeeder::class,
            ProductAndOptionSeeder::class,

            // Analytics / Extras
            StoreAnalyticsSeeder::class,
        ]);
    }
}
