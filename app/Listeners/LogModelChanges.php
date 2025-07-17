<?php

namespace App\Listeners;

use Illuminate\Database\Eloquent\Model;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogModelChanges implements ShouldQueue
{
    use InteractsWithQueue;
    private static $isLogging = false;

    public function handle($event, $data)
    {
        // LOG SYSTEM DISABLED - Commented out to prevent system breakage
        return;
        
        /*
        // Prevent recursive logging to avoid memory exhaustion
        if (self::$isLogging) {
            return;
        }

        foreach ($data as $model) {
            if ($model instanceof Model && !$model instanceof Log) {
                $action = $this->getActionFromEvent($event);
                if ($action) {
                    // Set flag to prevent recursion
                    self::$isLogging = true;

                    try {
                        // Limit context size to prevent memory issues
                        $changes = $model->getChanges();
                        $context = $this->limitContextSize($changes);

                        DB::table('logs')->insert([
                            'message' => class_basename($model) . " was {$action}",
                            'context' => json_encode($context),
                            'level' => 'info',
                        ]);
                    } catch (\Exception $e) {
                        // Log the error but don't break the application
                        error_log("Logging error: " . $e->getMessage());
                    } finally {
                        // Always reset the flag
                        self::$isLogging = false;
                    }
                }
            }
        }
        */
    }

    private function limitContextSize($changes, $maxSize = 1000)
    {
        $jsonString = json_encode($changes);
        if (strlen($jsonString) > $maxSize) {
            // Truncate large context to prevent memory issues
            return ['truncated' => true, 'size' => strlen($jsonString), 'sample' => array_slice($changes, 0, 5)];
        }
        return $changes;
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
