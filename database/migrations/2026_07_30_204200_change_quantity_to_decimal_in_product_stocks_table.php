<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE product_stocks ALTER COLUMN quantity TYPE NUMERIC(15,2) USING quantity::numeric');
            DB::statement('ALTER TABLE product_stocks ALTER COLUMN reserved_quantity TYPE NUMERIC(15,2) USING reserved_quantity::numeric');
            DB::statement('ALTER TABLE product_stocks ALTER COLUMN damaged_quantity TYPE NUMERIC(15,2) USING damaged_quantity::numeric');

            DB::statement('ALTER TABLE store_stocks ALTER COLUMN quantity TYPE NUMERIC(15,2) USING quantity::numeric');
            DB::statement('ALTER TABLE store_stocks ALTER COLUMN reserved_quantity TYPE NUMERIC(15,2) USING reserved_quantity::numeric');
        } else {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->decimal('quantity', 15, 2)->default(0)->change();
                $table->decimal('reserved_quantity', 15, 2)->default(0)->change();
                $table->decimal('damaged_quantity', 15, 2)->default(0)->change();
            });

            Schema::table('store_stocks', function (Blueprint $table) {
                $table->decimal('quantity', 15, 2)->default(0)->change();
                $table->decimal('reserved_quantity', 15, 2)->default(0)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed for rollback
    }
};
