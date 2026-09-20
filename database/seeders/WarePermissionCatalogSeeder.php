<?php

namespace Database\Seeders;

use App\Models\WarePermission;
use Illuminate\Database\Seeder;

/**
 * The complete list of warehouse permissions, i.e. the single place the
 * permission names used by the sidebar, route map and roles are defined.
 *
 * Idempotent: safe to run against any database, it only adds what is missing.
 * (Two permissions the app relies on, manage_products and view_audit_logs,
 * existed in production but were created by no seeder, so a freshly built
 * environment lacked them.)
 */
class WarePermissionCatalogSeeder extends Seeder
{
    public const PERMISSIONS = [
        'access_pos',
        'adjust_stock',
        'approve_po',
        'approve_store_requests',
        'cancel_po',
        'create_po',
        'create_products',
        'create_stores',
        'delete_po',
        'delete_products',
        'delete_stores',
        'edit_po',
        'edit_products',
        'edit_stores',
        'export_reports',
        'manage_audits',
        'manage_categories',
        'manage_enquiries',
        'manage_inventory',
        'manage_invoices',
        'manage_marketing_assets',
        'manage_min_max',
        'manage_min_max_levels',
        'manage_payments',
        'manage_pricing',
        'manage_product_options',
        'manage_products',
        'manage_promotions',
        'manage_recall_requests',
        'manage_recalls',
        'manage_roles',
        'manage_settings',
        'manage_staff',
        'manage_store_inventory',
        'manage_subcategories',
        'manage_support',
        'manage_users',
        'manage_vendors',
        'modify_approved_po',
        'override_po_quantities',
        'receive_po',
        'receive_stock',
        'report_damages',
        'transfer_stock',
        'upload_images',
        'view_all_tickets',
        'view_analytics',
        'view_audit_logs',
        'view_audits',
        'view_compliance',
        'view_dashboard',
        'view_discrepancies',
        'view_expiry_damage_report',
        'view_expiry_report',
        'view_financial_reports',
        'view_inventory',
        'view_margins',
        'view_po',
        'view_po_alerts',
        'view_products',
        'view_staff',
        'view_staff_performance',
        'view_stock_control',
        'view_stock_movement',
        'view_stock_overview',
        'view_stock_requests',
        'view_stock_valuation',
        'view_stores',
        'view_transfers',
        'view_vendors',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $name) {
            WarePermission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }
}
