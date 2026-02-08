<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->id();
            // The Main Item (e.g., Burger)
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // The Ingredient (e.g., Bun) - References 'products' table too
            $table->foreignId('ingredient_id')->constrained('products')->onDelete('cascade');
            
            // Quantity needed (e.g., 1.00 for 1 Bun, 0.200 for 200g Meat)
            $table->decimal('quantity', 10, 3); 
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_ingredients');
    }
};