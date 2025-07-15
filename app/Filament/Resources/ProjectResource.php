<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['developer', 'media']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('developer_id')
                    ->relationship('developer', 'name')
                    ->required(),
                Forms\Components\TextInput::make('location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_no')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('project')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('area')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('garden_area')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('bedrooms')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('bathrooms')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('floor')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'On-going' => 'On-going',
                        'Exited' => 'Exited',
                    ])
                    ->required(),
                Forms\Components\Select::make('stage')
                    ->options([
                        'Buying' => 'Buying',
                        'Selling' => 'Selling',
                        'Cancelled' => 'Cancelled',
                        'Sold' => 'Sold',
                        'Rented' => 'Rented',
                        'Holding' => 'Holding',
                        'Renovating' => 'Renovating',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('target_1')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('target_2')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DatePicker::make('entry_date'),
                Forms\Components\DatePicker::make('exit_date'),
                Forms\Components\TextInput::make('investment_type')
                    ->required()
                    ->maxLength(255),
                SpatieMediaLibraryFileUpload::make('main_image')
                    ->label('Main Image')
                    ->collection('main_image'),

                SpatieMediaLibraryFileUpload::make('images')
                    ->label('Images')
                    ->collection('images')
                    ->multiple()
                    ->reorderable(),
                Forms\Components\FileUpload::make('document')
                    ->label('Document')
                    ->disk('public')
                    ->directory('project-documents'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('developer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_no')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('garden_area')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bathrooms')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('floor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('stage')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_1')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_2')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('entry_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('exit_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('investment_type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('document')
                    ->searchable()
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'On-going' => 'On-going',
                        'Exited' => 'Exited',
                    ])
                    ->default('On-going'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('key'),
                TextEntry::make('title'),
                TextEntry::make('developer.name'),
                TextEntry::make('location'),
                TextEntry::make('type'),
                TextEntry::make('unit_no'),
                TextEntry::make('project'),
                TextEntry::make('area'),
                TextEntry::make('garden_area'),
                TextEntry::make('bedrooms'),
                TextEntry::make('bathrooms'),
                TextEntry::make('floor'),
                TextEntry::make('status'),
                TextEntry::make('stage'),
                TextEntry::make('target_1'),
                TextEntry::make('target_2'),
                TextEntry::make('entry_date')->date(),
                TextEntry::make('exit_date')->date(),
                TextEntry::make('investment_type'),
                SpatieMediaLibraryImageEntry::make('main_image')
                    ->label('Main Image')
                    ->collection('main_image'),
                SpatieMediaLibraryImageEntry::make('images')->collection('images'),
                TextEntry::make('document'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProjects::route('/'),
        ];
    }
}
