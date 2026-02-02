<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->default(5.00); // 1.00 to 5.00
            $table->decimal('on_time_delivery_rate', 5, 2)->default(100.00); // Percentage
            $table->integer('total_orders_count')->default(0);
        });
    }

    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['rating', 'on_time_delivery_rate', 'total_orders_count']);
        });
    }
};