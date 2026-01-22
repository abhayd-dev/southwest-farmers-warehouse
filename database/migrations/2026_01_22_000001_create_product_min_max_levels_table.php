<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_min_max_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()->unique();
            $table->integer('min_level')->default(5);
            $table->integer('max_level')->default(100);
            $table->integer('reorder_quantity')->default(20);
            $table->foreignId('updated_by')->nullable()->constrained('ware_users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_min_max_levels');
    }
};