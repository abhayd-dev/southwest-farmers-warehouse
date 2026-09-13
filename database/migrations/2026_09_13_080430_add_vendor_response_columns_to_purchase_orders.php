<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vendor acknowledge/deny response on a sent Purchase Order.
 * Deliberately separate from approval_status (internal approval workflow) —
 * this tracks the VENDOR's response after the PO has been sent to them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'vendor_response_status')) {
                $table->string('vendor_response_status')->nullable()->after('approval_reason');
            }
            if (!Schema::hasColumn('purchase_orders', 'vendor_response_at')) {
                $table->timestamp('vendor_response_at')->nullable()->after('vendor_response_status');
            }
            if (!Schema::hasColumn('purchase_orders', 'vendor_denial_reason')) {
                $table->text('vendor_denial_reason')->nullable()->after('vendor_response_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            foreach (['vendor_response_status', 'vendor_response_at', 'vendor_denial_reason'] as $col) {
                if (Schema::hasColumn('purchase_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
