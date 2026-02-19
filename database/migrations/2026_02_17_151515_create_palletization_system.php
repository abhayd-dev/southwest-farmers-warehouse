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
        // Pallets table
        Schema::create('pallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('stock_transfers')->onDelete('cascade');
            $table->string('pallet_number')->unique();
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->decimal('total_weight', 10, 2)->default(0);
            $table->decimal('max_weight', 10, 2)->default(2200); // 2200 lbs max
            $table->string('status')->default('preparing'); // preparing, ready, in_transit, delivered
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Pallet items (products on each pallet)
        Schema::create('pallet_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pallet_id')->constrained('pallets')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
            $table->decimal('weight_per_unit', 10, 2); // Weight of each unit
            $table->decimal('total_weight', 10, 2); // quantity * weight_per_unit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pallet_items');
        Schema::dropIfExists('pallets');
    }
};
