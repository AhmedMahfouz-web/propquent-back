<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CompanyFinancialReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.company-financial-report';

    protected static ?string $navigationGroup = 'Financials';

    protected static ?string $slug = 'company-financial-report';

    public static function getNavigationLabel(): string
    {
        return 'Company Financial Report';
    }
}
