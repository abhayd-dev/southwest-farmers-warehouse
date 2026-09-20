<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for columns that are joined or filtered on constantly but had none
 * (found by comparing the live schema against real table sizes):
 *   - stock_audit_items.stock_audit_id / product_id  the largest table (28k rows); every audit screen reads it
 *   - ware_notifications.user_id                     read on every page load for the bell icon
 *   - ware_role_has_permissions.role_id              joined by every permission check
 *   - store_stocks.product_id, products.*_id, purchase_order_items.*_id, product_options.*_id
 *   - ware_activity_logs.created_at                  the audit log is sorted/filtered by date
 *
 * Each is skipped when an index on that column already exists.
 */
return new class extends Migration
{
    private const INDEXES = [
        'stock_audit_items' => ['stock_audit_id', 'product_id'],
        'ware_notifications' => ['user_id'],
        'ware_role_has_permissions' => ['role_id'],
        'store_stocks' => ['product_id'],
        'ware_activity_logs' => ['created_at'],
        'products' => ['category_id', 'department_id', 'subcategory_id'],
        'purchase_order_items' => ['purchase_order_id', 'product_id'],
        'product_options' => ['category_id', 'subcategory_id'],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $column) && !Schema::hasIndex($table, [$column])) {
                    Schema::table($table, fn (Blueprint $t) => $t->index($column));
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $columns) {
            foreach ($columns as $column) {
                $name = "{$table}_{$column}_index";
                if (Schema::hasTable($table) && Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }
};
