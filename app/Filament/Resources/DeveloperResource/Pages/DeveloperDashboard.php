<?php

namespace App\Filament\Resources\DeveloperResource\Pages;

use App\Filament\Resources\DeveloperResource;
use Filament\Resources\Pages\Page;

class DeveloperDashboard extends Page
{
    protected static string $resource = DeveloperResource::class;

    protected static string $view = 'filament.resources.developer-resource.pages.developer-dashboard';

    protected function getHeaderActions(): array
    {
        return [
            // Add actions if needed for developer tools
        ];
    }
}
