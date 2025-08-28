<?php

namespace App\Filament\Pages;

use App\Models\SystemConfiguration;
use App\Services\ConfigurationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class SystemSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Settings';

    protected static ?int $navigationSort = 99;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.system-settings';

    public static function canAccess(): bool
    {
        $user = auth('admins')->user();
        return $user && in_array($user->role, ['super_admin', 'admin']);
    }

    public ?array $projectStatusesData = [];
    public ?array $projectStagesData = [];
    public ?array $projectTargetsData = [];
    public ?array $propertyTypesData = [];
    public ?array $investmentTypesData = [];
    public ?array $transactionTypesData = [];
    public ?array $transactionStatusesData = [];
    public ?array $transactionServingData = [];
    public ?array $transactionMethodsData = [];

    public string $activeTab = 'general';

    public ?array $generalSettingsData = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refreshData')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->loadConfigurationData();
                    Notification::make()
                        ->title('Data Refreshed')
                        ->body('Configuration data has been reloaded from the database.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('clearCache')
                ->label('Clear Cache')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Clear Configuration Cache')
                ->modalDescription('This will clear all cached configuration data. Are you sure?')
                ->action(function () {
                    Cache::flush();
                    Notification::make()
                        ->title('Cache Cleared')
                        ->body('System configuration cache has been cleared successfully.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('exportConfig')
                ->label('Export Settings')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    $configs = SystemConfiguration::all()->groupBy('category');
                    $export = $configs->toArray();

                    Notification::make()
                        ->title('Export Ready')
                        ->body('Configuration export functionality would be implemented here.')
                        ->info()
                        ->send();
                }),
        ];
    }

    public function mount(): void
    {
        $this->loadConfigurationData();
    }

    protected function loadConfigurationData(): void
    {
        $categories = [
            'project_statuses' => 'projectStatusesData',
            'project_stages' => 'projectStagesData',
            'project_targets' => 'projectTargetsData',
            'property_types' => 'propertyTypesData',
            'investment_types' => 'investmentTypesData',
            'transaction_types' => 'transactionTypesData',
            'transaction_statuses' => 'transactionStatusesData',
            'transaction_serving' => 'transactionServingData',
            'transaction_methods' => 'transactionMethodsData',
        ];

        foreach ($categories as $category => $property) {
            $configs = SystemConfiguration::byCategory($category)->ordered()->get();
            $this->{$property} = $configs->map(function ($config) {
                return [
                    'id' => $config->id,
                    'key' => $config->key,
                    'value' => $config->value,
                    'label' => $config->label,
                    'description' => $config->description,
                    'is_active' => $config->is_active,
                    'sort_order' => $config->sort_order,
                ];
            })->toArray();
        }

        // Load general settings
        $this->loadGeneralSettings();
    }

    protected function loadGeneralSettings(): void
    {
        $user = auth('admins')->user();
        $this->generalSettingsData = [
            'theme_color' => $user?->theme_color ?? 'blue',
            'default_currency' => 'USD',
            'date_format' => 'Y-m-d',
            'timezone' => config('app.timezone', 'UTC'),
        ];
    }

    protected function createFormForCategory(string $dataProperty, string $label): Form
    {
        return $this->makeForm()
            ->schema([
                Forms\Components\Repeater::make($dataProperty)
                    ->label($label)
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->disabled(fn($state) => !empty($state))
                            ->rules(['alpha_dash', 'max:255'])
                            ->unique(SystemConfiguration::class, 'key', ignoreRecord: true)
                            ->helperText('Unique identifier (auto-generated for new items)'),
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                                if ($state && empty($get('key'))) {
                                    $key = strtolower(str_replace([' ', '-'], '_', $state));
                                    $key = preg_replace('/[^a-z0-9_]/', '', $key);
                                    $set('key', $key);
                                    $set('value', $key);
                                }
                            }),
                        Forms\Components\TextInput::make('value')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Database value'),
                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Display order'),
                    ])
                    ->columns(3)
                    ->reorderable('sort_order')
                    ->collapsible()
                    ->itemLabel(fn(array $state): ?string => $state['label'] ?? 'New Item')
                    ->addActionLabel('Add New Item')
                    ->deleteAction(
                        fn(Forms\Components\Actions\Action $action) => $action
                            ->requiresConfirmation()
                            ->before(function (array $arguments, Forms\Components\Repeater $component) {
                                $items = $component->getState();
                                $item = $items[$arguments['item']] ?? null;

                                if ($item && isset($item['id'])) {
                                    $service = new ConfigurationService();
                                    if (!$service->canDeleteConfiguration($item['id'])) {
                                        Notification::make()
                                            ->title('Cannot Delete')
                                            ->body('This configuration is currently in use and cannot be deleted.')
                                            ->danger()
                                            ->send();
                                        $action->cancel();
                                    }
                                }
                            })
                    ),
            ])
            ->statePath($dataProperty);
    }

    protected function saveCategory(string $dataProperty, string $category, string $title): void
    {
        try {
            $this->validate();

            $existingIds = [];
            $service = new ConfigurationService();

            foreach ($this->{$dataProperty} as $data) {
                if (isset($data['id']) && $data['id']) {
                    // Update existing configuration
                    $config = SystemConfiguration::find($data['id']);
                    if ($config) {
                        $config->update([
                            'label' => $data['label'],
                            'value' => $data['value'] ?? $data['key'],
                            'description' => $data['description'] ?? null,
                            'is_active' => $data['is_active'] ?? true,
                            'sort_order' => $data['sort_order'] ?? 0,
                        ]);
                        $existingIds[] = $data['id'];
                    }
                } else {
                    // Create new configuration
                    if (!empty($data['key']) && !empty($data['label'])) {
                        $config = SystemConfiguration::create([
                            'category' => $category,
                            'key' => $data['key'],
                            'value' => $data['value'] ?? $data['key'],
                            'label' => $data['label'],
                            'description' => $data['description'] ?? null,
                            'is_active' => $data['is_active'] ?? true,
                            'sort_order' => $data['sort_order'] ?? 0,
                        ]);
                        $existingIds[] = $config->id;
                    }
                }
            }

            // Handle deletions (items that were removed from the form)
            $currentConfigs = SystemConfiguration::byCategory($category)->get();
            foreach ($currentConfigs as $config) {
                if (!in_array($config->id, $existingIds)) {
                    if ($service->canDeleteConfiguration($config->id)) {
                        $config->delete();
                    }
                }
            }

            $service->clearCategoryCache($category);

            // Reload data to reflect changes
            $this->loadConfigurationData();

            Notification::make()
                ->title($title . ' Updated Successfully')
                ->body('All changes have been saved and applied.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Saving ' . $title)
                ->body('An error occurred while saving: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Project Status Methods
    public function getProjectStatusesForm(): Form
    {
        return $this->createFormForCategory('projectStatusesData', 'Project Statuses');
    }

    public function saveProjectStatuses(): void
    {
        $this->saveCategory('projectStatusesData', 'project_statuses', 'Project Statuses');
    }

    // Project Stages Methods
    public function getProjectStagesForm(): Form
    {
        return $this->createFormForCategory('projectStagesData', 'Project Stages');
    }

    public function saveProjectStages(): void
    {
        $this->saveCategory('projectStagesData', 'project_stages', 'Project Stages');
    }

    // Project Targets Methods
    public function getProjectTargetsForm(): Form
    {
        return $this->createFormForCategory('projectTargetsData', 'Project Targets');
    }

    public function saveProjectTargets(): void
    {
        $this->saveCategory('projectTargetsData', 'project_targets', 'Project Targets');
    }

    // Property Types Methods
    public function getPropertyTypesForm(): Form
    {
        return $this->createFormForCategory('propertyTypesData', 'Property Types');
    }

    public function savePropertyTypes(): void
    {
        $this->saveCategory('propertyTypesData', 'property_types', 'Property Types');
    }

    // Investment Types Methods
    public function getInvestmentTypesForm(): Form
    {
        return $this->createFormForCategory('investmentTypesData', 'Investment Types');
    }

    public function saveInvestmentTypes(): void
    {
        $this->saveCategory('investmentTypesData', 'investment_types', 'Investment Types');
    }

    // Transaction Types Methods
    public function getTransactionTypesForm(): Form
    {
        return $this->createFormForCategory('transactionTypesData', 'Transaction Types');
    }

    public function saveTransactionTypes(): void
    {
        $this->saveCategory('transactionTypesData', 'transaction_types', 'Transaction Types');
    }

    // Transaction Statuses Methods
    public function getTransactionStatusesForm(): Form
    {
        return $this->createFormForCategory('transactionStatusesData', 'Transaction Statuses');
    }

    public function saveTransactionStatuses(): void
    {
        $this->saveCategory('transactionStatusesData', 'transaction_statuses', 'Transaction Statuses');
    }

    // Transaction Serving Methods
    public function getTransactionServingForm(): Form
    {
        return $this->createFormForCategory('transactionServingData', 'Transaction Serving Types');
    }

    public function saveTransactionServing(): void
    {
        $this->saveCategory('transactionServingData', 'transaction_serving', 'Transaction Serving Types');
    }

    // Transaction Methods Methods
    public function getTransactionMethodsForm(): Form
    {
        return $this->createFormForCategory('transactionMethodsData', 'Transaction Methods');
    }

    public function saveTransactionMethods(): void
    {
        $this->saveCategory('transactionMethodsData', 'transaction_methods', 'Transaction Methods');
    }

    // General Settings Methods
    public function getGeneralSettingsForm(): Form
    {
        return $this->makeForm()
            ->schema([
                Forms\Components\Section::make('Theme Settings')
                    ->schema([
                        Forms\Components\Select::make('theme_color')
                            ->label('Theme Color')
                            ->options([
                                'blue' => 'Blue',
                                'green' => 'Green',
                                'purple' => 'Purple',
                                'orange' => 'Orange',
                                'red' => 'Red',
                                'indigo' => 'Indigo',
                                'pink' => 'Pink',
                                'teal' => 'Teal',
                            ])
                            ->default('blue')
                            ->required()
                            ->native(false)
                            ->helperText('Choose your preferred color theme'),
                    ]),

                Forms\Components\Section::make('System Preferences')
                    ->schema([
                        Forms\Components\TextInput::make('default_currency')
                            ->label('Default Currency')
                            ->default('USD')
                            ->maxLength(3)
                            ->helperText('Default currency code (e.g., USD, EUR)'),

                        Forms\Components\Select::make('date_format')
                            ->label('Date Format')
                            ->options([
                                'Y-m-d' => 'YYYY-MM-DD (2024-01-15)',
                                'd/m/Y' => 'DD/MM/YYYY (15/01/2024)',
                                'm/d/Y' => 'MM/DD/YYYY (01/15/2024)',
                                'd-m-Y' => 'DD-MM-YYYY (15-01-2024)',
                            ])
                            ->default('Y-m-d')
                            ->required(),

                        Forms\Components\Select::make('timezone')
                            ->label('Timezone')
                            ->options([
                                'UTC' => 'UTC',
                                'America/New_York' => 'Eastern Time',
                                'America/Chicago' => 'Central Time',
                                'America/Denver' => 'Mountain Time',
                                'America/Los_Angeles' => 'Pacific Time',
                                'Europe/London' => 'London',
                                'Europe/Paris' => 'Paris',
                                'Asia/Tokyo' => 'Tokyo',
                                'Asia/Dubai' => 'Dubai',
                            ])
                            ->default('UTC')
                            ->required()
                            ->searchable(),
                    ])
                    ->columns(2),
            ])
            ->statePath('generalSettingsData');
    }

    public function saveGeneralSettings(): void
    {
        try {
            $this->validate();

            $user = auth('admins')->user();
            if ($user && isset($this->generalSettingsData['theme_color'])) {
                $user->update(['theme_color' => $this->generalSettingsData['theme_color']]);
            }

            // Here you could save other general settings to a configuration table or config files
            // For now, we'll just handle the theme color

            Notification::make()
                ->title('General Settings Updated Successfully')
                ->body('Your preferences have been saved. Some changes may require a page refresh.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Saving General Settings')
                ->body('An error occurred while saving: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
