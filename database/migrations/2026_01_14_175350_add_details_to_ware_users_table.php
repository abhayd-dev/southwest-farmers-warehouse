<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ware_users', function (Blueprint $table) {
            $table->string('emp_code')->nullable()->unique()->after('id');
            $table->text('address')->nullable()->after('phone');
            $table->string('profile_image')->nullable()->after('designation');
        });
    }

    public function down(): void
    {
        Schema::table('ware_users', function (Blueprint $table) {
            $table->dropColumn(['emp_code', 'address', 'profile_image']);
        });
    }
};