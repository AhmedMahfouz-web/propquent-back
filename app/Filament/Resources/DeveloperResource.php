<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeveloperResource\Pages;
use App\Filament\Resources\DeveloperResource\RelationManagers;
use App\Models\Developer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeveloperResource extends Resource
{
    protected static ?string $model = Developer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Real Estate';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Developer Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255)
                            ->prefix('https://')
                            ->suffixIcon('heroicon-m-globe-alt'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Address & Description')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'undo',
                                'redo',
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope')
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('website')
                    ->searchable()
                    ->icon('heroicon-m-globe-alt')
                    ->url(fn($record) => $record->website)
                    ->openUrlInNewTab()
                    ->toggleable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('projects_count')
                    ->counts('projects')
                    ->label('Projects')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(),

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
                Tables\Filters\Filter::make('has_projects')
                    ->query(fn(Builder $query): Builder => $query->has('projects'))
                    ->label('Has Projects'),

                Tables\Filters\Filter::make('has_email')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('email'))
                    ->label('Has Email'),

                Tables\Filters\Filter::make('has_website')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('website'))
                    ->label('Has Website'),

                Tables\Filters\Filter::make('projects_count')
                    ->form([
                        Forms\Components\TextInput::make('min_projects')
                            ->numeric()
                            ->label('Minimum Projects'),
                        Forms\Components\TextInput::make('max_projects')
                            ->numeric()
                            ->label('Maximum Projects'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_projects'],
                                fn(Builder $query, $count): Builder => $query->has('projects', '>=', $count),
                            )
                            ->when(
                                $data['max_projects'],
                                fn(Builder $query, $count): Builder => $query->has('projects', '<=', $count),
                            );
                    }),
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
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProjectsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevelopers::class,
            'create' => Pages\CreateDeveloper::class,
            'view' => Pages\ViewDeveloper::class,
            'edit' => Pages\EditDeveloper::class,
        ];
    }
}
