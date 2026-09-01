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
        Schema::create('kitchen_time_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ware_user_id');
            $table->foreignId('kitchen_location_id')->nullable()->constrained('kitchen_locations')->nullOnDelete();
            $table->timestamp('clock_in_at');
            $table->timestamp('clock_out_at')->nullable();
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->integer('break_minutes')->default(0);
            $table->string('status')->default('clocked_in'); // clocked_in, on_break, clocked_out
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_time_logs');
    }
};
