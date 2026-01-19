<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            // Store side fields
            if (!Schema::hasColumn('stock_requests', 'store_payment_proof')) {
                $table->string('store_payment_proof')->nullable()->after('status');
            }
            if (!Schema::hasColumn('stock_requests', 'store_remarks')) {
                $table->text('store_remarks')->nullable()->after('store_payment_proof');
            }

            // Warehouse side fields
            if (!Schema::hasColumn('stock_requests', 'warehouse_payment_proof')) {
                $table->string('warehouse_payment_proof')->nullable()->after('store_remarks');
            }
            if (!Schema::hasColumn('stock_requests', 'warehouse_remarks')) {
                $table->text('warehouse_remarks')->nullable()->after('warehouse_payment_proof');
            }
            
            if (!Schema::hasColumn('stock_requests', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('warehouse_remarks');
            }
            if (!Schema::hasColumn('stock_requests', 'purchase_ref')) {
                $table->string('purchase_ref')->nullable()->after('admin_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn([
                'store_payment_proof', 
                'store_remarks', 
                'warehouse_payment_proof', 
                'warehouse_remarks', 
                'verified_at', 
                'purchase_ref'
            ]);
        });
    }
};