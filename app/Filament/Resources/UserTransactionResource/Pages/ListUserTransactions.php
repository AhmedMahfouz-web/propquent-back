<?php

namespace App\Filament\Resources\UserTransactionResource\Pages;

use App\Filament\Resources\UserTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\Page;

class ListUserTransactions extends Page
{
    protected static string $resource = UserTransactionResource::class;
    
    protected static string $view = 'filament.resources.user-transaction-resource.pages.list-user-transactions';

    protected function getHeaderActions(): array
    {
        return [
            // Removed CreateAction - using Livewire table's headerActions instead
        ];
    }
}
