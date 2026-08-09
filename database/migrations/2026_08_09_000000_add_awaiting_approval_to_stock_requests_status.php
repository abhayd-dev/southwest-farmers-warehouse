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
            DB::statement("ALTER TABLE stock_requests DROP CONSTRAINT IF EXISTS stock_requests_status_check");
            DB::statement("ALTER TABLE stock_requests ADD CONSTRAINT stock_requests_status_check 
                CHECK (status IN ('pending', 'awaiting_approval', 'approved', 'rejected', 'partial', 'dispatched', 'completed'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE stock_requests DROP CONSTRAINT IF EXISTS stock_requests_status_check");
        }
    }
};
