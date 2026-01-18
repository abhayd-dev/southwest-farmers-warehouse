<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // This drops the strict check constraint on the 'type' column
        // allowing 'sale_out' and other new types to be saved.
        DB::statement("ALTER TABLE stock_transactions DROP CONSTRAINT IF EXISTS stock_transactions_type_check");
    }

    public function down(): void
    {
        // Optional: Restore constraint if needed (usually not required for this fix)
    }
};