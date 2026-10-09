<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client ticket 23: returnable pallet/divider inventory per vendor --
 * received alongside a PO's goods, sent back to the vendor independently,
 * and reconciled. vendor_container_balances holds the current running
 * count per vendor+type; vendor_container_transactions is the audit trail
 * every balance change is derived from (received / returned / adjustment).
 */
return new class extends Migration
{
    public function up(): void
    {
        // vendors.id has no primary key / unique constraint in this database
        // (pre-existing -- see purchase_orders.vendor_id, also unconstrained
        // in practice despite the model declaring the relation), so these
        // reference it as plain columns rather than a DB-level foreign key.
        if (!Schema::hasTable('vendor_container_balances')) {
            Schema::create('vendor_container_balances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id');
                $table->decimal('pallet_count', 15, 2)->default(0);
                $table->decimal('divider_count', 15, 2)->default(0);
                $table->timestamps();
                $table->unique('vendor_id');
            });
        }

        if (!Schema::hasTable('vendor_container_transactions')) {
            Schema::create('vendor_container_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id');
                $table->string('container_type', 20); // pallet, divider
                $table->string('direction', 20); // received, returned, adjustment
                $table->decimal('quantity_change', 15, 2); // signed
                $table->decimal('running_balance', 15, 2);
                $table->unsignedBigInteger('purchase_order_id')->nullable();
                $table->unsignedBigInteger('ware_user_id')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->index(['vendor_id', 'container_type', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_container_transactions');
        Schema::dropIfExists('vendor_container_balances');
    }
};
