<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register any report-specific services here
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // This provider doesn't need to do anything at boot time
        // The report configuration will be used by the Filament providers
    }

    /**
     * Check if a specific report is enabled in the configuration.
     *
     * @param string $reportKey The configuration key for the report
     * @return bool Whether the report is enabled
     */
    public static function isReportEnabled(string $reportKey): bool
    {
        return config('reports.enabled.' . $reportKey, false);
    }

    /**
     * Get all enabled reports.
     *
     * @return array Array of enabled report keys
     */
    public static function getEnabledReports(): array
    {
        $reports = config('reports.enabled', []);
        return array_keys(array_filter($reports, fn($enabled) => $enabled === true));
    }

    /**
     * Get all disabled reports.
     *
     * @return array Array of disabled report keys
     */
    public static function getDisabledReports(): array
    {
        $reports = config('reports.enabled', []);
        return array_keys(array_filter($reports, fn($enabled) => $enabled === false));
    }
}
