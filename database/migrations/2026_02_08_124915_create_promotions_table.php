<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('store_details')->onDelete('cascade');
            $table->string('name'); // e.g., "Summer Sale"
            $table->string('code')->nullable(); // e.g., "SUMMER20" (optional)
            $table->enum('type', ['percentage', 'fixed_amount', 'bogo']); // Logic type
            $table->decimal('value', 10, 2)->default(0); // e.g., 10.00 (for 10% or $10)
            
            // Scope: Can apply to a specific Product OR a whole Category
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('cascade');
            
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotions');
    }
};