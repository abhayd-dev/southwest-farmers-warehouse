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
        // 2. Audit Items (Line Items)
        Schema::create('stock_audit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_audit_id')->constrained('stock_audits')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');

            $table->decimal('system_qty', 10, 2); // Qty in DB at start of audit
            $table->decimal('physical_qty', 10, 2)->nullable(); // Qty entered by user
            $table->decimal('variance_qty', 10, 2)->default(0); // Difference
            $table->decimal('cost_price', 10, 2); // Snapshot of cost

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_audit_items');
    }
};
