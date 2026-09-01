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
        Schema::create('kitchen_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ware_user_id');
            $table->foreignId('kitchen_location_id')->constrained('kitchen_locations')->cascadeOnDelete();
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('station')->default('Main Kitchen'); // Main Kitchen, Bakery, Soup Station, Sauce Prep, Packaging, etc.
            $table->string('status')->default('scheduled'); // scheduled, completed, absent, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_shifts');
    }
};
