<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_audits', function (Blueprint $table) {
            $table->foreignId('department_id')
                  ->nullable()
                  ->after('warehouse_id')
                  ->constrained('departments')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('stock_audits', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};