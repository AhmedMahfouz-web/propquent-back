<?php

namespace App\Filament\Resources\UserTransactionResource\Pages;

use App\Filament\Resources\UserTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserTransactions extends ListRecords
{
    protected static string $resource = UserTransactionResource::class;
    protected static string $view = 'filament.resources.user-transaction-resource.pages.list-user-transactions';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
