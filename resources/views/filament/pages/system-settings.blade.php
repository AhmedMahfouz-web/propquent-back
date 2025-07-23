<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::tabs>
            <x-filament::tabs.item :active="$activeTab === 'general'" wire:click="$set('activeTab', 'general')">
                General Settings
            </x-filament::tabs.item>

            <x-filament::tabs.item :active="$activeTab === 'projects'" wire:click="$set('activeTab', 'projects')">
                Projects
            </x-filament::tabs.item>

            <x-filament::tabs.item :active="$activeTab === 'transactions'" wire:click="$set('activeTab', 'transactions')">
                Transactions
            </x-filament::tabs.item>

            <x-filament::tabs.item :active="$activeTab === 'properties'" wire:click="$set('activeTab', 'properties')">
                Properties
            </x-filament::tabs.item>
        </x-filament::tabs>

        @if ($activeTab === 'general')
            <div class="space-y-6">
                <x-filament::section>
                    <x-slot name="heading">
                        General Settings
                    </x-slot>

                    <x-slot name="description">
                        Configure general system preferences including theme, currency, and display formats.
                    </x-slot>

                    <form wire:submit="saveGeneralSettings">
                        {{ $this->getGeneralSettingsForm() }}

                        <div class="mt-6">
                            <x-filament::button type="submit">
                                Save General Settings
                            </x-filament::button>
                        </div>
                    </form>

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Quick Theme Switcher
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Click on a color below to instantly preview and apply the theme.
                        </p>
                        <x-theme-switcher :current-theme="auth('admins')->user()?->theme_color ?? 'blue'" />
                    </div>
                </x-filament::section>
            </div>
        @endif

        @if ($activeTab === 'projects')
            <div class="space-y-6">
                <x-filament::section>
                    <x-slot name="heading">
                        Project Statuses
                    </x-slot>

                    <x-slot name="description">
                        Manage the available project statuses in your system.
                    </x-slot>

                    <form wire:submit="saveProjectStatuses">
                        {{ $this->getProjectStatusesForm() }}

                        <div class="mt-6">
                            <x-filament::button type="submit">
                                Save Project Statuses
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">
                        Project Stages
                    </x-slot>

                    <x-slot name="description">
                        Manage the available project stages in your system.
                    </x-slot>

                    <form wire:submit="saveProjectStages">
                        {{ $this->getProjectStagesForm() }}

                        <div class="mt-6">
                            <x-filament::button type="submit">
                                Save Project Stages
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">
                        Project Targets
                    </x-slot>

                    <x-slot name="description">
                        Manage the available project targets in your system.
                    </x-slot>

                    <form wire:submit="saveProjectTargets">
                        {{ $this->getProjectTargetsForm() }}

                        <div class="mt-6">
                            <x-filament::button type="submit">
                                Save Project Targets
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::section>
            </div>
        @endif

        @if ($activeTab === 'properties')
            <div class="space-y-6">
                <x-filament::section>
                    <x-slot name="heading">
                        Property Types
                    </x-slot>

                    <x-slot name="description">
                        Manage the available property types in your system.
                    </x-slot>

                    <form wire:submit="savePropertyTypes">
                        {{ $this->getPropertyTypesForm() }}

                        <div class="mt-6">
                            <x-filament::button type="submit">
                                Save Property Types
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">
                        Investment Types
                    </x-slot>

                    <x-slot name="description">
                        Manage the available investment types in your system.
                    </x-slot>

                    <form wire:submit="saveInvestmentTypes">
                        {{ $this->getInvestmentTypesForm() }}

                        <div class="mt-6">
                            <x-filament::button type="submit">
                                Save Investment Types
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::section>
            </div>
        @endif

        @if ($activeTab === 'transactions')
            <div class="space-y-6">
                <x-filament::section>
                    <x-slot name="heading">
                        Transaction Types
                    </x-slot>

                    <x-slot name="description">
                        Manage the available transaction types in your system.
                    </x-slot>

                    <form wire:submit="saveTransactionTypes">
                        {{ $this->getTransactionTypesForm() }}

                        <div class="mt-6">
                            <x-filament::button type="submit">
                                Save Transaction Types
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">
                        Transaction Statuses
                    </x-slot>

                    <x-slot name="description">
                        Manage the available transaction statuses in your system.
                    </x-slot>

                    <form wire:submit="saveTransactionStatuses">
                        {{ $this->getTransactionStatusesForm() }}

                        <div class="mt-6">
                            <x-filament::button type="submit">
                                Save Transaction Statuses
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">
                        Transaction Serving Types
                    </x-slot>

                    <x-slot name="description">
                        Manage the available transaction serving types in your system.
                    </x-slot>

                    <form wire:submit="saveTransactionServing">
                        {{ $this->getTransactionServingForm() }}

                        <div class="mt-6">
                            <x-filament::button type="submit">
                                Save Transaction Serving Types
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">
                        Transaction Methods
                    </x-slot>

                    <x-slot name="description">
                        Manage the available transaction methods in your system.
                    </x-slot>

                    <form wire:submit="saveTransactionMethods">
                        {{ $this->getTransactionMethodsForm() }}

                        <div class="mt-6">
                            <x-filament::button type="submit">
                                Save Transaction Methods
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
