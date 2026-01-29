<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            // Ensure column exists before modifying
            if (Schema::hasColumn('product_categories', 'store_id')) {
                // Make store_id nullable
                $table->integer('store_id')->nullable()->change();
            }
        });

        // Remove default value (PostgreSQL safe)
        DB::statement('ALTER TABLE product_categories ALTER COLUMN store_id DROP DEFAULT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            if (Schema::hasColumn('product_categories', 'store_id')) {
                // Restore default value if rollback
                $table->integer('store_id')->default(1)->change();
            }
        });

        DB::statement('ALTER TABLE product_categories ALTER COLUMN store_id SET DEFAULT 1');
    }
};
