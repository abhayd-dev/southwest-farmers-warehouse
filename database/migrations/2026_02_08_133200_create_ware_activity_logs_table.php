<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ware_activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Who did it?
            $table->unsignedBigInteger('causer_id')->nullable(); // User ID
            $table->string('causer_type')->nullable(); // User Model (WareUser)

            // What changed?
            $table->string('subject_type')->nullable(); // e.g., App\Models\Product
            $table->unsignedBigInteger('subject_id')->nullable(); // Product ID

            // Action Details
            $table->string('action'); // created, updated, deleted, login, custom
            $table->text('description')->nullable(); // "Updated Price for iPhone"
            
            // Data Changes (Old vs New)
            $table->json('properties')->nullable(); // Stores {old: [], new: []}
            
            // Context
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            
            // Indexes for faster searching
            $table->index('causer_id');
            $table->index('subject_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ware_activity_logs');
    }
};