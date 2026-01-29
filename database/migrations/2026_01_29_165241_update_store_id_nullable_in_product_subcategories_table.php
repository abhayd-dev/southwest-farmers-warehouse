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
        Schema::table('product_subcategories', function (Blueprint $table) {
            // Safety check
            if (Schema::hasColumn('product_subcategories', 'store_id')) {
                // Make store_id nullable
                $table->integer('store_id')->nullable()->change();
            }
        });

        // PostgreSQL: remove default value
        DB::statement(
            'ALTER TABLE product_subcategories ALTER COLUMN store_id DROP DEFAULT'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_subcategories', function (Blueprint $table) {
            if (Schema::hasColumn('product_subcategories', 'store_id')) {
                // Restore old behavior on rollback
                $table->integer('store_id')->default(1)->change();
            }
        });

        DB::statement(
            'ALTER TABLE product_subcategories ALTER COLUMN store_id SET DEFAULT 1'
        );
    }
};
