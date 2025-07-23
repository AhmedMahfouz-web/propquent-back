<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Report Filters -->
        <x-filament::section>
            <x-slot name="heading">
                Report Filters
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        @if ($this->selectedClientId)
            <!-- Portfolio Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-filament::card>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary-600">
                            ${{ number_format($portfolioData['total_invested'] ?? 0, 2) }}
                        </div>
                        <div class="text-sm text-gray-600">Total Invested</div>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-success-600">
                            ${{ number_format($portfolioData['current_value'] ?? 0, 2) }}
                        </div>
                        <div class="text-sm text-gray-600">Current Value</div>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="text-center">
                        <div
                            class="text-2xl font-bold {{ ($portfolioData['total_return'] ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                            ${{ number_format($portfolioData['total_return'] ?? 0, 2) }}
                        </div>
                        <div class="text-sm text-gray-600">Total Return</div>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="text-center">
                        <div
                            class="text-2xl font-bold {{ ($portfolioData['return_percentage'] ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                            {{ number_format($portfolioData['return_percentage'] ?? 0, 2) }}%
                        </div>
                        <div class="text-sm text-gray-600">Return Percentage</div>
                    </div>
                </x-filament::card>
            </div>

            <!-- Client Information -->
            @if ($selectedClient)
                <x-filament::section>
                    <x-slot name="heading">
                        Client Information
                    </x-slot>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <strong>Name:</strong> {{ $selectedClient->full_name }}
                        </div>
                        <div>
                            <strong>Email:</strong> {{ $selectedClient->email }}
                        </div>
                        <div>
                            <strong>Phone:</strong> {{ $selectedClient->phone_number ?? 'N/A' }}
                        </div>
                        <div>
                            <strong>Country:</strong> {{ $selectedClient->country ?? 'N/A' }}
                        </div>
                        <div>
                            <strong>Total Investments:</strong> {{ $portfolioData['investment_count'] ?? 0 }}
                        </div>
                        <div>
                            <strong>Account Status:</strong>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $selectedClient->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $selectedClient->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </x-filament::section>
            @endif

            <!-- Investment Breakdown -->
            @if (!empty($investmentBreakdown))
                <x-filament::section>
                    <x-slot name="heading">
                        Profit Analysis
                    </x-slot>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-lg font-semibold text-blue-600">
                                ${{ number_format($investmentBreakdown['realized_gains'] ?? 0, 2) }}
                            </div>
                            <div class="text-sm text-blue-800">Realized Gains</div>
                        </div>

                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-lg font-semibold text-green-600">
                                ${{ number_format($investmentBreakdown['unrealized_gains'] ?? 0, 2) }}
                            </div>
                            <div class="text-sm text-green-800">Unrealized Gains</div>
                        </div>

                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-lg font-semibold text-purple-600">
                                {{ number_format($investmentBreakdown['gains_percentage'] ?? 0, 2) }}%
                            </div>
                            <div class="text-sm text-purple-800">Total Gains %</div>
                        </div>
                    </div>
                </x-filament::section>
            @endif

            <!-- Investment Details Table -->
            <x-filament::section>
                <x-slot name="heading">
                    Investment Details
                </x-slot>

                {{ $this->table }}
            </x-filament::section>
        @else
            <!-- Default State -->
            <x-filament::section>
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">
                        📊
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Client Portfolio Analysis</h3>
                    <p class="text-gray-600 mb-6">Select a client from the filter above to view their detailed portfolio
                        analysis, investment breakdown, and performance metrics.</p>

                    <!-- Overall Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-8">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-700">
                                {{ $portfolioData['total_clients'] ?? 0 }}
                            </div>
                            <div class="text-sm text-gray-600">Total Clients</div>
                        </div>

                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-700">
                                {{ $portfolioData['total_investments'] ?? 0 }}
                            </div>
                            <div class="text-sm text-gray-600">Total Investments</div>
                        </div>

                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-700">
                                ${{ number_format($portfolioData['total_portfolio_value'] ?? 0, 0) }}
                            </div>
                            <div class="text-sm text-gray-600">Portfolio Value</div>
                        </div>

                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-700">
                                {{ number_format($portfolioData['average_return'] ?? 0, 1) }}%
                            </div>
                            <div class="text-sm text-gray-600">Average Return</div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
