<?php

namespace App\Filament\Resources\ProjectTransactionResource\Pages;

use App\Filament\Resources\ProjectTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectTransaction extends EditRecord
{
    protected static string $resource = ProjectTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
