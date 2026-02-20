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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('promotion_price', 15, 2)->nullable()->after('price');
            $table->dateTime('promotion_start_date')->nullable()->after('promotion_price');
            $table->dateTime('promotion_end_date')->nullable()->after('promotion_start_date');
        });

        Schema::table('product_market_prices', function (Blueprint $table) {
            $table->decimal('promotion_price', 15, 2)->nullable()->after('sale_price');
            $table->dateTime('promotion_start_date')->nullable()->after('promotion_price');
            $table->dateTime('promotion_end_date')->nullable()->after('promotion_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['promotion_price', 'promotion_start_date', 'promotion_end_date']);
        });

        Schema::table('product_market_prices', function (Blueprint $table) {
            $table->dropColumn(['promotion_price', 'promotion_start_date', 'promotion_end_date']);
        });
    }
};
