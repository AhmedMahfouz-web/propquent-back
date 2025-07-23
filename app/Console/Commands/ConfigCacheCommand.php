<?php

namespace App\Console\Commands;

use App\Services\ConfigurationService;
use Illuminate\Console\Command;

class ConfigCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'config:cache-manage
                            {action : The action to perform (warm|clear|stats|preload)}
                            {--category= : Specific category to target}
                            {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Manage configuration cache (warm, clear, stats, preload)';

    /**
     * Configuration service instance.
     */
    private ConfigurationService $configService;

    /**
     * Create a new command instance.
     */
    public function __construct(ConfigurationService $configService)
    {
        parent::__construct();
        $this->configService = $configService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');
        $category = $this->option('category');
        $force = $this->option('force');

        return match ($action) {
            'warm' => $this->warmCache($category),
            'clear' => $this->clearCache($category, $force),
            'stats' => $this->showStats(),
            'preload' => $this->preloadCache(),
            default => $this->showHelp(),
        };
    }

    /**
     * Warm configuration cache.
     */
    private function warmCache(?string $category): int
    {
        $this->info('🔥 Warming configuration cache...');

        try {
            if ($category) {
                $warmed = $this->configService->warmCacheForCategories([$category]);
                $this->info("✅ Cache warmed for category: {$category}");
            } else {
                $warmed = $this->configService->warmCache();
                $this->info("✅ Cache warmed for " . count($warmed) . " categories");
            }

            $this->table(['Category'], array_map(fn($cat) => [$cat], $warmed));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to warm cache: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Clear configuration cache.
     */
    private function clearCache(?string $category, bool $force): int
    {
        if (!$force && !$this->confirm('Are you sure you want to clear the configuration cache?')) {
            $this->info('Cache clear cancelled.');
            return Command::SUCCESS;
        }

        $this->info('🧹 Clearing configuration cache...');

        try {
            if ($category) {
                $this->configService->clearCategoryCache($category);
                $this->info("✅ Cache cleared for category: {$category}");
            } else {
                $this->configService->clearAllCache();
                $this->info("✅ All configuration cache cleared");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to clear cache: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Show cache statistics.
     */
    private function showStats(): int
    {
        $this->info('📊 Configuration Cache Statistics');

        try {
            $stats = $this->configService->getCacheStats();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Categories', $stats['total_categories']],
                    ['Cached Categories', $stats['cached_categories']],
                    ['Cache Hit Ratio', $stats['cache_hit_ratio'] . '%'],
                ]
            );

            if (!empty($stats['cache_sizes'])) {
                $this->newLine();
                $this->info('📋 Cache Status by Category:');

                $rows = [];
                foreach ($stats['cache_sizes'] as $category => $sizes) {
                    $rows[] = [
                        $category,
                        $sizes['configurations'] === 'cached' ? '✅' : '❌',
                        $sizes['options'] === 'cached' ? '✅' : '❌',
                    ];
                }

                $this->table(['Category', 'Configurations', 'Options'], $rows);
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to get cache stats: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Preload critical configurations.
     */
    private function preloadCache(): int
    {
        $this->info('⚡ Preloading critical configurations...');

        try {
            $this->configService->preloadCriticalConfigurations();
            $this->info("✅ Critical configurations preloaded");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to preload configurations: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Show command help.
     */
    private function showHelp(): int
    {
        $this->error('❌ Invalid action. Available actions: warm, clear, stats, preload');

        $this->newLine();
        $this->info('Examples:');
        $this->line('  php artisan config:cache-manage warm');
        $this->line('  php artisan config:cache-manage clear --category=project_statuses');
        $this->line('  php artisan config:cache-manage stats');
        $this->line('  php artisan config:cache-manage preload');

        return Command::FAILURE;
    }
}
