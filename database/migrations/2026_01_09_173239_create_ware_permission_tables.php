<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('ware_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('ware_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('ware_role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')->references('id')->on('ware_permissions')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('ware_roles')->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('ware_model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->index(['model_id', 'model_type']);

            $table->foreign('role_id')->references('id')->on('ware_roles')->onDelete('cascade');
        });

        Schema::create('ware_model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->index(['model_id', 'model_type']);

            $table->foreign('permission_id')->references('id')->on('ware_permissions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ware_model_has_permissions');
        Schema::dropIfExists('ware_model_has_roles');
        Schema::dropIfExists('ware_role_has_permissions');
        Schema::dropIfExists('ware_roles');
        Schema::dropIfExists('ware_permissions');
    }
};

