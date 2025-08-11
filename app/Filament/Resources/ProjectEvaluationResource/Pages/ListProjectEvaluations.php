<?php

namespace App\Filament\Resources\ProjectEvaluationResource\Pages;

use App\Filament\Resources\ProjectEvaluationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectEvaluations extends ListRecords
{
    protected static string $resource = ProjectEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('bulk_edit')
                ->label('Bulk Edit')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(static::getResource()::getUrl('bulk-edit')),
        ];
    }
}
