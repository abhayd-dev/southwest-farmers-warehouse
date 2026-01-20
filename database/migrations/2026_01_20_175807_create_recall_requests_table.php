<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recall_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('store_details')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('requested_quantity');
            $table->integer('approved_quantity')->default(0);
            $table->integer('dispatched_quantity')->default(0);
            $table->integer('received_quantity')->default(0);
            $table->enum('status', [
                'pending_store_approval',
                'approved_by_store',
                'partial_approved',
                'rejected_by_store',
                'dispatched',
                'received',
                'completed',
                'cancelled'
            ])->default('pending_store_approval');
            $table->enum('reason', ['near_expiry', 'quality_issue', 'overstock', 'damage', 'other']);
            $table->text('reason_remarks')->nullable();
            $table->text('store_remarks')->nullable();
            $table->text('warehouse_remarks')->nullable();
            $table->foreignId('initiated_by')->constrained('ware_users')->nullOnDelete();
            $table->foreignId('approved_by_store_user_id')->nullable()->constrained('store_users')->nullOnDelete();
            $table->foreignId('received_by_ware_user_id')->nullable()->constrained('ware_users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recall_requests');
        
    }
};