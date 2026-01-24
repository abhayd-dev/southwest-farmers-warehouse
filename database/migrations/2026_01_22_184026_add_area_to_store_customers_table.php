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
        Schema::table('store_customers', function (Blueprint $table) {
            $table->string('area')->nullable()->after('address')->index(); // Adding Area
        });
    }

    public function down()
    {
        Schema::table('store_customers', function (Blueprint $table) {
            $table->dropColumn('area');
        });
    }
};
