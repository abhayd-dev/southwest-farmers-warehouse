<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('store_details', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('warehouse_id')
                ->constrained('ware_details')
                ->cascadeOnDelete();

            $table->foreignId('store_user_id')
                ->nullable()
                ->constrained('store_users')
                ->nullOnDelete();

            // Store Info
            $table->string('store_name');
            $table->string('store_code')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();

            // Address
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('pincode', 20)->nullable();

            // Geo
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_details');
    }
};
