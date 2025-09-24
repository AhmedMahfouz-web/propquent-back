<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('admins')
            ->authPasswordBroker('users')
            ->brandName('PROPQUENT')
            ->brandLogo(app()->runningInConsole() ? null : asset('favicon.ico'))
            ->brandLogoHeight('2rem')
            ->favicon(app()->runningInConsole() ? null : asset('favicon.ico'))
            ->colors([
                'primary' => Color::Sky,
                'gray' => Color::Slate,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Blue,
            ])
            ->font('Inter')
            ->navigationGroups([
                NavigationGroup::make('Financial Reports')
                    ->icon('heroicon-o-chart-bar')
                    ->collapsible(),
                NavigationGroup::make('Transactions')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible(),
                NavigationGroup::make('Real Estate')
                    ->icon('heroicon-o-building-office-2')
                    ->collapsible(),
                NavigationGroup::make('User Management')
                    ->icon('heroicon-o-users')
                    ->collapsible(),
                NavigationGroup::make('Settings')
                    ->icon('heroicon-o-cog-6-tooth')
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->topNavigation(false)
            ->maxContentWidth('full')
            // ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            // ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\ProjectStatusOverview::class,
                \App\Filament\Widgets\ProjectStatusWidget::class,
                \App\Filament\Widgets\ProjectStatusBreakdown::class,
                \App\Filament\Widgets\TransactionSummaryWidget::class,
                \App\Filament\Widgets\RecentActivityWidget::class,
                \App\Filament\Widgets\TrendAnalysisWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->spa();
    }
}
