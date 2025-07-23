<?php

namespace App\Repositories;

use App\Models\ReportCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ReportCacheRepository
{
    /**
     * Cache report data.
     */
    public function cacheReport(string $reportType, string $reportKey, array $reportData, int $expiresInMinutes = 60): void
    {
        // Check if the ReportCache model exists
        if (!class_exists(ReportCache::class)) {
            // Fall back to Laravel's cache if the model doesn't exist
            Cache::put("report_{$reportType}_{$reportKey}", $reportData, Carbon::now()->addMinutes($expiresInMinutes));
            return;
        }

        try {
            // Use the ReportCache model
            ReportCache::updateOrCreate(
                [
                    'report_type' => $reportType,
                    'report_key' => $reportKey,
                ],
                [
                    'report_data' => $reportData,
                    'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
                ]
            );
        } catch (\Exception $e) {
            // If there's an error (like table doesn't exist), fall back to Laravel's cache
            \Log::warning('Error accessing report_cache table: ' . $e->getMessage());
            Cache::put("report_{$reportType}_{$reportKey}", $reportData, Carbon::now()->addMinutes($expiresInMinutes));
        }
    }

    /**
     * Get cached report data.
     */
    public function getCachedReport(string $reportType, string $reportKey): ?array
    {
        // Check if the ReportCache model exists
        if (!class_exists(ReportCache::class)) {
            // Fall back to Laravel's cache if the model doesn't exist
            return Cache::get("report_{$reportType}_{$reportKey}");
        }

        try {
            // Use the ReportCache model
            $cachedReport = ReportCache::where('report_type', $reportType)
                ->where('report_key', $reportKey)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            return $cachedReport ? $cachedReport->report_data : null;
        } catch (\Exception $e) {
            // If there's an error (like table doesn't exist), fall back to Laravel's cache
            \Log::warning('Error accessing report_cache table: ' . $e->getMessage());
            return Cache::get("report_{$reportType}_{$reportKey}");
        }
    }

    /**
     * Clear cached report data.
     */
    public function clearCachedReport(string $reportType, string $reportKey = null): void
    {
        // Check if the ReportCache model exists
        if (!class_exists(ReportCache::class)) {
            // Fall back to Laravel's cache if the model doesn't exist
            if ($reportKey) {
                Cache::forget("report_{$reportType}_{$reportKey}");
            } else {
                // Clear all reports of this type
                $keys = Cache::get('report_keys_' . $reportType, []);
                foreach ($keys as $key) {
                    Cache::forget("report_{$reportType}_{$key}");
                }
                Cache::forget('report_keys_' . $reportType);
            }
            return;
        }

        try {
            // Use the ReportCache model
            $query = ReportCache::where('report_type', $reportType);

            if ($reportKey) {
                $query->where('report_key', $reportKey);
            }

            $query->delete();
        } catch (\Exception $e) {
            // If there's an error (like table doesn't exist), fall back to Laravel's cache
            \Log::warning('Error accessing report_cache table: ' . $e->getMessage());
            if ($reportKey) {
                Cache::forget("report_{$reportType}_{$reportKey}");
            } else {
                // Clear all reports of this type
                $keys = Cache::get('report_keys_' . $reportType, []);
                foreach ($keys as $key) {
                    Cache::forget("report_{$reportType}_{$key}");
                }
                Cache::forget('report_keys_' . $reportType);
            }
        }
    }

    /**
     * Clear expired cached reports.
     */
    public function clearExpiredReports(): int
    {
        // Check if the ReportCache model exists
        if (!class_exists(ReportCache::class)) {
            return 0;
        }

        try {
            // Use the ReportCache model
            return ReportCache::where('expires_at', '<', Carbon::now())->delete();
        } catch (\Exception $e) {
            // If there's an error (like table doesn't exist), log it and return 0
            \Log::warning('Error accessing report_cache table: ' . $e->getMessage());
            return 0;
        }
    }
}
