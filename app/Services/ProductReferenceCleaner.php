<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Deleting a product must first remove the rows that reference it in tables
 * without a database-level cascade. Used by single, bulk and delete-all.
 */
class ProductReferenceCleaner
{
    private const TABLES = [
        'pallet_items',
        'sale_return_items',
        'sale_items',
        'stock_transfers',
        'stock_audit_items',
        'purchase_order_items',
        'store_purchase_order_items',
    ];

    /** @param int[] $productIds */
    public function deleteFor(array $productIds): void
    {
        if ($productIds === []) {
            return;
        }

        foreach (self::TABLES as $table) {
            DB::table($table)->whereIn('product_id', $productIds)->delete();
        }
    }
}
