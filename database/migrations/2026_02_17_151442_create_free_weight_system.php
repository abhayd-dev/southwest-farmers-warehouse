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
        // Table for bulk weight products (e.g., 5000 lbs of lentils)
        Schema::create('free_weight_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('ware_details');
            $table->decimal('bulk_weight', 15, 2); // Total bulk weight available
            $table->string('unit')->default('lbs'); // lbs or kg
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table for package sizes (e.g., 10 lb bags, 20 lb bags)
        Schema::create('free_weight_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('free_weight_product_id')->constrained('free_weight_products')->onDelete('cascade');
            $table->string('package_name'); // e.g., "10 lb bag"
            $table->decimal('package_size', 10, 2); // e.g., 10
            $table->string('unit')->default('lbs');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->integer('quantity_created')->default(0); // Total bags created
            $table->timestamps();
        });

        // Table for packaging events (history of packaging operations)
        Schema::create('packaging_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('free_weight_product_id')->constrained('free_weight_products');
            $table->foreignId('package_id')->constrained('free_weight_packages');
            $table->foreignId('employee_id')->constrained('ware_users');
            $table->decimal('bulk_weight_reduced', 15, 2); // How much bulk was used
            $table->integer('packages_created'); // How many bags were created
            $table->timestamp('event_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packaging_events');
        Schema::dropIfExists('free_weight_packages');
        Schema::dropIfExists('free_weight_products');
    }
};
