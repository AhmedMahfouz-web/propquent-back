<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemConfigurationResource\Pages;
use App\Filament\Resources\SystemConfigurationResource\RelationManagers;
use App\Models\SystemConfiguration;
use App\Services\ConfigurationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class SystemConfigurationResource extends Resource
{
    protected static ?string $model = SystemConfiguration::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $modelLabel = 'Setting';

    protected static ?string $pluralModelLabel = 'Settings';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuration Details')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->options([
                                'project_statuses' => 'Project Statuses',
                                'project_stages' => 'Project Stages',
                                'project_targets' => 'Project Targets',
                                'property_types' => 'Property Types',
                                'investment_types' => 'Investment Types',
                                'transaction_types' => 'Transaction Types',
                                'transaction_statuses' => 'Transaction Statuses',
                                'transaction_serving' => 'Transaction Serving',
                                'transaction_methods' => 'Transaction Methods',
                            ])
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                // Auto-generate key from label when category changes
                                $set('key', '');
                            }),

                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->unique(SystemConfiguration::class, 'key', ignoreRecord: true)
                            ->rules(['alpha_dash'])
                            ->helperText('Unique identifier (letters, numbers, dashes, underscores only)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                                if ($state && !$get('value')) {
                                    $set('value', $state);
                                }
                            }),

                        Forms\Components\TextInput::make('value')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The actual value stored in the database'),

                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Human-readable label displayed in forms')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                                if ($state && !$get('key')) {
                                    $key = strtolower(str_replace([' ', '-'], '_', $state));
                                    $key = preg_replace('/[^a-z0-9_]/', '', $key);
                                    $set('key', $key);
                                    if (!$get('value')) {
                                        $set('value', $key);
                                    }
                                }
                            }),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->helperText('Optional description of this configuration option')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive configurations will not appear in dropdowns')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first in lists'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->colors([
                        'primary' => 'project_statuses',
                        'success' => 'project_stages',
                        'warning' => 'transaction_types',
                        'info' => 'property_types',
                        'secondary' => 'investment_types',
                    ])
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('value')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'project_statuses' => 'Project Statuses',
                        'project_stages' => 'Project Stages',
                        'project_targets' => 'Project Targets',
                        'property_types' => 'Property Types',
                        'investment_types' => 'Investment Types',
                        'transaction_types' => 'Transaction Types',
                        'transaction_statuses' => 'Transaction Statuses',
                        'transaction_serving' => 'Transaction Serving',
                        'transaction_methods' => 'Transaction Methods',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All configurations')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('primary'),

                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, SystemConfiguration $record) {
                        $service = new ConfigurationService();

                        if (!$service->canDeleteConfiguration($record->id)) {
                            Notification::make()
                                ->title('Cannot Delete Configuration')
                                ->body('This configuration is currently in use and cannot be deleted.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            $service = new ConfigurationService();
                            $cannotDelete = [];

                            foreach ($records as $record) {
                                if (!$service->canDeleteConfiguration($record->id)) {
                                    $cannotDelete[] = $record->label;
                                }
                            }

                            if (!empty($cannotDelete)) {
                                Notification::make()
                                    ->title('Cannot Delete Some Configurations')
                                    ->body('The following configurations are in use: ' . implode(', ', $cannotDelete))
                                    ->danger()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ])
            ->defaultSort('category')
            ->defaultSort('sort_order')
            ->groups([
                Tables\Grouping\Group::make('category')
                    ->label('Category')
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemConfigurations::route('/'),
            'create' => Pages\CreateSystemConfiguration::route('/create'),
            'edit' => Pages\EditSystemConfiguration::route('/{record}/edit'),
        ];
    }
}
