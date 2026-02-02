<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique(); // TRF-2026001
            
            // Source & Target
            $table->foreignId('from_store_id')->constrained('store_details')->cascadeOnDelete();
            $table->foreignId('to_store_id')->constrained('store_details')->cascadeOnDelete();
            
            // Product Info
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity_sent');
            $table->integer('quantity_received')->default(0);
            
            // Status Workflow
            $table->string('status')->default('pending'); // pending, approved, dispatched, completed, rejected
            
            // Audit
            $table->foreignId('created_by')->constrained('store_users'); // Who requested/initiated
            $table->foreignId('approved_by')->nullable()->constrained('store_users'); // Sender Store Manager
            $table->foreignId('received_by')->nullable()->constrained('store_users'); // Receiver Store Staff
            
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transfers');
    }
};