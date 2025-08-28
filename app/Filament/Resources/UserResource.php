<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('phone_number')
                            ->tel()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('country')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Authentication')
                    ->schema([
                        Forms\Components\TextInput::make('password_hash')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('auth_provider')
                            ->options([
                                'local' => 'Local',
                                'google' => 'Google',
                                'facebook' => 'Facebook',
                                'twitter' => 'Twitter',
                            ])
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('provider_user_id')
                            ->maxLength(255)
                            ->nullable(),
                        
                        Forms\Components\Toggle::make('email_verified')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        
                        Forms\Components\DateTimePicker::make('last_login_at')
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Profile & Preferences')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_picture_url')
                            ->label('Profile Picture')
                            ->image()
                            ->directory('profile-pictures')
                            ->nullable(),
                        
                        Forms\Components\ColorPicker::make('theme_color')
                            ->nullable(),
                        
                        Forms\Components\ColorPicker::make('custom_theme_color')
                            ->nullable(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_picture_url')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn (): string => 'https://ui-avatars.com/api/?name=User&color=7F9CF5&background=EBF4FF'),
                
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('auth_provider')
                    ->colors([
                        'primary' => 'local',
                        'success' => 'google',
                        'info' => 'facebook',
                        'warning' => 'twitter',
                    ])
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('email_verified')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label('Transactions')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('referrals_count')
                    ->counts('referrals')
                    ->label('Referrals')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('last_login_at')
                    ->dateTime()
                    ->sortable()
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
                Tables\Filters\SelectFilter::make('auth_provider')
                    ->options([
                        'local' => 'Local',
                        'google' => 'Google',
                        'facebook' => 'Facebook',
                        'twitter' => 'Twitter',
                    ]),
                
                Tables\Filters\TernaryFilter::make('email_verified')
                    ->label('Email Verified')
                    ->boolean()
                    ->trueLabel('Verified only')
                    ->falseLabel('Unverified only')
                    ->native(false),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),
                
                Tables\Filters\Filter::make('has_transactions')
                    ->query(fn (Builder $query): Builder => $query->has('transactions'))
                    ->label('Has Transactions'),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
            RelationManagers\ReferralsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
