<?php

namespace App\Listeners;

use App\Events\ConfigurationChanged;
use App\Jobs\WarmConfigurationCacheJob;
use App\Services\ConfigurationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class InvalidateConfigurationCache implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Configuration service instance.
     */
    private ConfigurationService $configService;

    /**
     * Create the event listener.
     */
    public function __construct(ConfigurationService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Handle the event.
     */
    public function handle(ConfigurationChanged $event): void
    {
        $category = $event->configuration->category;

        Log::info('Configuration changed, invalidating cache', [
            'category' => $category,
            'key' => $event->configuration->key,
            'change_type' => $event->changeType,
        ]);

        try {
            // Invalidate cache for the affected category
            $this->configService->invalidateOnChange($category);

            // If this is a critical configuration, warm it immediately
            $criticalCategories = [
                'project_statuses',
                'transaction_types',
                'property_types',
                'transaction_methods',
            ];

            if (in_array($category, $criticalCategories)) {
                // Dispatch job to warm cache in background
                WarmConfigurationCacheJob::dispatch([$category], false)
                    ->delay(now()->addSeconds(5)); // Small delay to ensure DB changes are committed
            }

            Log::info('Configuration cache invalidated successfully', [
                'category' => $category,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate configuration cache', [
                'category' => $category,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(ConfigurationChanged $event, \Throwable $exception): void
    {
        Log::error('Configuration cache invalidation failed', [
            'category' => $event->configuration->category,
            'key' => $event->configuration->key,
            'error' => $exception->getMessage(),
        ]);
    }
}
