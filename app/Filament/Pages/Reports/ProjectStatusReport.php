<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;

class ProjectStatusReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string $view = 'filament.pages.reports.project-status-report';

    protected static ?string $navigationLabel = 'Project Status';

    protected static ?string $title = 'Project Status Report';

    protected static ?string $navigationGroup = 'Financial Reports';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return null;
    }
}
