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
        Schema::create('kitchen_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_location_id')->constrained('kitchen_locations')->cascadeOnDelete();
            $table->morphs('item'); // allows linking to MenuItem or Product
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit')->default('unit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_stocks');
    }
};
