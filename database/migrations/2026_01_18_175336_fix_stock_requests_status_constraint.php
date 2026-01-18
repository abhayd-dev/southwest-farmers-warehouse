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
        // 1. Drop the old strict constraint
        DB::statement("ALTER TABLE stock_requests DROP CONSTRAINT IF EXISTS stock_requests_status_check");

        // 2. Add a new constraint with ALL required statuses
        // We include: pending, approved, rejected, partial, dispatched, completed
        DB::statement("ALTER TABLE stock_requests ADD CONSTRAINT stock_requests_status_check 
            CHECK (status IN ('pending', 'approved', 'rejected', 'partial', 'dispatched', 'completed'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Revert to original (usually not needed)
        DB::statement("ALTER TABLE stock_requests DROP CONSTRAINT IF EXISTS stock_requests_status_check");
    }
};