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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('duties', 15, 2)->default(0)->after('other_costs');
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('duties');
            $table->decimal('taxes', 15, 2)->default(0)->after('shipping_cost');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('tax_percent', 'warehouse_markup_percentage');
            $table->renameColumn('retail_price', 'store_retail_price');
            $table->string('plu_code')->nullable()->after('store_retail_price'); // Added plu_code
            $table->decimal('store_markup_percentage', 15, 2)->default(0)->after('warehouse_markup_percentage');
            $table->decimal('manual_override_price', 15, 2)->nullable()->after('store_retail_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['duties', 'shipping_cost', 'taxes']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('warehouse_markup_percentage', 'tax_percent');
            $table->renameColumn('store_retail_price', 'retail_price');
            $table->dropColumn(['store_markup_percentage', 'manual_override_price']);
        });
    }
};
