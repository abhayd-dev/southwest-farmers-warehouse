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
    Schema::create('stock_requests', function (Blueprint $table) {
        $table->id();
        // Link to the store making the request
        $table->foreignId('store_id')->constrained('store_details')->cascadeOnDelete();
        // Link to the product being requested
        $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
        
        $table->integer('requested_quantity');
        
        // Status of the request
        $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
        
        // Optional: Admin note for rejection/approval
        $table->text('admin_note')->nullable();
        
        $table->timestamps();
    });
}
};