<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            // Make warehouse_id nullable
            if (Schema::hasColumn('stock_transactions', 'warehouse_id')) {
                $table->foreignId('warehouse_id')
                    ->nullable()
                    ->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            // Revert warehouse_id to NOT NULL
            if (Schema::hasColumn('stock_transactions', 'warehouse_id')) {
                $table->foreignId('warehouse_id')
                    ->nullable(false)
                    ->change();
            }
        });
    }
};
