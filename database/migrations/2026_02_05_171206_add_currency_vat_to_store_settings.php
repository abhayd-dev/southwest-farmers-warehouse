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
    Schema::table('store_settings', function (Blueprint $table) {
        $table->string('currency')->default('$')->nullable()->after('address');
        $table->decimal('vat_percentage', 5, 2)->default(0.00)->nullable()->after('currency');
    });
}

public function down()
{
    Schema::table('store_settings', function (Blueprint $table) {
        $table->dropColumn(['currency', 'vat_percentage']);
    });
}
};
