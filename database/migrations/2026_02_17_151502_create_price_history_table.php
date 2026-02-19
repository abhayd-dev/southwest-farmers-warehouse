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
        Schema::create('price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('old_price', 15, 2);
            $table->decimal('new_price', 15, 2);
            $table->decimal('old_margin', 5, 2)->nullable();
            $table->decimal('new_margin', 5, 2)->nullable();
            $table->foreignId('changed_by')->constrained('ware_users');
            $table->timestamp('changed_at');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->text('reason')->nullable();
            $table->string('change_type')->default('manual'); // manual, promotion, bulk_update
            $table->timestamps();
            
            $table->index(['product_id', 'changed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_history');
    }
};
