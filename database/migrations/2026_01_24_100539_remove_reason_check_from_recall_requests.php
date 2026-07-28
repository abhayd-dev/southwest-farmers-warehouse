<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE recall_requests DROP CONSTRAINT IF EXISTS recall_requests_reason_check");
            DB::statement("ALTER TABLE recall_requests DROP CONSTRAINT IF EXISTS recall_requests_status_check");
        }
    }

    public function down()
    {
        // Reverting this is not necessary for now
    }
};