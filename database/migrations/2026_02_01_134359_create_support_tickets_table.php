<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Support Tickets
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // e.g., TKT-20260201-001
            
            // Created By (Polymorphic: StoreUser or WareUser)
            $table->unsignedBigInteger('created_by_id');
            $table->string('created_by_type');

            // Assigned To (Warehouse Staff)
            $table->unsignedBigInteger('assigned_to_id')->nullable(); 

            // Context
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();

            // Details
            $table->string('category'); // Technical, Stock, Logistics, etc.
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->enum('status', ['open', 'in_progress', 'waiting', 'resolved', 'closed'])->default('open');

            // Reference (Polymorphic: PO, Request, Order)
            $table->nullableMorphs('reference'); 

            // Timestamps & SLA
            $table->timestamp('sla_due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

    }

    public function down()
    {
        Schema::dropIfExists('support_tickets');
    }
};