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
        // Markets table (Houston, Dallas, etc.)
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Houston", "Dallas"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pivot table: stores belong to markets
        Schema::create('store_markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('store_details')->onDelete('cascade');
            $table->foreignId('market_id')->constrained('markets')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['store_id', 'market_id']);
        });

        // Market-level pricing (applies to all stores in a market)
        Schema::create('product_market_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('market_id')->constrained('markets')->onDelete('cascade');
            $table->decimal('cost_price', 15, 2);
            $table->decimal('sale_price', 15, 2);
            $table->decimal('margin_percent', 5, 2);
            $table->timestamps();
            
            $table->unique(['product_id', 'market_id']);
        });

        // Store-specific pricing (overrides market pricing)
        Schema::create('store_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('store_details')->onDelete('cascade');
            $table->decimal('cost_price', 15, 2);
            $table->decimal('sale_price', 15, 2);
            $table->decimal('margin_percent', 5, 2);
            $table->timestamps();
            
            $table->unique(['product_id', 'store_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_product_prices');
        Schema::dropIfExists('product_market_prices');
        Schema::dropIfExists('store_markets');
        Schema::dropIfExists('markets');
    }
};
