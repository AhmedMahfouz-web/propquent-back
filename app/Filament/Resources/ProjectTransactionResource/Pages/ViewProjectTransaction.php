<?php

namespace App\Filament\Resources\ProjectTransactionResource\Pages;

use App\Filament\Resources\ProjectTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProjectTransaction extends ViewRecord
{
    protected static string $resource = ProjectTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
