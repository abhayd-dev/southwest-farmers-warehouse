<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client 9/27: an order received short with nothing more coming (ordered 100,
 * 75 arrived) can be completed, and the invoice is reduced to what was
 * received. The short lines keep the original ordered quantity for the record.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'short_close_lines')) {
                $table->json('short_close_lines')->nullable();     // [{item_id, product, ordered, received, unit_cost}]
                $table->string('short_closed_by')->nullable();
                $table->timestamp('short_closed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            foreach (['short_close_lines', 'short_closed_by', 'short_closed_at'] as $col) {
                if (Schema::hasColumn('purchase_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
