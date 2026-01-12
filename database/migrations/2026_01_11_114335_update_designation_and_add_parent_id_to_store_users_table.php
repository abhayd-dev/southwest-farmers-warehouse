<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Needed for raw SQL if data exists

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('store_users', function (Blueprint $table) {
            // 1. Add parent_id (Self-referencing Foreign Key for hierarchy)
            // Placed after 'id' or 'store_id' for better readability
            $table->unsignedBigInteger('parent_id')->nullable()->after('email'); 
            
            // Optional: Add Foreign Key Constraint (Connects to store_users.id)
            $table->foreign('parent_id')->references('id')->on('store_users')->onDelete('set null');
        });

        // 2. Change designation to Integer (to store role_id)
        // If you are using PostgreSQL and have existing data, simple ->change() might fail.
        // We use a raw statement to handle the type casting safely.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE store_users ALTER COLUMN designation TYPE BIGINT USING (designation::bigint)');
        } else {
            // For MySQL/others
            Schema::table('store_users', function (Blueprint $table) {
                $table->unsignedBigInteger('designation')->nullable()->change();
            });
        }
        
        // Optional: If you want to rename 'designation' to 'role_id' for clarity, uncomment below:
        // Schema::table('store_users', function (Blueprint $table) {
        //     $table->renameColumn('designation', 'role_id');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_users', function (Blueprint $table) {
            // 1. Drop Foreign Key first
            $table->dropForeign(['parent_id']);
            
            // 2. Drop Column
            $table->dropColumn('parent_id');
        });

        // 3. Revert designation to String
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE store_users ALTER COLUMN designation TYPE VARCHAR(255)');
        } else {
            Schema::table('store_users', function (Blueprint $table) {
                $table->string('designation', 255)->nullable()->change();
            });
        }
    }
};