<?php

namespace App\Filament\Resources\UserTransactionResource\Pages;

use App\Filament\Resources\UserTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserTransactions extends ListRecords
{
    protected static string $resource = UserTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
