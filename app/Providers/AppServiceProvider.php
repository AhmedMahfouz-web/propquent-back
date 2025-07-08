<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Livewire\ThemeSwitcherPanel;
use App\Filament\Livewire\ThemeSwitcher;
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
        Livewire::component('theme-switcher-panel', ThemeSwitcherPanel::class);
        Livewire::component('theme-switcher', ThemeSwitcher::class);
    }
}
