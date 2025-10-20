<?php

namespace App\Filament\Resources\UserTransactionResource\Pages;

use App\Filament\Resources\UserTransactionResource;
use App\Models\SystemConfiguration;
use App\Models\User;
use App\Models\UserTransaction;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserTransaction extends CreateRecord
{
    protected static string $resource = UserTransactionResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('debug_info')
                ->label('Debug Info')
                ->color('warning')
                ->action(function () {
                    // This will show debug info in notifications
                    $userCount = User::count();
                    $statuses = UserTransaction::getAvailableStatuses();
                    $methods = UserTransaction::getAvailableMethods();
                    $types = UserTransaction::getAvailableTransactionTypes();
                    
                    $debugInfo = "Users: {$userCount} | ";
                    $debugInfo .= "Statuses: " . count($statuses) . " (" . implode(', ', array_keys($statuses)) . ") | ";
                    $debugInfo .= "Methods: " . count($methods) . " (" . implode(', ', array_keys($methods)) . ") | ";
                    $debugInfo .= "Types: " . count($types) . " (" . implode(', ', array_keys($types)) . ")";
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Debug Information')
                        ->body($debugInfo)
                        ->persistent()
                        ->send();
                })
        ];
    }
}
