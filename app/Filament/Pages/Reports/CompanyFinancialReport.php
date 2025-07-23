<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;

class CompanyFinancialReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.company-financial-report';
    protected static ?string $navigationGroup = 'Financial Reports';
    protected static ?string $title = 'Company Financial Report';
    protected static ?int $navigationSort = 1;
}
