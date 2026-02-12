<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clean existing data (Tables ko khali karein)
        // Order important hai taaki foreign key errors na aayein
        DB::statement('TRUNCATE TABLE store_role_has_permissions CASCADE');
        DB::statement('TRUNCATE TABLE store_roles RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE store_permissions RESTART IDENTITY CASCADE');

        $guard = 'store_user';
        $now = now();

        // ----------------------------------------------------------------
        // 2. Define & Insert Permissions
        // ----------------------------------------------------------------
        $permissionsList = [
            // Dashboard
            ['name' => 'view_dashboard', 'group' => 'Dashboard'],

            // POS & Orders
            ['name' => 'access_pos', 'group' => 'Orders & POS'],
            ['name' => 'create_order', 'group' => 'Orders & POS'],
            ['name' => 'view_orders', 'group' => 'Orders & POS'],
            ['name' => 'cancel_order', 'group' => 'Orders & POS'],
            ['name' => 'process_return', 'group' => 'Orders & POS'],

            // Products & Catalog
            ['name' => 'view_products', 'group' => 'Inventory & Merchandising'],
            ['name' => 'create_product', 'group' => 'Inventory & Merchandising'],
            ['name' => 'edit_product', 'group' => 'Inventory & Merchandising'],
            ['name' => 'delete_product', 'group' => 'Inventory & Merchandising'],
            ['name' => 'manage_recipes', 'group' => 'Inventory & Merchandising'],
            ['name' => 'view_categories', 'group' => 'Inventory & Merchandising'],
            ['name' => 'manage_categories', 'group' => 'Inventory & Merchandising'],

            // Promotions
            ['name' => 'view_promotions', 'group' => 'Marketing'],
            ['name' => 'create_promotion', 'group' => 'Marketing'],
            ['name' => 'manage_promotions', 'group' => 'Marketing'],

            // Inventory Operations
            ['name' => 'view_inventory', 'group' => 'Inventory'],
            ['name' => 'request_stock', 'group' => 'Inventory'],
            ['name' => 'adjust_stock', 'group' => 'Inventory'],
            ['name' => 'view_transfers', 'group' => 'Inventory Ops'],
            ['name' => 'create_transfer', 'group' => 'Inventory Ops'],
            ['name' => 'dispatch_transfer', 'group' => 'Inventory Ops'],
            ['name' => 'receive_transfer', 'group' => 'Inventory Ops'],

            // Audits & Reports
            ['name' => 'view_audits', 'group' => 'Stock Control'],
            ['name' => 'create_audit', 'group' => 'Stock Control'],
            ['name' => 'perform_audit', 'group' => 'Stock Control'],
            ['name' => 'finalize_audit', 'group' => 'Stock Control'],
            ['name' => 'view_sales_report', 'group' => 'Reports'],
            ['name' => 'view_stock_report', 'group' => 'Reports'],
            ['name' => 'view_analytics', 'group' => 'Reports'],

            // Customers & Support
            ['name' => 'view_customers', 'group' => 'Customers'],
            ['name' => 'manage_customers', 'group' => 'Customers'],
            ['name' => 'view_tickets', 'group' => 'Support'],
            ['name' => 'raise_ticket', 'group' => 'Support'],

            // Admin Only
            ['name' => 'view_staff', 'group' => 'Staff Management'],
            ['name' => 'manage_staff', 'group' => 'Staff Management'],
            ['name' => 'manage_roles', 'group' => 'Staff Management'],
            ['name' => 'view_settings', 'group' => 'Settings'],
            ['name' => 'update_settings', 'group' => 'Settings'],
        ];

        // Insert Permissions
        foreach ($permissionsList as $perm) {
            DB::table('store_permissions')->insert([
                'name' => $perm['name'],
                'guard_name' => $guard,
                'group_name' => $perm['group'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Fetch all permissions to assign via ID later
        $allPermissions = DB::table('store_permissions')->pluck('id', 'name');

        // ----------------------------------------------------------------
        // 3. Define & Insert Roles
        // ----------------------------------------------------------------
        $roles = [
            'Super Admin',      // Sab kuch kar sakta hai
            'Store Manager',    // Operations head, but maybe restricted from critical settings
            'Inventory Manager',// Sirf stock, transfers aur audits
            'Cashier',          // Sirf POS aur Orders
            'Sales Staff'       // Floor staff, sirf view access
        ];

        $roleIds = [];
        foreach ($roles as $roleName) {
            $id = DB::table('store_roles')->insertGetId([
                'name' => $roleName,
                'guard_name' => $guard,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $roleIds[$roleName] = $id;
        }

        // ----------------------------------------------------------------
        // 4. Assign Permissions to Roles
        // ----------------------------------------------------------------

        // -> SUPER ADMIN (Give All Permissions)
        foreach ($allPermissions as $permName => $permId) {
            DB::table('store_role_has_permissions')->insert([
                'permission_id' => $permId,
                'role_id' => $roleIds['Super Admin']
            ]);
        }

        // -> STORE MANAGER (Sab kuch except Role/Staff management agar chaho to)
        // Abhi ke liye Manager ko bhi sab de dete hain except maybe critical deletions
        $managerPermissions = $allPermissions->reject(function ($id, $name) {
            return in_array($name, ['delete_product', 'delete_staff']); // Manager delete nahi kar payega
        });
        foreach ($managerPermissions as $permId) {
            DB::table('store_role_has_permissions')->insert([
                'permission_id' => $permId,
                'role_id' => $roleIds['Store Manager']
            ]);
        }

        // -> INVENTORY MANAGER (Only Stock related)
        $inventoryPerms = [
            'view_inventory', 'request_stock', 'adjust_stock',
            'view_transfers', 'create_transfer', 'dispatch_transfer', 'receive_transfer',
            'view_audits', 'create_audit', 'perform_audit', 'finalize_audit',
            'view_stock_report', 'view_products', 'view_categories'
        ];
        foreach ($inventoryPerms as $name) {
            if (isset($allPermissions[$name])) {
                DB::table('store_role_has_permissions')->insert([
                    'permission_id' => $allPermissions[$name],
                    'role_id' => $roleIds['Inventory Manager']
                ]);
            }
        }

        // -> CASHIER (Only POS, Orders, Customers)
        $cashierPerms = [
            'access_pos', 'create_order', 'view_orders', 'process_return',
            'view_daily_sales', 'view_products', 'view_customers', 'create_customer'
        ];
        foreach ($cashierPerms as $name) {
            if (isset($allPermissions[$name])) {
                DB::table('store_role_has_permissions')->insert([
                    'permission_id' => $allPermissions[$name],
                    'role_id' => $roleIds['Cashier']
                ]);
            }
        }

        // -> SALES STAFF (Basic View Access)
        $staffPerms = [
            'view_products', 'view_inventory'
        ];
        foreach ($staffPerms as $name) {
            if (isset($allPermissions[$name])) {
                DB::table('store_role_has_permissions')->insert([
                    'permission_id' => $allPermissions[$name],
                    'role_id' => $roleIds['Sales Staff']
                ]);
            }
        }
    }
}