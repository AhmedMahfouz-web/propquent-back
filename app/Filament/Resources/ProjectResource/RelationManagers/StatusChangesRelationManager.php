<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StatusChangesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusChanges';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('from_status')
                    ->options([
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'reserved' => 'Reserved',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
                
                Forms\Components\Select::make('to_status')
                    ->options([
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'reserved' => 'Reserved',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
                
                Forms\Components\Textarea::make('reason')
                    ->maxLength(65535)
                    ->nullable(),
                
                Forms\Components\Select::make('changed_by')
                    ->relationship('changedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Changed By Admin'),
                
                Forms\Components\DateTimePicker::make('changed_at')
                    ->required()
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('from_status')
            ->columns([
                Tables\Columns\BadgeColumn::make('from_status')
                    ->colors([
                        'success' => 'available',
                        'primary' => 'sold',
                        'warning' => 'reserved',
                        'danger' => 'cancelled',
                    ])
                    ->label('From'),
                
                Tables\Columns\TextColumn::make('arrow')
                    ->label('')
                    ->formatStateUsing(fn () => '→')
                    ->alignCenter(),
                
                Tables\Columns\BadgeColumn::make('to_status')
                    ->colors([
                        'success' => 'available',
                        'primary' => 'sold',
                        'warning' => 'reserved',
                        'danger' => 'cancelled',
                    ])
                    ->label('To'),
                
                Tables\Columns\TextColumn::make('reason')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                
                Tables\Columns\TextColumn::make('changedBy.name')
                    ->label('Changed By')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('changed_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('from_status')
                    ->options([
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'reserved' => 'Reserved',
                        'cancelled' => 'Cancelled',
                    ]),
                
                Tables\Filters\SelectFilter::make('to_status')
                    ->options([
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'reserved' => 'Reserved',
                        'cancelled' => 'Cancelled',
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
            ])
            ->defaultSort('changed_at', 'desc');
    }
}
