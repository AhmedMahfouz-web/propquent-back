<?php

namespace App\Filament\Resources\ProjectTransactionResource\Pages;

use App\Filament\Resources\ProjectTransactionResource;
use Filament\Resources\Pages\Page;

class ListProjectTransactions extends Page
{
    protected static string $resource = ProjectTransactionResource::class;
    
    protected static string $view = 'filament.resources.project-transaction-resource.pages.list-project-transactions';
}
