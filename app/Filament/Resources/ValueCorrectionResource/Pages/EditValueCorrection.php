<?php

namespace App\Filament\Resources\ValueCorrectionResource\Pages;

use App\Filament\Resources\ValueCorrectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditValueCorrection extends EditRecord
{
    protected static string $resource = ValueCorrectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
