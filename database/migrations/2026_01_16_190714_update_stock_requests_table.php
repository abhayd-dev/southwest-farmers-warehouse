<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            // Kitna maal bhej diya gaya hai (Partial tracking ke liye)
            $table->integer('fulfilled_quantity')->default(0)->after('requested_quantity');
            
        });
    }

    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn('fulfilled_quantity');
        });
    }
};