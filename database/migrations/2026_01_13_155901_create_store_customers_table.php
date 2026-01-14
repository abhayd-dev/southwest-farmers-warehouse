<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->index(); // Link to store
            
            // Fields from your reference image
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('party_type')->default('Retail'); // Select Option
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('due', 10, 2)->default(0.00); // Due Amount
            $table->string('image')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_customers');
    }
};