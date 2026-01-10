<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // 1. Store Permissions Table
        Schema::create('store_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');       // e.g., 'edit_orders', 'view_revenue'
            $table->string('guard_name'); // e.g., 'store_web'
            $table->timestamps();
        });

        // 2. Store Roles Table
        Schema::create('store_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');       // e.g., 'Store Manager', 'Cashier'
            $table->string('guard_name'); // e.g., 'store_web'
            $table->timestamps();
        });

        // 3. Mapping: Role -> Permissions
        Schema::create('store_role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                  ->references('id')
                  ->on('store_permissions')
                  ->onDelete('cascade');

            $table->foreign('role_id')
                  ->references('id')
                  ->on('store_roles')
                  ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id'], 'store_role_perm_primary');
        });

        // 4. Mapping: User (Model) -> Roles
        Schema::create('store_model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type'); // e.g., 'App\Models\StoreUser'
            $table->unsignedBigInteger('model_id'); // store_users.id

            $table->index(['model_id', 'model_type']);

            $table->foreign('role_id')
                  ->references('id')
                  ->on('store_roles')
                  ->onDelete('cascade');
            
            // Primary key to prevent duplicate role assignments to same user
            $table->primary(['role_id', 'model_id', 'model_type'], 'store_model_role_primary');
        });

        // 5. Mapping: User (Model) -> Direct Permissions
        Schema::create('store_model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->index(['model_id', 'model_type']);

            $table->foreign('permission_id')
                  ->references('id')
                  ->on('store_permissions')
                  ->onDelete('cascade');

            // Primary key
            $table->primary(['permission_id', 'model_id', 'model_type'], 'store_model_perm_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_model_has_permissions');
        Schema::dropIfExists('store_model_has_roles');
        Schema::dropIfExists('store_role_has_permissions');
        Schema::dropIfExists('store_roles');
        Schema::dropIfExists('store_permissions');
    }
};