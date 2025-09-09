<?php

namespace App\Services;

use App\Models\SystemConfiguration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class ConfigurationService
{
    /**
     * Cache key prefix for configurations.
     */
    private const CACHE_TAG = 'system_configurations';

    /**
     * Cache TTL in seconds (24 hours).
     */
    private const CACHE_TTL = 86400;

    /**
     * Cache TTL for frequently accessed data (1 hour).
     */
    private const FREQUENT_CACHE_TTL = 3600;

    /**
     * Cache TTL for options data (6 hours).
     */
    private const OPTIONS_CACHE_TTL = 21600;

    /**
     * Cache warming batch size.
     */
    private const CACHE_WARM_BATCH_SIZE = 50;

    /**
     * Get configurations by category with caching.
     */
    public function getConfigurationsByCategory(string $category): Collection
    {
        $cacheKey = "config.{$category}.all";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($category) {
            return SystemConfiguration::getByCategory($category);
        });
    }

    /**
     * Get configuration options for dropdowns with caching.
     */
    public function getOptions(string $category): array
    {
        $cacheKey = "config.{$category}.options";

        return Cache::remember($cacheKey, self::OPTIONS_CACHE_TTL, function () use ($category) {
            return SystemConfiguration::getOptions($category);
        });
    }

    /**
     * Get frequently accessed configuration with shorter TTL.
     */
    public function getFrequentConfiguration(string $category): Collection
    {
        $cacheKey = "config.{$category}.frequent";

        return Cache::remember($cacheKey, self::FREQUENT_CACHE_TTL, function () use ($category) {
            return SystemConfiguration::getByCategory($category);
        });
    }

    /**
     * Warm cache for all configuration categories.
     */
    public function warmCache(): array
    {
        $categories = $this->getAllCategories();
        $warmed = [];

        foreach ($categories as $category) {
            try {
                // Warm main configuration cache
                $this->getConfigurationsByCategory($category);

                // Warm options cache
                $this->getOptions($category);

                $warmed[] = $category;
            } catch (\Exception $e) {
                \Log::warning("Failed to warm cache for category: {$category}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $warmed;
    }

    /**
     * Warm cache for specific categories.
     */
    public function warmCacheForCategories(array $categories): array
    {
        $warmed = [];

        foreach ($categories as $category) {
            try {
                $this->getConfigurationsByCategory($category);
                $this->getOptions($category);
                $warmed[] = $category;
            } catch (\Exception $e) {
                \Log::warning("Failed to warm cache for category: {$category}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $warmed;
    }

    /**
     * Get a single configuration value.
     */
    public function getValue(string $category, string $key, mixed $default = null): mixed
    {
        $cacheKey = "config.{$category}.key.{$key}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($category, $key, $default) {
            return SystemConfiguration::getValue($category, $key, $default);
        });
    }

    /**
     * Update an existing configuration.
     */
    public function updateConfiguration(string $category, string $key, string $value): bool
    {
        try {
            $config = SystemConfiguration::byCategory($category)
                ->where('key', $key)
                ->firstOrFail();

            $config->update(['value' => $value]);

            $this->clearCategoryCache($category);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Add a new configuration.
     */
    public function addConfiguration(array $data): SystemConfiguration
    {
        $config = SystemConfiguration::create($data);

        $this->clearCategoryCache($data['category']);

        return $config;
    }

    /**
     * Create a new configuration (alias for addConfiguration).
     */
    public function createConfiguration(array $data): SystemConfiguration
    {
        return $this->addConfiguration($data);
    }

    /**
     * Delete a configuration if it's not in use.
     */
    public function deleteConfiguration(int $id): bool
    {
        try {
            $config = SystemConfiguration::findOrFail($id);

            if (!$this->canDeleteConfiguration($id)) {
                return false;
            }

            $category = $config->category;
            $config->delete();

            $this->clearCategoryCache($category);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete a configuration by category and key.
     */
    public function deleteConfigurationByKey(string $category, string $key): bool
    {
        try {
            $config = SystemConfiguration::byCategory($category)
                ->where('key', $key)
                ->first();

            if (!$config) {
                return false;
            }

            return $this->deleteConfiguration($config->id);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if a configuration can be deleted (not in use).
     */
    public function canDeleteConfiguration(int $id): bool
    {
        $config = SystemConfiguration::find($id);

        if (!$config) {
            return false;
        }

        // Check if configuration is in use based on category
        return match ($config->category) {
            'project_statuses' => !$this->isProjectStatusInUse($config->key),
            'project_stages' => !$this->isProjectStageInUse($config->key),
            'project_types' => !$this->isProjectTypeInUse($config->key),
            'transaction_types' => !$this->isTransactionTypeInUse($config->key),
            'transaction_serving' => !$this->isTransactionServingInUse($config->key),
            'transaction_methods' => !$this->isTransactionMethodInUse($config->key),
            'transaction_statuses' => !$this->isTransactionStatusInUse($config->key),
            'property_types' => !$this->isPropertyTypeInUse($config->key),
            default => true, // Allow deletion for unknown categories
        };
    }

    /**
     * Clear all cached configurations for a category.
     */
    public function clearCategoryCache(string $category): void
    {
        try {
            // Clear specific cache keys for this category
            $this->fallbackCacheClear($category);

            \Log::info("Cache cleared for category: {$category}");
        } catch (\Exception $e) {
            \Log::error("Failed to clear cache for category: {$category}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear all configuration cache.
     */
    public function clearAllCache(): void
    {
        try {
            // Clear all configuration cache keys
            $categories = $this->getAllCategories();
            foreach ($categories as $category) {
                $this->fallbackCacheClear($category);
            }
            \Log::info("All configuration cache cleared");
        } catch (\Exception $e) {
            \Log::error("Failed to clear all configuration cache", [
                'error' => $e->getMessage()
            ]);

            // Fallback to full cache flush
            Cache::flush();
        }
    }

    /**
     * Fallback cache clearing method.
     */
    private function fallbackCacheClear(string $category): void
    {
        $keys = [
            "config.{$category}.all",
            "config.{$category}.options",
            "config.{$category}.frequent",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }



    /**
     * Get all distinct configuration categories.
     */
    private function getAllCategories(): array
    {
        return SystemConfiguration::query()->distinct()->pluck('category')->toArray();
    }

    /**
     * Check if project status is in use.
     */
    private function isProjectStatusInUse(string $status): bool
    {
        return DB::table('projects')->where('status', $status)->exists();
    }

    /**
     * Check if project stage is in use.
     */
    private function isProjectStageInUse(string $stage): bool
    {
        return DB::table('projects')->where('stage', $stage)->exists();
    }

    /**
     * Check if project type is in use.
     */
    private function isProjectTypeInUse(string $type): bool
    {
        return DB::table('projects')->where('type', $type)->exists();
    }

    /**
     * Check if transaction type is in use.
     */
    private function isTransactionTypeInUse(string $type): bool
    {
        return DB::table('project_transactions')->where('transaction_type', $type)->exists();
    }

    /**
     * Check if transaction serving is in use.
     */
    private function isTransactionServingInUse(string $serving): bool
    {
        return DB::table('project_transactions')->where('serving', $serving)->exists();
    }

    /**
     * Check if transaction method is in use.
     */
    private function isTransactionMethodInUse(string $method): bool
    {
        return DB::table('project_transactions')->where('method', $method)->exists();
    }

    /**
     * Check if transaction status is in use.
     */
    private function isTransactionStatusInUse(string $status): bool
    {
        return DB::table('project_transactions')->where('status', $status)->exists();
    }

    /**
     * Check if property type is in use.
     */
    private function isPropertyTypeInUse(string $type): bool
    {
        return DB::table('projects')->where('property_type', $type)->exists();
    }

    /**
     * Get cache statistics.
     */
    public function getCacheStats(): array
    {
        $categories = $this->getAllCategories();
        $stats = [
            'total_categories' => count($categories),
            'cached_categories' => 0,
            'cache_sizes' => [],
            'cache_hit_ratio' => $this->calculateCacheHitRatio(),
        ];

        foreach ($categories as $category) {
            $allKey = "config.{$category}.all";
            $optionsKey = "config.{$category}.options";

            $isCached = Cache::has($allKey) || Cache::has($optionsKey);

            if ($isCached) {
                $stats['cached_categories']++;
            }

            $stats['cache_sizes'][$category] = [
                'configurations' => Cache::has($allKey) ? 'cached' : 'not_cached',
                'options' => Cache::has($optionsKey) ? 'cached' : 'not_cached',
            ];
        }

        return $stats;
    }

    /**
     * Preload critical configurations.
     */
    public function preloadCriticalConfigurations(): void
    {
        $criticalCategories = [
            'project_statuses',
            'transaction_types',
            'property_types',
            'transaction_methods',
            'transaction_statuses',
        ];

        foreach ($criticalCategories as $category) {
            try {
                $this->getConfigurationsByCategory($category);
                $this->getOptions($category);
                \Log::debug("Preloaded critical configuration: {$category}");
            } catch (\Exception $e) {
                \Log::warning("Failed to preload critical configuration: {$category}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Calculate cache hit ratio (simplified implementation).
     */
    private function calculateCacheHitRatio(): float
    {
        // This is a simplified implementation
        // In a real scenario, you'd track hits/misses over time
        $categories = $this->getAllCategories();
        $hits = 0;
        $total = count($categories) * 2; // 2 cache keys per category

        foreach ($categories as $category) {
            if (Cache::has("config.{$category}.all")) $hits++;
            if (Cache::has("config.{$category}.options")) $hits++;
        }

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    /**
     * Invalidate cache when configuration changes.
     */
    public function invalidateOnChange(string $category): void
    {
        $this->clearCategoryCache($category);

        // Also clear related widget caches
        $this->clearRelatedCaches($category);

        // Preload the category again for immediate availability
        try {
            $this->getConfigurationsByCategory($category);
            $this->getOptions($category);
        } catch (\Exception $e) {
            \Log::warning("Failed to preload after invalidation: {$category}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear related caches that depend on configuration.
     */
    private function clearRelatedCaches(string $category): void
    {
        $relatedCacheKeys = [
            'project_status_widget_data',
            'transaction_summary_widget_*',
            'recent_activity_widget_data',
            'trend_analysis_widget_*',
        ];

        foreach ($relatedCacheKeys as $pattern) {
            if (str_contains($pattern, '*')) {
                // For wildcard patterns, we'd need Redis SCAN or similar
                // For now, we'll clear specific known keys
                $this->clearWildcardCache($pattern);
            } else {
                Cache::forget($pattern);
            }
        }
    }

    /**
     * Clear cache keys matching a wildcard pattern.
     */
    private function clearWildcardCache(string $pattern): void
    {
        try {
            // This is a simplified implementation
            // In production, you'd use Redis SCAN or maintain a key registry
            $basePattern = str_replace('*', '', $pattern);

            // Clear known variations
            $variations = ['monthly', 'weekly', 'quarterly', 'projects_vs_transactions', 'revenue_vs_investment'];

            foreach ($variations as $variation) {
                Cache::forget($basePattern . $variation);
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to clear wildcard cache pattern: {$pattern}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Schedule cache warming for optimal performance.
     */
    public function scheduleWarmup(): void
    {
        // This would typically be called from a scheduled job
        $this->preloadCriticalConfigurations();

        // Warm cache during off-peak hours
        $this->warmCache();

        \Log::info("Configuration cache warming completed");
    }
}
