<?php

namespace App\Filament\Resources\UserTransactionResource\Pages;

use App\Filament\Resources\UserTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserTransaction extends CreateRecord
{
    protected static string $resource = UserTransactionResource::class;
}
