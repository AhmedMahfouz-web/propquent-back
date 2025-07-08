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
        $userName = Auth::user() ? Auth::user()->name : 'System';

        $message = "User '{$userName}' {$action} a {$modelName}.";

        Log::create([
            'message' => $message,
            'level' => 'info',
            'context' => json_encode($model->getDirty()), // Log only changed attributes for updates
        ]);
    }
}
