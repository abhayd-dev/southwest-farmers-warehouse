<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Store-side "Draft" button on a new unscheduled Store Request (client PDF
 * 9/22, Store item 3). A draft stays on the store until it is reviewed; the
 * warehouse queue only reads pending / awaiting_approval.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE stock_requests DROP CONSTRAINT IF EXISTS stock_requests_status_check");
            DB::statement("ALTER TABLE stock_requests ADD CONSTRAINT stock_requests_status_check
                CHECK (status IN ('draft', 'pending', 'awaiting_approval', 'approved', 'rejected', 'partial', 'dispatched', 'completed'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE stock_requests SET status = 'pending' WHERE status = 'draft'");
            DB::statement("ALTER TABLE stock_requests DROP CONSTRAINT IF EXISTS stock_requests_status_check");
            DB::statement("ALTER TABLE stock_requests ADD CONSTRAINT stock_requests_status_check
                CHECK (status IN ('pending', 'awaiting_approval', 'approved', 'rejected', 'partial', 'dispatched', 'completed'))");
        }
    }
};
