<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectTransactionResource\Pages;
use App\Models\ProjectTransaction;
use App\Imports\ProjectTransactionSheetImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ImportAction;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Artisan;

class ProjectTransactionResource extends Resource
{
    protected static ?string $model = ProjectTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Project Transactions';

    protected static ?string $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\Select::make('project_key')
                            ->label('Project')
                            ->options(function () {
                                return \App\Models\Project::with('developer')
                                    ->get()
                                    ->mapWithKeys(function ($project) {
                                        return [$project->key => "{$project->title} ({$project->developer->name})"];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->columnSpanFull()
                            ->default(function () {
                                if (request()->has('project_id')) {
                                    $project = \App\Models\Project::find(request('project_id'));
                                    return $project?->key;
                                }
                                return null;
                            }),

                        Forms\Components\Select::make('financial_type')
                            ->label('Financial Type')
                            ->options(fn() => ProjectTransaction::getAvailableFinancialTypes())
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('serving')
                            ->options(fn() => ProjectTransaction::getAvailableServingTypes())
                            ->nullable(),

                        Forms\Components\Select::make('what')
                            ->label('What (Purpose)')
                            ->options(fn() => ProjectTransaction::getAvailableWhatTypes())
                            ->searchable()
                            ->nullable(),

                        Forms\Components\TextInput::make('transaction_category')
                            ->label('Transaction Category')
                            ->maxLength(255)
                            ->placeholder('Enter transaction category')
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Financial Information')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required()
                            ->rules(['min:0.01']),

                        Forms\Components\Select::make('method')
                            ->options(fn() => ProjectTransaction::getAvailableTransactionMethods())
                            ->searchable()
                            ->nullable(),

                        Forms\Components\TextInput::make('reference_no')
                            ->label('Reference Number')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->options(fn() => ProjectTransaction::getAvailableStatuses())
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Date Information')
                    ->schema([
                        Forms\Components\TextInput::make('due_date')
                            ->placeholder('YYYY-MM-DD')
                            ->rules(['nullable', 'date_format:Y-m-d'])
                            ->nullable(),

                        Forms\Components\TextInput::make('actual_date')
                            ->placeholder('YYYY-MM-DD')
                            ->rules(['nullable', 'date_format:Y-m-d'])
                            ->nullable(),

                        Forms\Components\TextInput::make('transaction_date')
                            ->placeholder('YYYY-MM-DD')
                            ->rules(['required', 'date_format:Y-m-d'])
                            ->required()
                            ->default(today()->format('Y-m-d')),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('note')
                            ->maxLength(65535)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->extremePaginationLinks()
            ->columns([
                Tables\Columns\TextColumn::make('project_key')
                    ->label('Project Key')
                    ->sortable()
                    ->searchable()
                    ->width(200)
                    ->copyable(),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project Name')
                    ->sortable()
                    ->searchable()
                    ->width(250)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('project.developer.name')
                    ->label('Developer')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('financial_type')
                    ->colors([
                        'success' => 'revenue',
                        'danger' => 'expense',
                    ])
                    ->sortable()
                    ->width(120),

                Tables\Columns\TextColumn::make('amount')
                    ->money('EGP')
                    ->sortable()
                    ->width(120)
                    ->alignEnd(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->sortable()
                    ->width(120),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable()
                    ->width(150),

                Tables\Columns\TextColumn::make('serving')
                    ->sortable()
                    ->width(120)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('what')
                    ->sortable()
                    ->width(150)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('method')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reference_no')
                    ->label('Reference')
                    ->sortable()
                    ->width(150)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->width(150)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('actual_date')
                    ->date()
                    ->sortable()
                    ->width(150)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('note')
                    ->limit(50)
                    ->sortable()
                    ->width(200)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('transaction_category')
                    ->label('Category')
                    ->sortable()
                    ->width(150)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'title')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->key} - {$record->title}"),


                Tables\Filters\SelectFilter::make('financial_type')
                    ->options(fn() => ProjectTransaction::getAvailableFinancialTypes()),

                Tables\Filters\SelectFilter::make('serving')
                    ->options(fn() => ProjectTransaction::getAvailableServingTypes()),

                Tables\Filters\SelectFilter::make('what')
                    ->label('What (Purpose)')
                    ->options(fn() => ProjectTransaction::getAvailableWhatTypes()),

                Tables\Filters\SelectFilter::make('status')
                    ->options(fn() => ProjectTransaction::getAvailableStatuses()),

                Tables\Filters\SelectFilter::make('method')
                    ->options(fn() => ProjectTransaction::getAvailableTransactionMethods()),

                Tables\Filters\Filter::make('amount_range')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('amount_to')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn(Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn(Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    }),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add New Row')
                    ->keyBindings(['ctrl+n', 'cmd+n']),
                Tables\Actions\Action::make('clear_today')
                    ->label('Clear today\'s Imports')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        $deleted = ProjectTransaction::whereDate('created_at', today())->delete();
                        \Filament\Notifications\Notification::make()
                            ->title('Cleared Successfully')
                            ->body("Deleted {$deleted} transactions imported today.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('import')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->directory('imports')
                    ])
                    ->action(function (array $data) {
                        try {
                            $filePath = storage_path('app/public/' . $data['file']);
                            $import = new ProjectTransactionSheetImport;

                            Excel::import($import, $filePath);

                            \Filament\Notifications\Notification::make()
                                ->title('Import Process Complete')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Import Failed')
                                ->body('Error: ' . $e->getMessage() . '\nFile: ' . ($filePath ?? 'unknown'))
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('downloadTemplate')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        // Generate fresh template with current data
                        \Artisan::call('template:project-transactions');

                        // Return the generated file for download
                        $templatePath = public_path('templates/project-transactions-template.xlsx');

                        if (file_exists($templatePath)) {
                            return response()->download($templatePath, 'project-transactions-template-' . date('Y-m-d') . '.xlsx');
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Error')
                                ->body('Failed to generate template. Please try again.')
                                ->danger()
                                ->send();
                        }
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning'),


                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-m-trash'),
            ])
            ->recordUrl(null) // Disable row click navigation to allow inline editing
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
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
            'create' => Pages\CreateProjectTransaction::route('/create'),
            'view' => Pages\ViewProjectTransaction::route('/{record}'),
            'edit' => Pages\EditProjectTransaction::route('/{record}/edit'),
        ];
    }
}
