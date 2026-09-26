<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QA: "Receiving orders still cannot accept decimal values" -- e.g. produce
 * received by weight (12.5 lb). Stock tables (product_stocks, product_batches,
 * stock_transactions) are already numeric; only the PO lines were integers.
 * Integer -> numeric(15,2) is lossless for existing rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('requested_quantity', 15, 2)->change();
            $table->decimal('received_quantity', 15, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->integer('requested_quantity')->change();
            $table->integer('received_quantity')->default(0)->change();
        });
    }
};
