<?php

namespace App\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register theme assets with Filament
        FilamentAsset::register([
            Css::make('theme-support', resource_path('css/theme-support.css')),
            Js::make('theme-manager', resource_path('js/theme-manager.js')),
            Js::make('theme-validator', resource_path('js/theme-validator.js')),
        ]);
    }
}
