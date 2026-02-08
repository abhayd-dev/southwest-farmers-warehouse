<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareRole;
use App\Models\WarePermission;
use App\Models\WareUser;
use Illuminate\Support\Facades\DB;

class WareRolePermissionSeeder extends Seeder
{
    public function run()
    {
        // ----------------------------------------------------
        // 0. FIX POSTGRES SEQUENCE (Important for Imports)
        // ----------------------------------------------------
        if (DB::getDriverName() == 'pgsql') {
            $tables = ['ware_permissions', 'ware_roles'];
            foreach ($tables as $table) {
                // Check if table has data
                if (DB::table($table)->exists()) {
                    DB::statement("SELECT setval('{$table}_id_seq', (SELECT MAX(id) FROM {$table}))");
                }
            }
        }

        // ====================================================
        // 1. DEFINE ALL PERMISSIONS (Old + New)
        // ====================================================
        $permissions = [
            // --- Dashboard ---
            'view_dashboard',
            'view_analytics',

            // --- Warehouse & Inventory ---
            'view_inventory',       // View Stock Levels
            'manage_inventory',     // Add/Edit Stock manually
            'adjust_stock',         // Adjustment Page
            'view_stock_movement',  // History

            // --- Product Catalog ---
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            'manage_categories',    // Categories & Subcategories
            'manage_product_options', // Variants/Options
            
            // --- Stores ---
            'view_stores',
            'create_stores',
            'edit_stores',
            'delete_stores',

            // --- Procurement (PO & Vendors) ---
            'view_vendors',
            'manage_vendors',
            'view_po',
            'create_po',
            'edit_po',
            'approve_po',           // Approve Draft PO
            'receive_po',           // Receive Items
            'delete_po',

            // --- Fulfillment (Store Orders) ---
            'view_stock_requests',
            'approve_store_requests', // Dispatch items to store
            'view_discrepancies',     // Returns/Shortages

            // --- Stock Control (NEW SECTIONS) ---
            'view_stock_control',     // Parent Menu Access
            'view_stock_overview',    // Consolidated View
            'view_transfers',         // Transfer Monitor
            'manage_recalls',         // Recall Stock
            'view_stock_valuation',   // Financial Value of Stock
            'manage_min_max',         // Set Reorder Points
            'view_audits',            // View Cycle Counts
            'manage_audits',          // Start/Finalize Audits

            // --- Finance & Reports ---
            'view_financial_reports', // Ledger & Revenue
            'view_expiry_report',     // Expiry Dashboard
            'export_reports',

            // --- Administration ---
            'view_staff',
            'manage_staff',           // Create/Edit/Delete Users
            'manage_roles',           // Permissions
            'manage_settings',        // General Settings & Automation

            // --- Support ---
            'view_all_tickets',       // Helpdesk Access
            'manage_support',         // Reply/Close Tickets
        ];

        // Create Permissions if they don't exist
        foreach ($permissions as $perm) {
            WarePermission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ====================================================
        // 2. DEFINE ROLES & ASSIGN PERMISSIONS
        // ====================================================

        // --- SUPER ADMIN (All Permissions) ---
        $superAdmin = WareRole::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->permissions()->sync(WarePermission::all());

        // --- VP OPERATIONS (Head of Warehouse) ---
        $vpOps = WareRole::firstOrCreate(['name' => 'VP Operations', 'guard_name' => 'web']);
        $vpOps->permissions()->sync(WarePermission::whereIn('name', [
            'view_dashboard', 'view_analytics',
            'view_inventory', 'manage_inventory', 'adjust_stock',
            'view_products', 'manage_categories',
            'view_stores',
            'view_vendors', 'view_po', 'approve_po',
            'view_stock_requests', 'approve_store_requests', 'view_discrepancies',
            'view_stock_control', 'view_stock_overview', 'view_transfers', 'manage_recalls', 'view_stock_valuation', 'manage_min_max', 'view_audits',
            'view_financial_reports', 'view_expiry_report',
            'view_staff', 'manage_staff',
            'view_all_tickets'
        ])->pluck('id'));

        // --- INVENTORY MANAGER (Focus on Stock & Audits) ---
        $invManager = WareRole::firstOrCreate(['name' => 'Inventory Manager', 'guard_name' => 'web']);
        $invManager->permissions()->sync(WarePermission::whereIn('name', [
            'view_dashboard',
            'view_inventory', 'adjust_stock', 'view_stock_movement',
            'view_products', 'create_products', 'edit_products',
            'view_stock_requests', 'approve_store_requests',
            'view_stock_control', 'view_stock_overview', 'view_transfers', 'manage_recalls', 'manage_audits',
            'view_expiry_report'
        ])->pluck('id'));

        // --- PURCHASE MANAGER (Focus on PO & Vendors) ---
        $purManager = WareRole::firstOrCreate(['name' => 'Purchase Manager', 'guard_name' => 'web']);
        $purManager->permissions()->sync(WarePermission::whereIn('name', [
            'view_dashboard',
            'view_inventory',
            'view_vendors', 'manage_vendors',
            'view_po', 'create_po', 'edit_po', 'receive_po',
            'view_stock_valuation'
        ])->pluck('id'));

        // --- STAFF (Basic Access) ---
        $staff = WareRole::firstOrCreate(['name' => 'General Staff', 'guard_name' => 'web']);
        $staff->permissions()->sync(WarePermission::whereIn('name', [
            'view_inventory',
            'view_products',
            'receive_po' // Can create GRN
        ])->pluck('id'));

        // ====================================================
        // 3. ASSIGN ROLES TO USERS (Safe Update)
        // ====================================================
        
        $this->assignRoleToUser('admin@invoidea.com', 'Super Admin');
        $this->assignRoleToUser('vp@warehouse.com', 'VP Operations');
        $this->assignRoleToUser('inventory@warehouse.com', 'Inventory Manager');
        $this->assignRoleToUser('purchase@warehouse.com', 'Purchase Manager');
        $this->assignRoleToUser('staff@warehouse.com', 'General Staff');
    }

    private function assignRoleToUser($email, $roleName)
    {
        $user = WareUser::where('email', $email)->first();
        if ($user) {
            $role = WareRole::where('name', $roleName)->first();
            if ($role && !$user->roles->contains($role->id)) {
                $user->roles()->sync([$role->id]);
                $this->command->info("Assigned '$roleName' to $email");
            }
        }
    }
}