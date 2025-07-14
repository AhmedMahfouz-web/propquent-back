<?php

namespace App\Listeners;

use Illuminate\Database\Eloquent\Model;
use App\Models\Log;

class LogModelChanges
{
    public function handle($event, $data)
    {
        foreach ($data as $model) {
                    if ($model instanceof Model && !$model instanceof Log) {
                $action = $this->getActionFromEvent($event);
                if ($action) {
                    Log::create([
                        'message' => class_basename($model) . " was {$action}",
                        'context' => $model->getChanges(),
                        'level' => 'info',
                    ]);
                }
            }
        }
    }

    private function getActionFromEvent($event)
    {
        if (str_contains($event, 'created')) {
            return 'created';
        }
        if (str_contains($event, 'updated')) {
            return 'updated';
        }
        if (str_contains($event, 'deleted')) {
            return 'deleted';
        }
        return null;
    }
}
