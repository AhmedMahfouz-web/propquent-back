<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Real Estate';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('Project Key')
                            ->placeholder('e.g., PROP-001, VILLA-DUBAI-01')
                            ->helperText('Optional human-readable identifier (1-50 characters, alphanumeric)')
                            ->rules(['unique:projects,key', 'regex:/^[a-zA-Z0-9]{1,50}$/'])
                            ->validationMessages([
                                'unique' => 'This project key is already in use.',
                                'regex' => 'Project key must be 1-50 characters long and contain only letters, numbers.',
                            ]),
                        // ->live(onBlur: true),

                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('developer_id')
                            ->relationship('developer', 'name', fn($query) => $query->active())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('map_location')
                            ->label('Map Location')
                            ->placeholder('Enter map coordinates, address, or location details')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->options(fn() => Project::getAvailablePropertyTypes())
                            ->searchable(),

                        Forms\Components\TextInput::make('unit_no')
                            ->label('Unit Number')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Property Details')
                    ->schema([
                        Forms\Components\TextInput::make('area')
                            ->numeric()
                            ->suffix('sqm')
                            ->step(0.01),

                        Forms\Components\TextInput::make('garden_area')
                            ->label('Garden Area')
                            ->numeric()
                            ->suffix('sqm')
                            ->step(0.01),

                        Forms\Components\TextInput::make('bedrooms')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10),

                        Forms\Components\TextInput::make('bathrooms')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10),

                        Forms\Components\Select::make('status')
                            ->options(fn() => Project::getAvailableStatuses())
                            ->default('on-going')
                            ->required(),

                        Forms\Components\Select::make('stage')
                            ->options(fn() => Project::getAvailableStages())
                            ->default('holding')
                            ->required(),

                        Forms\Components\Select::make('investment_type')
                            ->label('Investment Type')
                            ->options(fn() => Project::getAvailableInvestmentTypes())
                            ->searchable()
                            ->nullable(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Contract Information')
                    ->schema([
                        Forms\Components\DatePicker::make('reservation_date')
                            ->label('Reservation Date')
                            ->displayFormat('d/m/Y'),

                        Forms\Components\DatePicker::make('contract_date')
                            ->label('Contract Date')
                            ->displayFormat('d/m/Y'),

                        Forms\Components\TextInput::make('years_of_installment')
                            ->label('Years of Installment')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->suffix('years'),

                        Forms\Components\TextInput::make('total_contract_value')
                            ->label('Total Contract Value')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Project Images')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                            ->collection('images')
                            ->multiple()
                            ->image()
                            ->maxFiles(10)
                            ->helperText('Upload up to 10 project images.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->height(50)
                    ->width(50)
                    ->circular(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Project Key')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Auto-generated')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('developer.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'apartment',
                        'success' => 'villa',
                        'warning' => 'townhouse',
                        'info' => 'penthouse',
                        'secondary' => 'studio',
                        'danger' => 'duplex',
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('area')
                    ->suffix(' sqm')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('garden_area')
                    ->label('Garden Area')
                    ->suffix(' sqm')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('bedrooms')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('bathrooms')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_contract_value')
                    ->label('Contract Value')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('reservation_date')
                    ->label('Reservation')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('years_of_installment')
                    ->label('Installment Years')
                    ->suffix(' years')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'on-going',
                        'primary' => 'exited',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('stage')
                    ->colors([
                        'secondary' => 'holding',
                        'warning' => 'buying',
                        'info' => 'selling',
                        'success' => 'sold',
                        'primary' => 'rented',
                        'danger' => 'cancelled',
                        'gray' => 'renovation',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('developer')
                    ->relationship('developer', 'name', fn($query) => $query->active())
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options(fn() => Project::getAvailableStatuses()),

                Tables\Filters\Filter::make('has_project_key')
                    ->label('Has Project Key')
                    ->query(fn($query) => $query->whereNotNull('project_key')),

                Tables\Filters\Filter::make('with_investments')
                    ->label('With Investments')
                    ->query(fn($query) => $query->whereHas('clientInvestments')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
            RelationManagers\StatusChangesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
