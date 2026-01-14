<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareRole;
use App\Models\WarePermission;
use Illuminate\Support\Facades\DB;

class WareRolePermissionSeeder extends Seeder
{
    public function run()
    {
        // ---------------------------------------
        // 1. Clear Existing Data (Postgres Safe)
        // ---------------------------------------
        DB::statement('TRUNCATE TABLE 
            ware_role_has_permissions,
            ware_model_has_roles,
            ware_model_has_permissions,
            ware_roles,
            ware_permissions
            RESTART IDENTITY CASCADE
        ');

        // ---------------------------------------
        // 2. Permission Master List (FINAL)
        // ---------------------------------------
        $permissions = [

            // Dashboard & Analytics
            'view_dashboard',
            'view_analytics',

            // Warehouse & Inventory
            'view_inventory',
            'manage_inventory',
            'adjust_stock',
            'transfer_stock',
            'receive_stock',
            'view_stock_movement',
            'report_damages',

            // Products & Catalog
            'view_products',
            'manage_products',
            'manage_categories',
            'manage_subcategories',
            'manage_product_options',
            'upload_images',

            // Purchase Orders (PO)
            'view_po',
            'create_po',
            'edit_po',
            'approve_po',
            'cancel_po',
            'override_po_quantities',
            'modify_approved_po',
            'view_po_alerts',

            // Finance & Accounts
            'view_financial_reports',
            'manage_invoices',
            'manage_payments',
            'view_margins',
            'manage_pricing',
            'view_compliance',

            // Stores
            'view_stores',
            'manage_store_inventory',
            'approve_store_requests',

            // Users & System
            'manage_users',
            'manage_roles',
            'view_audit_logs',
            'view_staff_performance',

            // Marketing
            'manage_marketing_assets',

            // POS
            'access_pos',
        ];

        foreach ($permissions as $perm) {
            WarePermission::create([
                'name' => $perm,
                'guard_name' => 'web'
            ]);
        }

        // ---------------------------------------
        // 3. Roles + Permission Mapping
        // ---------------------------------------
        $roles = [

            'Super Admin' => $permissions,

            'CEO' => [
                'view_dashboard',
                'view_analytics',
                'view_inventory',
                'view_financial_reports',
                'approve_po',
                'view_audit_logs',
                'view_staff_performance'
            ],

            'CFO' => [
                'approve_po',
                'view_financial_reports',
                'manage_payments',
                'view_margins',
                'view_compliance',
                'view_audit_logs'
            ],

            'VP Operations' => [
                'view_inventory',
                'create_po',
                'edit_po',
                'approve_po',
                'transfer_stock',
                'override_po_quantities',
                'modify_approved_po',
                'view_po_alerts',
                'view_audit_logs'
            ],

            'Purchase Manager' => [
                'view_po',
                'create_po',
                'edit_po'
            ],

            'Inventory Manager' => [
                'view_inventory',
                'receive_stock',
                'manage_inventory',
                'report_damages',
                'view_stock_movement'
            ],

            'Accountant' => [
                'manage_invoices',
                'manage_payments',
                'view_financial_reports'
            ],

            'Finance Assistant' => [
                'manage_invoices',
                'manage_payments'
            ],

            'Store Handler' => [
                'view_stores',
                'receive_stock',
                'report_damages'
            ],

            'Brand Manager' => [
                'upload_images',
                'manage_marketing_assets',
                'view_products'
            ],

            'Community Coordinator' => [
                'view_stores',
                'manage_marketing_assets'
            ],

            'Personal Assistant' => [
                'view_dashboard',
                'view_financial_reports'
            ],

            'Regional Manager' => [
                'view_inventory',
                'view_po_alerts',
                'approve_store_requests',
                'view_staff_performance'
            ],

            'General Manager' => [
                'manage_store_inventory',
                'approve_store_requests',
                'view_analytics'
            ],

            'Supervisor' => [
                'view_inventory',
                'view_staff_performance'
            ],

            'Cashier' => [
                'access_pos'
            ],

            'General Staff' => [
                // Intentionally minimal
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = WareRole::create([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);

            if (!empty($perms)) {
                $permissionIds = WarePermission::whereIn('name', $perms)->pluck('id');
                $role->permissions()->sync($permissionIds);
            }
        }
    }
}
