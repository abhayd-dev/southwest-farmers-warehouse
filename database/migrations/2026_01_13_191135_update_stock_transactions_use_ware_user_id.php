<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fixing Stock Transactions Table
        Schema::table('stock_transactions', function (Blueprint $table) {
            // Agar user_id exist karta hai to usko rename karein, nahi to naya banayein
            if (Schema::hasColumn('stock_transactions', 'user_id')) {
                $table->renameColumn('user_id', 'ware_user_id');
            } else {
                if (!Schema::hasColumn('stock_transactions', 'ware_user_id')) {
                    $table->foreignId('ware_user_id')->nullable()->constrained('ware_users')->nullOnDelete();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('stock_transactions', 'ware_user_id')) {
                $table->renameColumn('ware_user_id', 'user_id');
            }
        });
    }
};