<?php

namespace App\Filament\Resources\DeveloperResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                
                Forms\Components\Select::make('type')
                    ->options([
                        'apartment' => 'Apartment',
                        'villa' => 'Villa',
                        'townhouse' => 'Townhouse',
                        'penthouse' => 'Penthouse',
                        'studio' => 'Studio',
                        'duplex' => 'Duplex',
                    ])
                    ->searchable(),
                
                Forms\Components\TextInput::make('unit_no')
                    ->label('Unit Number')
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('area')
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
                    ->options([
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'reserved' => 'Reserved',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('available')
                    ->required(),
                
                Forms\Components\Select::make('stage')
                    ->options([
                        'planning' => 'Planning',
                        'construction' => 'Construction',
                        'completed' => 'Completed',
                        'delivered' => 'Delivered',
                    ])
                    ->default('planning')
                    ->required(),
                
                Forms\Components\DatePicker::make('entry_date'),
                
                Forms\Components\DatePicker::make('exit_date'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
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
                    ]),
                
                Tables\Columns\TextColumn::make('area')
                    ->suffix(' sqm')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('bedrooms')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('bathrooms')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'available',
                        'primary' => 'sold',
                        'warning' => 'reserved',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('stage')
                    ->colors([
                        'secondary' => 'planning',
                        'warning' => 'construction',
                        'success' => 'completed',
                        'primary' => 'delivered',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label('Transactions')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'apartment' => 'Apartment',
                        'villa' => 'Villa',
                        'townhouse' => 'Townhouse',
                        'penthouse' => 'Penthouse',
                        'studio' => 'Studio',
                        'duplex' => 'Duplex',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'reserved' => 'Reserved',
                        'cancelled' => 'Cancelled',
                    ]),
                
                Tables\Filters\SelectFilter::make('stage')
                    ->options([
                        'planning' => 'Planning',
                        'construction' => 'Construction',
                        'completed' => 'Completed',
                        'delivered' => 'Delivered',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
