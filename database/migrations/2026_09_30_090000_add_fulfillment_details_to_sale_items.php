<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The store POS and website checkout record which batches each sold line came
 * from ([{type, batch_id, qty}]), but the column was never created, so every
 * POS sale failed at "insert into sale_items".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sale_items', 'fulfillment_details')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->json('fulfillment_details')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sale_items', 'fulfillment_details')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropColumn('fulfillment_details');
            });
        }
    }
};
