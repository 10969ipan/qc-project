<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    protected static $originalData = [];

    /**
     * Store original data before an update occurs.
     */
    public static function setOriginalData($class, $id, $original)
    {
        self::$originalData[$class][$id] = $original;
    }

    public static function log($action, $model = null, $description = null, $properties = null)
    {
        // Auto-calculate properties for 'updated' action if properties is null and model is provided
        if ($action === 'updated' && $model && $properties === null) {
            $class = get_class($model);
            $id = $model->getKey();
            
            if (isset(self::$originalData[$class][$id])) {
                $properties = self::$originalData[$class][$id];
                // Clear from memory
                unset(self::$originalData[$class][$id]);
            }
        }

        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
