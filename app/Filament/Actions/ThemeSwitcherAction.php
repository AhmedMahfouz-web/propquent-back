<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Support\Colors\Color;

class ThemeSwitcherAction
{
    public static function make(): Action
    {
        return Action::make('switchTheme')
            ->label('Switch Theme')
            ->icon('heroicon-o-swatch')
            ->modalHeading('Choose Your Theme')
            ->modalDescription('Select a color theme for your dashboard')
            ->modalSubmitActionLabel('Apply Theme')
            ->form([
                Forms\Components\Select::make('theme')
                    ->label('Theme Color')
                    ->options([
                        'blue' => 'Blue',
                        'green' => 'Green',
                        'purple' => 'Purple',
                        'orange' => 'Orange',
                        'red' => 'Red',
                        'indigo' => 'Indigo',
                        'pink' => 'Pink',
                        'teal' => 'Teal',
                    ])
                    ->default(auth('admins')->user()?->theme_color ?? 'blue')
                    ->required()
                    ->native(false),
            ])
            ->action(function (array $data) {
                $user = auth('admins')->user();
                if ($user) {
                    $user->update(['theme_color' => $data['theme']]);

                    \Filament\Notifications\Notification::make()
                        ->title('Theme Updated')
                        ->body("Theme switched to {$data['theme']}. Refresh the page to see changes.")
                        ->success()
                        ->send();
                }
            });
    }
}
