<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StoreExtendedRolePermissionSeeder extends Seeder
{
    public function run()
    {
        $guard = 'store_user';
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | 1. Missing Permissions (based on Image + Sidebar)
        |--------------------------------------------------------------------------
        */
        $permissionsByGroup = [

            'Purchase Orders Advanced' => [
                'approve_po',
                'reject_po',
                'escalate_po',
                'view_po_timeline',
                'receive_po',
                'close_po'
            ],

            'Inventory' => [
                'view_inventory',
                'request_stock',
                'approve_stock_request',
                'adjust_stock',
                'view_stock_history'
            ],

            'Stock Control' => [
                'view_stock_control',
                'view_stock_valuation',
                'manage_recall_requests',
                'approve_recall_request'
            ],

            'Orders & POS' => [
                'access_pos',
                'create_order',
                'view_orders',
                'cancel_order',
                'process_return'
            ],

            'Sales & Billing' => [
                'view_daily_sales',
                'view_monthly_sales',
                'view_tax_summary'
            ],

            'Customers' => [
                'view_customers',
                'create_customer',
                'edit_customer'
            ],

            'Reports' => [
                'view_sales_report',
                'view_stock_report'
            ],

            'Support' => [
                'raise_ticket',
                'view_ticket_history',
                'manage_tickets'
            ],

            'Staff Management' => [
                'view_staff',
                'create_staff',
                'edit_staff',
                'disable_staff'
            ],

            'Settings' => [
                'view_settings',
                'update_general_settings',
                'update_store_settings'
            ]
        ];

        $permMap = [];

        foreach ($permissionsByGroup as $group => $permissions) {
            foreach ($permissions as $perm) {

                $existing = DB::table('store_permissions')
                    ->where('name', $perm)
                    ->where('guard_name', $guard)
                    ->first();

                if ($existing) {
                    $permMap[$perm] = $existing->id;
                    continue;
                }

                $permMap[$perm] = DB::table('store_permissions')->insertGetId([
                    'name' => $perm,
                    'guard_name' => $guard,
                    'group_name' => $group,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Assign Missing Permissions to Existing Roles
        |--------------------------------------------------------------------------
        */
        $rolePermissionMap = [

            'Super Admin' => array_keys($permMap),

            'Regional Manager' => [
                'approve_po',
                'reject_po',
                'escalate_po',
                'view_po_timeline',
                'view_stock_control',
                'view_stock_valuation',
                'view_orders',
                'view_sales_report',
                'view_stock_report'
            ],

            'General Manager' => [
                'approve_po',
                'reject_po',
                'receive_po',
                'close_po',
                'approve_stock_request',
                'manage_recall_requests',
                'view_orders',
                'view_daily_sales',
                'view_monthly_sales'
            ],

            'Manager' => [
                'request_stock',
                'adjust_stock',
                'view_inventory',
                'access_pos',
                'create_order',
                'process_return',
                'view_customers'
            ],

            'Supervisor' => [
                'view_inventory',
                'request_stock',
                'view_orders',
                'raise_ticket'
            ],

            'Cashier' => [
                'access_pos',
                'create_order',
                'process_return',
                'view_customers'
            ],

            'General Staff' => [
                'view_inventory',
                'request_stock',
                'raise_ticket'
            ],

            'Receptionist' => [
                'view_customers',
                'create_customer',
                'view_ticket_history'
            ],
        ];

        foreach ($rolePermissionMap as $roleName => $permissions) {

            $roleId = DB::table('store_roles')
                ->where('name', $roleName)
                ->where('guard_name', $guard)
                ->value('id');

            if (!$roleId) {
                continue;
            }

            foreach ($permissions as $permName) {

                if (!isset($permMap[$permName])) {
                    continue;
                }

                $exists = DB::table('store_role_has_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permMap[$permName])
                    ->exists();

                if (!$exists) {
                    DB::table('store_role_has_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permMap[$permName],
                    ]);
                }
            }
        }

        $this->command->info('Extended Store Permissions & Role Assignments Seeded Successfully!');
    }
}
