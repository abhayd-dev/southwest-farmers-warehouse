<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Adding ware_user_id
            if (!Schema::hasColumn('products', 'ware_user_id')) {
                $table->foreignId('ware_user_id')
                      ->nullable()
                      ->default(1) // Future tracking ke liye default 1 (Admin/System)
                      ->after('id')
                      ->constrained('ware_users')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'ware_user_id')) {
                $table->dropForeign(['ware_user_id']);
                $table->dropColumn('ware_user_id');
            }
        });
    }
};