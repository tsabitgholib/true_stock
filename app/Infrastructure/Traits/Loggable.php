<?php

namespace App\Infrastructure\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Loggable
{
    public static function bootLoggable()
    {
        static::created(function ($model) {
            static::logChange($model, 'created');
        });

        static::updated(function ($model) {
            static::logChange($model, 'updated');
        });

        static::deleted(function ($model) {
            static::logChange($model, 'deleted');
        });
    }

    protected static function logChange($model, $event)
    {
        $oldValues = null;
        $newValues = null;

        if ($event === 'updated') {
            $changed = $model->getDirty();
            $oldValues = array_intersect_key($model->getOriginal(), $changed);
            $newValues = $changed;

            // Skip if no actual data changed (e.g., only timestamps)
            if (empty($newValues) || (count($newValues) === 1 && isset($newValues['updated_at']))) {
                return;
            }
        } elseif ($event === 'created') {
            $newValues = $model->getAttributes();
        } elseif ($event === 'deleted') {
            $oldValues = $model->getOriginal();
        }

        // Filter out sensitive or excluded fields
        $exclude = property_exists($model, 'auditExclude') ? $model->auditExclude : ['id', 'created_at', 'updated_at', 'password'];
        
        if ($oldValues) {
            $oldValues = array_diff_key($oldValues, array_flip($exclude));
        }
        if ($newValues) {
            $newValues = array_diff_key($newValues, array_flip($exclude));
        }

        \App\Jobs\ProcessAuditLog::dispatch([
            'user_id' => Auth::id() ?? 1, // Fallback to system/seeder user
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => Request::hasHeader('host') ? Request::fullUrl() : 'console',
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'console',
        ]);
    }
}
