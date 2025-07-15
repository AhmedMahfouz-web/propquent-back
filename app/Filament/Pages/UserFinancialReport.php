<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class UserFinancialReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.user-financial-report';

    protected static ?string $navigationGroup = 'Financials';

    protected static ?string $slug = 'user-financial-report';

    public static function getNavigationLabel(): string
    {
        return 'Users Financial Report';
    }
}
