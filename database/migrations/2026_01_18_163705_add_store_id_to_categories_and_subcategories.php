<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add store_id to Product Categories
        Schema::table('product_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('product_categories', 'store_id')) {
                $table->unsignedBigInteger('store_id')->default(1)->after('id')->index();
            }
        });

        // 2. Add store_id to Product Subcategories
        Schema::table('product_subcategories', function (Blueprint $table) {
            if (!Schema::hasColumn('product_subcategories', 'store_id')) {
                $table->unsignedBigInteger('store_id')->default(1)->after('id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });

        Schema::table('product_subcategories', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });
    }
};