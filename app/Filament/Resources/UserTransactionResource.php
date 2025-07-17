<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserTransactionResource\Pages;
use App\Models\User;
use App\Models\UserTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserTransactionResource extends Resource
{
    protected static ?string $model = UserTransaction::class;

    protected static ?int $navigationSort = 5;
    protected static ?string $navigationGroup = 'Transactions';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'Deposit' => 'Deposit',
                        'Withdraw' => 'Withdraw',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('USD'),
                Forms\Components\DatePicker::make('transaction_date')
                    ->default(now())
                    ->required(),
                Forms\Components\DatePicker::make('actual_date'),
                Forms\Components\Select::make('method')
                    ->options([
                        'Cheque' => 'Cheque',
                        'Bank Transfer' => 'Bank Transfer',
                        'Cash' => 'Cash',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('reference_no')
                    ->maxLength(255),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'Done' => 'Done',
                        'Pending' => 'Pending',
                        'Canceled' => 'Canceled',
                    ])
                    ->default('Pending')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.full_name')
                    ->searchable()
                    ->sortable()
                    ->tooltip('View user profile')
                    ->extraAttributes([
                        'class' => 'modern-hover cursor-pointer',
                    ])
                    ->action(
                        Action::make('view_user')
                            ->label('') // This makes the column text the action trigger
                            ->modalHeading('User Profile')
                            ->modalContent(function (UserTransaction $record) {
                                $user = $record->user;

                                // Calculate Net Deposit efficiently using single query with aggregation
                                $netDepositData = UserTransaction::where('user_id', $user->id)
                                    ->where('status', 'Done')
                                    ->selectRaw('SUM(CASE WHEN type = "Deposit" THEN amount ELSE 0 END) as total_deposits')
                                    ->selectRaw('SUM(CASE WHEN type = "Withdraw" THEN amount ELSE 0 END) as total_withdrawals')
                                    ->first();
                                $netDeposit = ($netDepositData->total_deposits ?? 0) - ($netDepositData->total_withdrawals ?? 0);

                                // Get Last 5 Transactions efficiently with limited fields
                                $lastTransactions = $user->transactions()
                                    ->select('id', 'type', 'amount', 'status', 'transaction_date', 'description')
                                    ->latest('transaction_date')
                                    ->limit(5)
                                    ->get();

                                return Infolist::make()
                                    ->record($user)
                                    ->schema([
                                        Section::make()->schema([
                                            Grid::make(2)->schema([
                                                ImageEntry::make('profile_picture_url')
                                                    ->label('')
                                                    ->circular()
                                                    ->defaultImageUrl(url('/images/default-avatar.png')),
                                                Grid::make(2)->schema([
                                                    TextEntry::make('full_name')
                                                        ->weight('bold')
                                                        ->size('lg'),
                                                    TextEntry::make('email')
                                                        ->icon('heroicon-o-envelope'),
                                                    TextEntry::make('is_active')
                                                        ->label('Status')
                                                        ->badge()
                                                        ->color(fn(bool $state) => $state ? 'success' : 'danger')
                                                        ->formatStateUsing(fn(bool $state) => $state ? 'Active' : 'Inactive'),
                                                    TextEntry::make('net_deposit')
                                                        ->label('Net Deposit')
                                                        ->money('usd')
                                                        ->state($netDeposit)
                                                        ->icon('heroicon-o-banknotes')
                                                        ->color($netDeposit >= 0 ? 'success' : 'danger'),
                                                ])
                                            ])
                                        ]),
                                        Section::make('Additional Information')
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    TextEntry::make('phone_number')->icon('heroicon-o-phone'),
                                                    TextEntry::make('country')->icon('heroicon-o-flag'),
                                                    TextEntry::make('last_login_at')->dateTime()->icon('heroicon-o-clock'),
                                                    TextEntry::make('created_at')->label('Joined On')->date()->icon('heroicon-o-calendar-days'),
                                                ]),
                                            ])->columns(2),
                                        Section::make('Last 5 Transactions')
                                            ->schema([
                                                Grid::make(1)->schema([
                                                    ViewEntry::make('last_transactions')
                                                        ->label('')
                                                        ->view('infolists.components.last-transactions-list', ['transactions' => $lastTransactions])
                                                ])
                                            ])
                                    ]);
                            })
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                    ),
                TextColumn::make('type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Deposit' => 'success',
                        'Withdraw' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('amount')
                    ->money('usd')
                    ->sortable(),
                TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('method')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Cheque' => 'gray',
                        'Bank Transfer' => 'info',
                        'Cash' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Done' => 'success',
                        'Pending' => 'warning',
                        'Canceled' => 'dang er',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListUserTransactions::route('/'),
        ];
    }
}
