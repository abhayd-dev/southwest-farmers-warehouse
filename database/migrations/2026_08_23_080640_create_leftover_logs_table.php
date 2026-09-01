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
        Schema::create('leftover_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->foreignId('kitchen_location_id')->constrained('kitchen_locations')->cascadeOnDelete();
            $table->decimal('quantity_produced', 8, 2);
            $table->decimal('quantity_sold', 8, 2)->default(0);
            $table->decimal('quantity_leftover', 8, 2)->default(0);
            $table->date('log_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leftover_logs');
    }
};
