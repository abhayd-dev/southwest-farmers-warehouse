<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create Purchase Orders Table (Parent)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('warehouse_id')->default(1)->constrained('ware_details');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            
            // Financials
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('other_costs', 15, 2)->default(0.00);
            
            // Status
            $table->string('status')->default('draft');
            $table->string('payment_status')->default('unpaid');
            
            // Approval Workflow
            $table->string('approval_email')->nullable();
            $table->string('approval_status')->default('pending'); // pending, approved, rejected
            $table->string('approved_by_email')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_reason')->nullable();
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->constrained('ware_users');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Create Purchase Order Items Table (Child)
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            // This creates the link to the table above
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            
            $table->integer('requested_quantity');
            $table->integer('received_quantity')->default(0);
            
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('total_cost', 15, 2);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};