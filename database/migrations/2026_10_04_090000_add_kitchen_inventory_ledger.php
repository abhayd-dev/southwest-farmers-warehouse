<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kitchen spec (contract Exhibit A B.4, spec section 6): kitchen inventory
 * kept separately, with receipts, usage, adjustments, waste and an audit
 * ledger showing how each quantity was reached.
 *
 * - kitchen_locations.store_id: the store app gives every store its own
 *   kitchen, but the column only existed in a store-repo migration (which
 *   never runs on deploy). Same guarded definition, now where migrations run.
 * - kitchen_stocks: min_quantity (low-stock status) and reserved_quantity
 *   (Available = On Hand - Reserved).
 * - kitchen_stock_transactions: one row per movement, never edited.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitchen_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('kitchen_locations', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable()->after('id');
            }
        });

        Schema::table('kitchen_stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('kitchen_stocks', 'min_quantity')) {
                $table->decimal('min_quantity', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('kitchen_stocks', 'reserved_quantity')) {
                $table->decimal('reserved_quantity', 12, 2)->default(0);
            }
        });

        if (!Schema::hasTable('kitchen_stock_transactions')) {
            Schema::create('kitchen_stock_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kitchen_location_id')->constrained('kitchen_locations')->cascadeOnDelete();
                $table->foreignId('kitchen_stock_id')->constrained('kitchen_stocks')->cascadeOnDelete();
                // transfer_in, receive, use, adjust_in, adjust_out, waste, count
                $table->string('type', 30);
                $table->decimal('quantity_change', 12, 2); // + in, - out
                $table->decimal('balance_after', 12, 2);
                $table->string('reason')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('user_id')->nullable(); // store_users.id
                $table->string('user_name')->nullable();           // kept even if the user is deleted
                $table->timestamp('created_at')->useCurrent();

                $table->index(['kitchen_location_id', 'created_at']);
                $table->index('kitchen_stock_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_stock_transactions');

        Schema::table('kitchen_stocks', function (Blueprint $table) {
            foreach (['min_quantity', 'reserved_quantity'] as $col) {
                if (Schema::hasColumn('kitchen_stocks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        // kitchen_locations.store_id is left in place: the store app relies on it.
    }
};
