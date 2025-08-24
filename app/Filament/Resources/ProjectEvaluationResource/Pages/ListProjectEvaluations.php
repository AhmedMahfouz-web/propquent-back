<?php

namespace App\Filament\Resources\ProjectEvaluationResource\Pages;

use App\Filament\Resources\ProjectEvaluationResource;
use App\Imports\ProjectEvaluationImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class ListProjectEvaluations extends ListRecords
{
    protected static string $resource = ProjectEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Import Evaluations')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label('Excel File')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required()
                        ->helperText('Upload an Excel file (.xlsx or .xls) with project evaluation data. Download the template for the required format.')
                ])
                ->action(function (array $data) {
                    try {
                        $import = new ProjectEvaluationImport();
                        $filePath = storage_path('app/public/' . $data['file']);
                        Excel::import($import, $filePath);
                        
                        Notification::make()
                            ->title('Project evaluations imported successfully!')
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import failed')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(asset('templates/project-evaluations-template.xlsx'))
                ->openUrlInNewTab(),
            Actions\Action::make('bulk_edit')
                ->label('Bulk Edit')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(static::getResource()::getUrl('bulk-edit')),
        ];
    }
}
