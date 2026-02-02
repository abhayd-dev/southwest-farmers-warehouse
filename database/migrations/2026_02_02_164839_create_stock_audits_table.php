<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Audit Sessions
        Schema::create('stock_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_number')->unique(); // AUD-WH-2026-001
            
            // Location (Polymorphic style or specific columns)
            // Abhi hum specific columns rakhenge clarity ke liye
            $table->unsignedBigInteger('warehouse_id')->nullable(); 
            $table->unsignedBigInteger('store_id')->nullable(); // For Dev 2 later
            
            $table->string('type')->default('full'); // full, category_wise, partial
            $table->string('status')->default('draft'); // draft, in_progress, completed, adjusted
            
            $table->foreignId('initiated_by')->constrained('ware_users'); // Who started it
            $table->timestamp('completed_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('stock_audits');
    }
};