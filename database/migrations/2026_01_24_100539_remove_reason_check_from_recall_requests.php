<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 'reason' column se check constraint hatane ke liye
        DB::statement("ALTER TABLE recall_requests DROP CONSTRAINT IF EXISTS recall_requests_reason_check");
        
        // Agar 'status' wala pehle nahi hataya tha, toh wo bhi hata dein (Safety ke liye)
        DB::statement("ALTER TABLE recall_requests DROP CONSTRAINT IF EXISTS recall_requests_status_check");
    }

    public function down()
    {
        // Reverting this is not necessary for now
    }
};