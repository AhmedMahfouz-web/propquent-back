<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Register configuration cache event listeners
        \Event::listen(
            \App\Events\ConfigurationChanged::class,
            \App\Listeners\InvalidateConfigurationCache::class
        );

        // Model observers removed - caching disabled for performance
    }
}
