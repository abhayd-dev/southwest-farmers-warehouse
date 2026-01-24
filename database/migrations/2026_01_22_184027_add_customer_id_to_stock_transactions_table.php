<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            // Nullable because not all transactions are sales (e.g. Purchase In, Transfer)
            $table->foreignId('customer_id')->nullable()->constrained('store_customers')->after('store_id');
        });
    }

    public function down()
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};
