<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('store_stocks', function (Blueprint $table) {
            $table->integer('min_stock')->default(0)->after('quantity'); // Trigger Point
            $table->integer('max_stock')->default(0)->after('min_stock'); // Order Quantity (e.g., 6, 12)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('store_stocks', function (Blueprint $table) {
            $table->dropColumn(['min_stock', 'max_stock']);
        });
    }
};
