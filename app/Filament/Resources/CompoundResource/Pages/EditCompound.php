<?php

namespace App\Filament\Resources\CompoundResource\Pages;

use App\Filament\Resources\CompoundResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompound extends EditRecord
{
    protected static string $resource = CompoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
