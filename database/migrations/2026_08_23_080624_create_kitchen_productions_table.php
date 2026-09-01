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
        Schema::create('kitchen_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->foreignId('kitchen_location_id')->constrained('kitchen_locations')->cascadeOnDelete();
            $table->unsignedBigInteger('ware_user_id')->nullable(); // Employee who made it
            $table->decimal('quantity_made', 8, 2);
            $table->string('quantity_unit')->default('batch');
            $table->decimal('yield_plates', 8, 2)->nullable(); // How many plates came out
            $table->integer('daily_target')->default(0);
            $table->timestamp('produced_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_productions');
    }
};
