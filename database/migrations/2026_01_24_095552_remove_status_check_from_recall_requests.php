<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // PostgreSQL mein Check Constraint hataane ke liye raw SQL query
        DB::statement("ALTER TABLE recall_requests DROP CONSTRAINT IF EXISTS recall_requests_status_check");
    }

    public function down()
    {
        // Wapas lagana mushkil hai bina purane data ko fix kiye, isliye ise khali chhod sakte hain
    }
};