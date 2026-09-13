<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareRole;
use App\Models\WarePermission;

/**
 * Phase 7 — Warehouse Manager role.
 *
 * This role sits between "Inventory Manager" (stock/audits focus) and
 * "VP Operations" (full oversight): a floor-operations manager responsible
 * for day-to-day warehouse activity and incoming store orders, but without
 * admin-level access (no staff/role/settings management).
 */
class Phase7WarehouseManagerRoleSeeder extends Seeder
{
    public function run()
    {
        $role = WareRole::firstOrCreate(['name' => 'Warehouse Manager', 'guard_name' => 'web']);

        $role->permissions()->syncWithoutDetaching(WarePermission::whereIn('name', [
            'view_dashboard', 'view_analytics',

            // Warehouse & Inventory
            'view_inventory', 'manage_inventory', 'adjust_stock', 'view_stock_movement',

            // Product Catalog (view only)
            'view_products',

            // Procurement (view + receive, not create/approve — that stays with Purchase Manager)
            'view_vendors', 'view_po', 'receive_po',

            // Fulfillment (Store Orders) — this is the "incoming orders" surface
            'view_stock_requests', 'approve_store_requests', 'view_discrepancies',

            // Stock Control
            'view_stock_control', 'view_stock_overview', 'view_transfers',
            'manage_recalls', 'view_stock_valuation', 'manage_min_max',
            'view_audits', 'manage_audits',

            // Reports relevant to floor operations
            'view_expiry_report', 'export_reports',

            // Can see staff list but not manage it
            'view_staff',
        ])->pluck('id'));

        $this->command?->info("Warehouse Manager role ready with " . $role->permissions()->count() . " permissions.");
    }
}
