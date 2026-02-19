<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pallets', function (Blueprint $table) {
            // Make transfer_id nullable (pallets can be for Store POs, not just stock transfers)
            $table->unsignedBigInteger('store_po_id')->nullable()->after('transfer_id');
            $table->foreign('store_po_id')->references('id')->on('store_purchase_orders')->nullOnDelete();
        });

        // Also make transfer_id nullable if not already
        Schema::table('pallets', function (Blueprint $table) {
            $table->unsignedBigInteger('transfer_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pallets', function (Blueprint $table) {
            $table->dropForeign(['store_po_id']);
            $table->dropColumn('store_po_id');
        });
    }
};
