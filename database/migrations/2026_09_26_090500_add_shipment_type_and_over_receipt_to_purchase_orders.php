<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client PDF 9/24, Warehouse items 1-3:
 * - shipment_type (truck / container) decides what a short receipt does:
 *   container stays open ("In Transit"), truck closes the order.
 * - Receiving more than was ordered flags the PO for approval.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'shipment_type')) {
                $table->string('shipment_type', 20)->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'over_receipt_status')) {
                $table->string('over_receipt_status', 20)->nullable(); // pending | approved | rejected
                $table->json('over_receipt_lines')->nullable();        // [{item_id, product, ordered, received, unit_cost}]
                $table->string('over_receipt_decided_by')->nullable();
                $table->timestamp('over_receipt_decided_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            foreach (['shipment_type', 'over_receipt_status', 'over_receipt_lines', 'over_receipt_decided_by', 'over_receipt_decided_at'] as $col) {
                if (Schema::hasColumn('purchase_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
