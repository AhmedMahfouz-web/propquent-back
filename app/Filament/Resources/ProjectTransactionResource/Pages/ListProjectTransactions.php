<?php

namespace App\Filament\Resources\ProjectTransactionResource\Pages;

use App\Filament\Resources\ProjectTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectTransactions extends ListRecords
{
    protected static string $resource = ProjectTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
