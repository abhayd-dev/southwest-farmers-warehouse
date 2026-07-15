<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete all purchase orders that reference a non-existent vendor_id.
        // Database cascade constraints will automatically delete linked purchase_order_items.
        DB::table('purchase_orders')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('vendors')
                      ->whereColumn('vendors.id', 'purchase_orders.vendor_id');
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse action needed
    }
};
