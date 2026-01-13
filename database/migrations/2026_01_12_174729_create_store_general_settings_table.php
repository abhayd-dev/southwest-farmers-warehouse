<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->index(); 
            
            // Fields matching your image
            $table->string('app_name')->nullable();
            $table->string('app_phone')->nullable();
            $table->string('support_email')->nullable();
            $table->text('address')->nullable();
            
            // Branding Paths
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('login_logo')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};