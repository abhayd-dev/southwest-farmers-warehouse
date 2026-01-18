<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_users', function (Blueprint $table) {
            // Drop the old string column
            if (Schema::hasColumn('store_users', 'designation')) {
                $table->dropColumn('designation');
            }
            
            // Add the new Foreign Key column
            if (!Schema::hasColumn('store_users', 'store_role_id')) {
                $table->unsignedBigInteger('store_role_id')->nullable()->after('store_id');
                $table->foreign('store_role_id')->references('id')->on('store_roles')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('store_users', function (Blueprint $table) {
            $table->dropForeign(['store_role_id']);
            $table->dropColumn('store_role_id');
            $table->string('designation')->nullable();
        });
    }
};