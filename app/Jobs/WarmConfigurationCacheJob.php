<?php

namespace App\Jobs;

use App\Services\ConfigurationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WarmConfigurationCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300;

    /**
     * Categories to warm (null means all categories).
     */
    private ?array $categories;

    /**
     * Whether to preload critical configurations only.
     */
    private bool $criticalOnly;

    /**
     * Create a new job instance.
     */
    public function __construct(?array $categories = null, bool $criticalOnly = false)
    {
        $this->categories = $categories;
        $this->criticalOnly = $criticalOnly;
    }

    /**
     * Execute the job.
     */
    public function handle(ConfigurationService $configService): void
    {
        Log::info('Starting configuration cache warming job', [
            'categories' => $this->categories,
            'critical_only' => $this->criticalOnly,
        ]);

        try {
            if ($this->criticalOnly) {
                $configService->preloadCriticalConfigurations();
                Log::info('Critical configuration cache warming completed');
            } elseif ($this->categories) {
                $warmed = $configService->warmCacheForCategories($this->categories);
                Log::info('Configuration cache warming completed for specific categories', [
                    'warmed_categories' => $warmed,
                ]);
            } else {
                $warmed = $configService->warmCache();
                Log::info('Full configuration cache warming completed', [
                    'warmed_categories' => $warmed,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Configuration cache warming failed', [
                'error' => $e->getMessage(),
                'categories' => $this->categories,
                'critical_only' => $this->criticalOnly,
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Configuration cache warming job failed permanently', [
            'error' => $exception->getMessage(),
            'categories' => $this->categories,
            'critical_only' => $this->criticalOnly,
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['cache', 'configuration', 'warming'];
    }
}
