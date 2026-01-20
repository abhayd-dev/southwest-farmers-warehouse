<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WarePermission; // Assuming your model name

class WareStockControlPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Main access
            'view_stock_control'              => 'View Stock Control Section',

            // Sub-features
            'view_stock_overview'             => 'View Consolidated Stock Overview',
            'manage_recall_requests'          => 'Create/Process Recall Stock Requests',
            'view_expiry_damage_report'       => 'View Expiry & Damage Report',
            'view_stock_valuation'            => 'View Stock Valuation Report',
            'manage_min_max_levels'           => 'Manage Min-Max Stock Levels',
        ];

        foreach ($permissions as $name => $description) {
            WarePermission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        $this->command->info('Stock Control permissions seeded successfully!');
    }
}