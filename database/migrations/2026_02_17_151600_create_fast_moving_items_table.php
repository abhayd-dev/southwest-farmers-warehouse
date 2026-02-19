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
        Schema::create('fast_moving_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->onDelete('cascade');
            $table->decimal('sales_velocity', 10, 2)->default(0); // units/day or lbs/day
            $table->integer('stockout_frequency')->default(0); // number of times out of stock
            $table->integer('dispatch_volume')->default(0); // total units/lbs dispatched in period
            $table->boolean('is_fast_moving')->default(false);
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            $table->index(['is_fast_moving', 'sales_velocity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fast_moving_items');
    }
};
