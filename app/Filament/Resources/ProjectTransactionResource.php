<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectTransactionResource\Pages;
use App\Filament\Resources\ProjectTransactionResource\RelationManagers;
use App\Models\ProjectTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectTransactionResource extends Resource
{
    protected static ?string $model = ProjectTransaction::class;

    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Transactions';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('what');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('project_key')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'Expense' => 'Expense',
                        'Revenue' => 'Revenue',
                    ])
                    ->required(),
                Forms\Components\Select::make('serving')
                    ->options([
                        'Asset' => 'Asset',
                        'Operation' => 'Operation',
                    ])
                    ->required(),
                Forms\Components\Select::make('what_id')
                    ->relationship('what', 'id')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('due_date'),
                Forms\Components\DateTimePicker::make('actual_date'),
                Forms\Components\DateTimePicker::make('transaction_date')
                    ->required(),
                Forms\Components\Select::make('method')
                    ->options([
                        'Cheque' => 'Cheque',
                        'Bank Transfer' => 'Bank Transfer',
                        'Cash' => 'Cash',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('reference_no')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('status')
                    ->options([
                        'Done' => 'Done',
                        'Pending' => 'Pending',
                        'Canceled' => 'Canceled',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project_key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'danger' => 'Expense',
                        'success' => 'Revenue',
                    ])
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('serving')
                    ->colors([
                        'primary' => 'Asset',
                        'info' => 'Operation',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('what.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('method')
                    ->colors([
                        'primary' => 'Cheque',
                        'secondary' => 'Bank Transfer',
                        'info' => 'Cash',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_no')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'Done',
                        'warning' => 'Pending',
                        'danger' => 'Canceled',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectTransactions::route('/'),
        ];
    }
}
