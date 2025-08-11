<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectEvaluationResource\Pages;
use App\Filament\Resources\ProjectEvaluationResource\RelationManagers;
use App\Models\ProjectEvaluation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectEvaluationResource extends Resource
{
    protected static ?string $model = ProjectEvaluation::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Project Evaluations';
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?int $navigationSort = 3;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_key')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('evaluation_date')
                    ->label('Evaluation Month')
                    ->displayFormat('M Y')
                    ->format('Y-m-01')
                    ->default(now()->startOfMonth())
                    ->required()
                    ->helperText('Select the first day of the month for evaluation'),

                Forms\Components\TextInput::make('evaluation_amount')
                    ->label('Evaluation Amount')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('evaluation_date')
                    ->label('Month')
                    ->date('M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('evaluation_amount')
                    ->label('Evaluation Amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_key')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('evaluation_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Month'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Month'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('evaluation_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('evaluation_date', '<=', $date),
                            );
                    }),
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
            'index' => Pages\ListProjectEvaluations::route('/'),
            'create' => Pages\CreateProjectEvaluation::route('/create'),
            'edit' => Pages\EditProjectEvaluation::route('/{record}/edit'),
            'bulk-edit' => Pages\BulkProjectEvaluations::route('/bulk-edit'),
        ];
    }
}
