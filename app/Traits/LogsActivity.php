<?php

namespace App\Traits;

use App\Models\WareActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    // Boot the trait
    protected static function bootLogsActivity()
    {
        // Log Creation
        static::created(function ($model) {
            self::logChange($model, 'created', 'Created record', [
                'attributes' => $model->getAttributes()
            ]);
        });

        // Log Update
        static::updated(function ($model) {
            // Get changed fields only
            $changes = $model->getChanges();
            $original = $model->getOriginal();
            
            $oldValues = [];
            $newValues = [];

            foreach ($changes as $key => $value) {
                // Skip timestamps
                if ($key === 'updated_at' || $key === 'created_at') continue;

                $oldValues[$key] = $original[$key] ?? null;
                $newValues[$key] = $value;
            }

            if (!empty($newValues)) {
                self::logChange($model, 'updated', 'Updated record', [
                    'old' => $oldValues,
                    'new' => $newValues
                ]);
            }
        });

        // Log Deletion
        static::deleted(function ($model) {
            self::logChange($model, 'deleted', 'Deleted record', [
                'attributes' => $model->getAttributes()
            ]);
        });
    }

    protected static function logChange($model, $action, $description, $properties = [])
    {
        if (!Auth::check()) return; // Don't log system/seeder actions usually

        WareActivityLog::create([
            'causer_id'    => Auth::id(),
            'causer_type'  => get_class(Auth::user()),
            'subject_type' => get_class($model),
            'subject_id'   => $model->id,
            'action'       => $action,
            'description'  => $description,
            'properties'   => $properties,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }
}