<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ware_model_has_roles has no unique key. Read-only check found 16 of ~20
 * warehouse users each holding an exact duplicate of their one role (39 rows
 * for what should be ~23) -- the same class of bug the client just reported
 * on the Store side's staff screen ("Super Admin" shown twice). Removes exact
 * duplicate rows (keeping the lowest ctid of each group) and adds a unique
 * index so a duplicate can never be inserted again.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ware_model_has_roles')) {
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement("
                    DELETE FROM ware_model_has_roles a USING ware_model_has_roles b
                    WHERE a.ctid > b.ctid
                      AND a.model_id = b.model_id
                      AND a.model_type = b.model_type
                      AND a.role_id = b.role_id
                ");
            } else {
                DB::statement('
                    DELETE FROM ware_model_has_roles
                    WHERE rowid NOT IN (
                        SELECT MIN(rowid) FROM ware_model_has_roles
                        GROUP BY model_id, model_type, role_id
                    )
                ');
            }

            if (!Schema::hasIndex('ware_model_has_roles', 'ware_model_has_roles_unique')) {
                Schema::table('ware_model_has_roles', function (Blueprint $table) {
                    $table->unique(['model_id', 'model_type', 'role_id'], 'ware_model_has_roles_unique');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ware_model_has_roles') && Schema::hasIndex('ware_model_has_roles', 'ware_model_has_roles_unique')) {
            Schema::table('ware_model_has_roles', function (Blueprint $table) {
                $table->dropUnique('ware_model_has_roles_unique');
            });
        }
    }
};
