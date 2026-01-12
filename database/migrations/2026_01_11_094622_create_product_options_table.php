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
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('product_categories');

            $table->foreignId('subcategory_id')
                ->constrained('product_subcategories');

            $table->string('option_name');
            $table->string('sku')->nullable()->index();
            $table->string('barcode')->nullable()->index();

            $table->string('unit'); // kg, litre, pcs
            $table->decimal('tax_percent', 5, 2)->default(0);

            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->decimal('mrp', 12, 2)->nullable();

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_options');
    }
};
