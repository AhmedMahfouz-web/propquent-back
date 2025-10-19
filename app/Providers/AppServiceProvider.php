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

        // Register observers for automatic asset evaluation updates
        \App\Models\ProjectTransaction::observe(\App\Observers\ProjectTransactionObserver::class);
        \App\Models\ValueCorrection::observe(\App\Observers\ValueCorrectionObserver::class);
        \App\Models\Project::observe(\App\Observers\ProjectObserver::class);
    }
}
