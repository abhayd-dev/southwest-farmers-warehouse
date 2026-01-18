<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_users', function (Blueprint $table) {
            // Store ID add kar rahe hain taaki pata chale ye staff kis store ka hai
            // Nullable rakha hai taaki agar koi user abhi assign na ho toh error na aaye
            $table->foreignId('store_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('store_details')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('store_users', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};