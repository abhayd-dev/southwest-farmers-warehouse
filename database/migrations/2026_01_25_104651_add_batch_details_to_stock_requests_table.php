<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            // Stores JSON like: [{"batch": "B1", "qty": 10, "exp": "2026-01-01"}]
            $table->json('batch_details')->nullable()->after('purchase_ref');
        });
    }

    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn('batch_details');
        });
    }
};
