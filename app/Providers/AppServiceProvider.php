<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

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
        // Increase PHP execution time limit to prevent timeouts during heavy operations
        ini_set('max_execution_time', 300); // 5 minutes

        // Temporarily disabled theme switcher components to test for infinite loops
        // Livewire::component('theme-switcher-panel', ThemeSwitcherPanel::class);
        // Livewire::component('theme-switcher', ThemeSwitcher::class);
    }
}
