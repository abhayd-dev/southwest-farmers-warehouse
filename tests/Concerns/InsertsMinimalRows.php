<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Inserts a row into any table, auto-filling every required column that the
 * caller didn't specify. Lets a test create only the columns it cares about
 * instead of hand-maintaining a full insert per table (which goes stale each
 * time a migration adds a NOT NULL column). Foreign keys are not satisfied,
 * so call withoutForeignKeys() first when the test doesn't need them.
 */
trait InsertsMinimalRows
{
    protected function withoutForeignKeys(): void
    {
        // PRAGMA foreign_keys can't be switched inside a transaction (the base
        // TestCase wraps every test in one); deferring the checks can, and the
        // transaction is always rolled back so they never fire.
        DB::statement('PRAGMA defer_foreign_keys = ON');
    }

    protected function insertRow(string $table, array $values = []): int
    {
        $row = [];

        foreach (Schema::getColumns($table) as $column) {
            $name = $column['name'];

            if ($column['auto_increment'] || array_key_exists($name, $values)) {
                continue;
            }
            if ($column['nullable'] || $column['default'] !== null) {
                continue;
            }

            $row[$name] = $this->dummyValueFor($column['type_name'], $name);
        }

        return (int) DB::table($table)->insertGetId($values + $row);
    }

    private function dummyValueFor(string $type, string $name): mixed
    {
        $type = strtolower($type);

        return match (true) {
            str_contains($type, 'int'), $type === 'numeric', $type === 'decimal',
            str_contains($type, 'float'), str_contains($type, 'double'), $type === 'bool', $type === 'boolean' => 1,
            $type === 'date' => now()->toDateString(),
            str_contains($type, 'time') => now()->toDateTimeString(),
            str_contains($type, 'json') => '{}',
            default => $name . '-' . uniqid(),
        };
    }
}
