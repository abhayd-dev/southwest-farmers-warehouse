<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ware_settings', function (Blueprint $table) {
            // Add the missing description column
            if (!Schema::hasColumn('ware_settings', 'description')) {
                $table->string('description')->nullable()->after('value');
            }
        });
    }

    public function down()
    {
        Schema::table('ware_settings', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};