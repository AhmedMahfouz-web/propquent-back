<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Temporarily disabled to debug console issue
        // Event::listen(
        //     \App\Events\ConfigurationChanged::class,
        //     \App\Listeners\InvalidateConfigurationCache::class
        // );

        // Model observers removed - caching disabled for performance
    }
}
