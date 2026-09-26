<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * store_roles, store_permissions and store_role_has_permissions were live in
 * production with NO primary key at all (Schema::getIndexes() returned []),
 * and each held exactly 2 byte-identical copies of every row -- the root
 * cause of "Store Admin is showing twice" on the Staff screen: any join
 * against store_roles naturally doubles, independently of whether a given
 * staff member's own role assignment is duplicated. ware_roles (the
 * equivalent warehouse table) still has its primary key and no duplicates,
 * so this data corruption is isolated to the store_* permission tables --
 * the original migration (2026_01_10_000004_create_store_permission_tables)
 * does define these constraints; they were lost by something outside this
 * codebase's history. No live foreign keys currently reference these tables,
 * confirmed read-only beforehand, so deduping is safe: every duplicate pair
 * shares the exact same id, so removing one side of each pair changes no
 * value any other row points at.
 *
 * Guarded throughout so this is a no-op wherever the constraint already exists.
 * Postgres-only: this repairs a corruption specific to the production
 * database. A fresh SQLite schema built from $table->id() already has a real
 * primary key and can't have this problem, and SQLite doesn't support
 * ALTER TABLE ... ADD CONSTRAINT at all.
 */
/*
 * Lives in warehouse-pos (not the store repo) because this repo created the
 * store_* tables and its Railway deploy runs `migrate --force`; the store
 * app's Docker start command does not, so store-repo migrations never ran.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $this->dedupeAndKey('store_roles', ['id'], 'store_roles_pkey', primary: true);
        $this->dedupeAndKey('store_permissions', ['id'], 'store_permissions_pkey', primary: true);
        $this->dedupeAndKey(
            'store_role_has_permissions',
            ['permission_id', 'role_id'],
            'store_role_perm_primary',
            primary: true
        );

        // Prevent a future duplicate role/permission NAME even with the id-based
        // duplication above fixed (a second `StoreRole::create()` with the same
        // name would otherwise still succeed and reintroduce this class of bug).
        if (Schema::hasTable('store_roles') && !Schema::hasIndex('store_roles', 'store_roles_name_guard_unique')) {
            DB::statement('CREATE UNIQUE INDEX store_roles_name_guard_unique ON store_roles (name, guard_name)');
        }
        if (Schema::hasTable('store_permissions') && !Schema::hasIndex('store_permissions', 'store_permissions_name_guard_unique')) {
            DB::statement('CREATE UNIQUE INDEX store_permissions_name_guard_unique ON store_permissions (name, guard_name)');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ([
            ['store_roles', 'store_roles_pkey'],
            ['store_permissions', 'store_permissions_pkey'],
            ['store_role_has_permissions', 'store_role_perm_primary'],
        ] as [$table, $constraint]) {
            if (Schema::hasTable($table) && Schema::hasIndex($table, $constraint)) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$constraint}");
            }
        }
        foreach ([
            ['store_roles', 'store_roles_name_guard_unique'],
            ['store_permissions', 'store_permissions_name_guard_unique'],
        ] as [$table, $index]) {
            if (Schema::hasTable($table) && Schema::hasIndex($table, $index)) {
                DB::statement("DROP INDEX {$index}");
            }
        }
    }

    private function dedupeAndKey(string $table, array $keyColumns, string $constraint, bool $primary): void
    {
        if (!Schema::hasTable($table) || Schema::hasIndex($table, $constraint)) {
            return;
        }

        $on = collect($keyColumns)->map(fn ($c) => "a.{$c} = b.{$c}")->implode(' AND ');

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("DELETE FROM {$table} a USING {$table} b WHERE a.ctid > b.ctid AND {$on}");
        } else {
            $groupBy = implode(', ', $keyColumns);
            DB::statement("DELETE FROM {$table} WHERE rowid NOT IN (SELECT MIN(rowid) FROM {$table} GROUP BY {$groupBy})");
        }

        $columnList = implode(', ', $keyColumns);
        $type = $primary ? 'PRIMARY KEY' : 'UNIQUE';
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$constraint} {$type} ({$columnList})");
    }
};
