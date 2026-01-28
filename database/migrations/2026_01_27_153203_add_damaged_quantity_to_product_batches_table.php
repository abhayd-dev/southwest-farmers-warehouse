<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_batches', function (Blueprint $table) {
            // Decimal column add kar rahe hain, default 0 ke saath
            // 'quantity' column ke baad add hoga
            $table->decimal('damaged_quantity', 10, 2)->default(0)->after('quantity');
        });
    }

    public function down()
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropColumn('damaged_quantity');
        });
    }
};