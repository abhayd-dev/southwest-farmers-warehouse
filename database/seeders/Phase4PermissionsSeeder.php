<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareRole;
use App\Models\WarePermission;

class Phase4PermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Define New Permissions
        $permissions = [
            'view_sales_report',
            'view_stock_movement_report',
            'view_inventory_health_report',
            'view_fast_moving_report',
            'view_activity_logs',
        ];

        // 2. Create Permissions
        foreach ($permissions as $perm) {
            WarePermission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 3. Assign to Roles

        // Super Admin (All)
        $superAdmin = WareRole::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->syncWithoutDetaching(WarePermission::whereIn('name', $permissions)->pluck('id'));
        }

        // VP Operations (All Reports + Logs)
        $vpOps = WareRole::where('name', 'VP Operations')->first();
        if ($vpOps) {
            $vpOps->permissions()->syncWithoutDetaching(WarePermission::whereIn('name', $permissions)->pluck('id'));
        }

        // Inventory Manager (Selected Reports)
        $invManager = WareRole::where('name', 'Inventory Manager')->first();
        if ($invManager) {
            $invManager->permissions()->syncWithoutDetaching(WarePermission::whereIn('name', [
                'view_stock_movement_report',
                'view_inventory_health_report',
                'view_fast_moving_report'
            ])->pluck('id'));
        }

        // Purchase Manager (Selected Reports)
        $purManager = WareRole::where('name', 'Purchase Manager')->first();
        if ($purManager) {
            $purManager->permissions()->syncWithoutDetaching(WarePermission::whereIn('name', [
                'view_stock_movement_report', // To see incoming stock
                'view_inventory_health_report' // To see what to buy
            ])->pluck('id'));
        }
    }
}
