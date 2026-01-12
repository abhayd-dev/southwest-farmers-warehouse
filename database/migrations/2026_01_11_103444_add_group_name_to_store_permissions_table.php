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
        Schema::table('store_permissions', function (Blueprint $table) {
            // Adding group_name column after guard_name
            $table->string('group_name')->nullable()->after('guard_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_permissions', function (Blueprint $table) {
            $table->dropColumn('group_name');
        });
    }
};