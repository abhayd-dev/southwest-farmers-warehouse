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
        // Store Purchase Orders (PO-based, not item-based)
        Schema::create('store_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('store_id')->constrained('store_details')->onDelete('cascade');
            $table->date('request_date');
            $table->string('status')->default('pending'); // pending, approved, dispatched, completed, rejected
            $table->text('admin_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('store_users');
            $table->foreignId('approved_by')->nullable()->constrained('ware_users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Store Purchase Order Items
        Schema::create('store_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_po_id')->constrained('store_purchase_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('requested_qty');
            $table->integer('dispatched_qty')->default(0);
            $table->integer('pending_qty')->default(0);
            $table->string('status')->default('pending'); // pending, approved, dispatched, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_purchase_order_items');
        Schema::dropIfExists('store_purchase_orders');
    }
};
