<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only. Two apps share one database and each keeps its own copy of the
 * models, so a model can silently drift from the real table. Every bug of that
 * kind found so far was one of the checks below:
 *   - $fillable names a column that doesn't exist  -> INSERT/UPDATE crashes
 *   - SoftDeletes on a table with no deleted_at    -> every query crashes
 *   - NOT NULL column with no default isn't fillable -> create() crashes
 *   - the model's table doesn't exist at all
 * Exits non-zero when it finds any, so it can gate CI or a deploy.
 */
class SchemaDrift extends Command
{
    protected $signature = 'schema:drift {--model= : Only check this model class basename}';

    protected $description = 'Compare every Eloquent model against the real database table';

    public function handle(): int
    {
        $problems = [];
        $checked = 0;

        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\' . basename($file, '.php');
            if ($this->option('model') && basename($file, '.php') !== $this->option('model')) {
                continue;
            }
            if (!class_exists($class) || !is_subclass_of($class, Model::class) || (new \ReflectionClass($class))->isAbstract()) {
                continue;
            }

            $checked++;
            array_push($problems, ...$this->inspect(new $class));
        }

        if (!$problems) {
            $this->info("No drift found across {$checked} models.");

            return self::SUCCESS;
        }

        $this->table(['Model', 'Table', 'Problem'], $problems);
        $this->error(count($problems) . " problem(s) in {$checked} models.");

        return self::FAILURE;
    }

    private function inspect(Model $model): array
    {
        $name = class_basename($model);
        $table = $model->getTable();

        if (!Schema::hasTable($table)) {
            return [[$name, $table, 'table does not exist']];
        }

        $columns = collect(Schema::getColumns($table))->keyBy('name');
        $out = [];

        foreach ($model->getFillable() as $field) {
            if (!$columns->has($field)) {
                $out[] = [$name, $table, "\$fillable has \"{$field}\" but the table has no such column"];
            }
        }

        $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($model), true);
        $deletedAt = $usesSoftDeletes ? $model->getDeletedAtColumn() : 'deleted_at';
        if ($usesSoftDeletes && !$columns->has($deletedAt)) {
            $out[] = [$name, $table, "uses SoftDeletes but the table has no \"{$deletedAt}\" column"];
        }

        // Only models with an explicit whitelist can be missing a required column;
        // $guarded = [] models accept everything.
        if ($model->getFillable() && $model->getGuarded() !== []) {
            $optional = ['id', $model->getCreatedAtColumn(), $model->getUpdatedAtColumn(), $deletedAt];

            foreach ($columns as $column) {
                $required = !$column['nullable'] && $column['default'] === null && !$column['auto_increment'];
                if ($required && !in_array($column['name'], $optional, true) && !in_array($column['name'], $model->getFillable(), true)) {
                    $out[] = [$name, $table, "column \"{$column['name']}\" is NOT NULL with no default but isn't in \$fillable (create() will fail)"];
                }
            }
        }

        return $out;
    }
}
