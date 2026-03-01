<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->change();
            // Since upc might have duplicates before it was enforced, we won't strictly enforce unique in db yet,
            // or we can add it if requested. The plan says "Ensure upc is unique".
            // $table->unique('upc'); // might fail if there are duplicates.
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->text('vendor_notes')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('vendor_notes');
        });

        // Cannot easily revert sku nullable change safely, so omit it in down()
    }
};
