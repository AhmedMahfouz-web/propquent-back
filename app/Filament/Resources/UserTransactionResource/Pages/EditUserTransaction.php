<?php

namespace App\Filament\Resources\UserTransactionResource\Pages;

use App\Filament\Resources\UserTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserTransaction extends EditRecord
{
    protected static string $resource = UserTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
