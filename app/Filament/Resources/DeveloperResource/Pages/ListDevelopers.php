<?php

namespace App\Filament\Resources\DeveloperResource\Pages;

use App\Filament\Resources\DeveloperResource;
use App\Imports\DeveloperImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class ListDevelopers extends ListRecords
{
    protected static string $resource = DeveloperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Import Developers')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label('Excel File')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required()
                        ->helperText('Upload an Excel file (.xlsx or .xls) with developer data. Download the template for the required format.')
                ])
                ->action(function (array $data) {
                    try {
                        $import = new DeveloperImport();
                        $filePath = storage_path('app/public/' . $data['file']);
                        Excel::import($import, $filePath);
                        
                        Notification::make()
                            ->title('Developers imported successfully!')
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
                ->url(asset('templates/developers-template.xlsx'))
                ->openUrlInNewTab(),
        ];
    }
}
