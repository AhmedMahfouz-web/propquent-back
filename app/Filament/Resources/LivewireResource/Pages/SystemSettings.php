<?php

namespace App\Filament\Resources\LivewireResource\Pages;

use App\Filament\Resources\LivewireResource;
use Filament\Resources\Pages\Page;

class SystemSettings extends Page
{
    protected static string $resource = LivewireResource::class;

    protected static string $view = 'filament.resources.livewire-resource.pages.system-settings';
}
