<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ware_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Who receives it
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info'); // info, success, warning, danger
            $table->string('url')->nullable(); // Link to redirect (e.g., link to PO)
            $table->timestamp('read_at')->nullable(); // Null = Unread
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('ware_users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ware_notifications');
    }
};