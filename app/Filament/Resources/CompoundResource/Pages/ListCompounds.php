<?php

namespace App\Filament\Resources\CompoundResource\Pages;

use App\Filament\Resources\CompoundResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompounds extends ListRecords
{
    protected static string $resource = CompoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
