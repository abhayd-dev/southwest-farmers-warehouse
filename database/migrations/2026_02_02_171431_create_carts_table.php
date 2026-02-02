<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // To link with logged-in user
            $table->unsignedBigInteger('customer_id')->nullable(); // Optional: link to a customer
            $table->string('status')->default('active'); // active, held, completed
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('store_details')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('store_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
