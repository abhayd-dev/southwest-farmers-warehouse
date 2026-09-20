<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `stock_request_items` exists in production but no migration in this repo
 * creates it, so a database built from migrations alone was missing it.
 * Guarded, so it's a no-op wherever the table already exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_request_items')) {
            return;
        }

        Schema::create('stock_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_request_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->integer('quantity')->default(0);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->timestamps();
            $table->integer('dispatched_quantity')->default(0);
            $table->integer('received_quantity')->default(0);
        });
    }

    public function down(): void
    {
        // Intentionally left empty: table pre-dates this migration.
    }
};
