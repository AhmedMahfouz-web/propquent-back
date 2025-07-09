<?php

namespace App\Models\Traits;

use App\Models\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            static::logActivity($model, 'created');
        });

        static::updated(function (Model $model) {
            static::logActivity($model, 'updated');
        });

        static::deleted(function (Model $model) {
            static::logActivity($model, 'deleted');
        });
    }

    protected static function logActivity(Model $model, string $action)
    {
        $modelName = class_basename($model);
        // Correctly use full_name from the User model
        $userName = Auth::user() ? Auth::user()->full_name : 'System';

        $message = "User '{$userName}' {$action} a {$modelName}.";

        $context = [];
        switch ($action) {
            case 'created':
                // For created events, log all attributes of the new model
                $context = $model->getAttributes();
                break;
            case 'updated':
                // For updated events, log only the changed attributes
                $context = $model->getDirty();
                break;
            case 'deleted':
                // For deleted events, log the model's attributes before it was deleted
                $context = $model->getAttributes();
                break;
        }

        Log::create([
            'message' => $message,
            'level' => 'info',
            'context' => json_encode($context),
        ]);
    }
}
