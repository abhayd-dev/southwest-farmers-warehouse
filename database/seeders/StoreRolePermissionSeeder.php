<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StoreRolePermissionSeeder extends Seeder
{
    public function run()
    {
        $guard = 'store_web';
        $now = Carbon::now();

        // 1. Clear Old Data (Optional - Safety Check)
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::table('store_role_has_permissions')->truncate();
        // DB::table('store_roles')->truncate();
        // DB::table('store_permissions')->truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /*
        |--------------------------------------------------------------------------
        | 2. Insert Permissions
        |--------------------------------------------------------------------------
        */
        $permissionsList = [
            // Store Operations (PO)
            'create_po', 'view_po', 'edit_po_generated', 'view_po_logs', 'modify_po', 'view_po_alerts',
            
            // Escalations
            'view_escalations',

            // Inventory & Merchandising
            'update_pricing', 'check_stock_levels', 'manage_signage', 'manage_displays', 'manage_promotions',

            // Staff Operations
            'clock_in_out', 'manage_breaks', 'view_attendance', 'manage_staff'
        ];

        $permMap = []; // To store ID mapping for later use

        foreach ($permissionsList as $perm) {
            // Check if exists to avoid duplicates
            $exists = DB::table('store_permissions')->where('name', $perm)->where('guard_name', $guard)->first();
            
            if (!$exists) {
                $id = DB::table('store_permissions')->insertGetId([
                    'name' => $perm,
                    'guard_name' => $guard,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                $permMap[$perm] = $id;
            } else {
                $permMap[$perm] = $exists->id;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Insert Roles & Map Permissions
        |--------------------------------------------------------------------------
        */
        $rolesData = [
            'Super Admin' => array_keys($permMap), // All Permissions
            
            'Regional Manager' => [
                'view_po', 'view_po_logs', 'view_po_alerts', 'view_escalations',
                'check_stock_levels', 'view_attendance', 'manage_staff'
            ],

            'General Manager' => [
                'create_po', 'view_po', 'edit_po_generated', 'view_po_logs', 'view_po_alerts', 'view_escalations',
                'check_stock_levels', 'manage_displays', 'manage_promotions', 'view_attendance', 'manage_staff'
            ],

            'Manager' => [
                'create_po', 'view_po', 'view_po_logs', 'modify_po',
                'update_pricing', 'check_stock_levels', 'manage_signage', 'manage_displays',
                'view_attendance', 'manage_staff'
            ],

            'Supervisor' => [
                'view_po', 'check_stock_levels', 'manage_displays',
                'clock_in_out', 'manage_breaks'
            ],

            'Cashier' => [
                'clock_in_out', 'manage_breaks'
            ],

            'General Staff' => [
                'clock_in_out', 'manage_breaks', 'manage_displays', 'check_stock_levels'
            ],

            'Receptionist' => [
                'clock_in_out', 'manage_breaks', 'view_attendance'
            ],
        ];

        foreach ($rolesData as $roleName => $assignedPerms) {
            // Create Role
            $roleId = DB::table('store_roles')->where('name', $roleName)->where('guard_name', $guard)->value('id');

            if (!$roleId) {
                $roleId = DB::table('store_roles')->insertGetId([
                    'name' => $roleName,
                    'guard_name' => $guard,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }

            // Assign Permissions to Role
            foreach ($assignedPerms as $permName) {
                if (isset($permMap[$permName])) {
                    // Avoid duplicate entry
                    $exists = DB::table('store_role_has_permissions')
                        ->where('permission_id', $permMap[$permName])
                        ->where('role_id', $roleId)
                        ->exists();

                    if (!$exists) {
                        DB::table('store_role_has_permissions')->insert([
                            'permission_id' => $permMap[$permName],
                            'role_id' => $roleId
                        ]);
                    }
                }
            }
        }

        $this->command->info('Custom Store Roles & Permissions Seeded Successfully!');
    }
}