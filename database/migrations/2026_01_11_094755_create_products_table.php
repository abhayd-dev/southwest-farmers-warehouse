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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_option_id')
                ->nullable()
                ->constrained('product_options')
                ->nullOnDelete();

            $table->foreignId('category_id')
                ->constrained('product_categories');

            $table->foreignId('subcategory_id')
                ->constrained('product_subcategories');

            $table->string('product_name');
            $table->string('sku')->nullable()->index();
            $table->string('barcode')->nullable()->index();
            $table->string('unit');

            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('price', 12, 2);
            $table->decimal('cost_price', 12, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
