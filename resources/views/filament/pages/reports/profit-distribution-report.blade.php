<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Report Filters -->
        <x-filament::section>
            <x-slot name="heading">
                Report Filters
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-filament::card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-success-600">
                        ${{ number_format($summaryData['total_distributions'] ?? 0, 0) }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Total Distributed</div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-warning-600">
                        ${{ number_format($summaryData['pending_distributions'] ?? 0, 0) }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Pending Distributions</div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-danger-600">
                        ${{ number_format($summaryData['overdue_distributions'] ?? 0, 0) }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Overdue Amount</div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-info-600">
                        ${{ number_format($summaryData['recent_distributions'] ?? 0, 0) }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Last 30 Days</div>
                </div>
            </x-filament::card>
        </div>

        <!-- Distribution by Type -->
        @if (!empty($summaryData['distributions_by_type']))
            <x-filament::section>
                <x-slot name="heading">
                    Distribution Breakdown by Type
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($summaryData['distributions_by_type'] as $type => $amount)
                        <div
                            class="p-4 rounded-lg border {{ $type === 'dividend' ? 'bg-green-50 border-green-200' : ($type === 'capital_gain' ? 'bg-blue-50 border-blue-200' : 'bg-yellow-50 border-yellow-200') }}">
                            <div class="text-center">
                                <div
                                    class="text-2xl font-bold {{ $type === 'dividend' ? 'text-green-600' : ($type === 'capital_gain' ? 'text-blue-600' : 'text-yellow-600') }}">
                                    ${{ number_format($amount, 0) }}
                                </div>
                                <div
                                    class="text-sm {{ $type === 'dividend' ? 'text-green-800' : ($type === 'capital_gain' ? 'text-blue-800' : 'text-yellow-800') }} mt-1">
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Distribution Status Overview -->
            <x-filament::section>
                <x-slot name="heading">
                    Distribution Status Overview
                </x-slot>

                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                        <div>
                            <div class="font-medium text-green-800">Paid Distributions</div>
                            <div class="text-sm text-green-600">Successfully processed</div>
                        </div>
                        <div class="text-2xl font-bold text-green-600">
                            ${{ number_format($summaryData['total_distributions'] ?? 0, 0) }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                        <div>
                            <div class="font-medium text-yellow-800">Pending Distributions</div>
                            <div class="text-sm text-yellow-600">Awaiting processing</div>
                        </div>
                        <div class="text-2xl font-bold text-yellow-600">
                            ${{ number_format($summaryData['pending_distributions'] ?? 0, 0) }}
                        </div>
                    </div>

                    @if (($summaryData['overdue_distributions'] ?? 0) > 0)
                        <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                            <div>
                                <div class="font-medium text-red-800">Overdue Distributions</div>
                                <div class="text-sm text-red-600">Require immediate attention</div>
                            </div>
                            <div class="text-2xl font-bold text-red-600">
                                {{ $summaryData['overdue_distributions'] }}
                            </div>
                        </div>
                    @endif
                </div>
            </x-filament::section>

            <!-- Client Statistics -->
            <x-filament::section>
                <x-slot name="heading">
                    Client Distribution Statistics
                </x-slot>

                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                        <div>
                            <div class="font-medium text-blue-800">Active Clients</div>
                            <div class="text-sm text-blue-600">Receiving distributions</div>
                        </div>
                        <div class="text-2xl font-bold text-blue-600">
                            {{ $summaryData['total_users_with_distributions'] ?? 0 }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                        <div>
                            <div class="font-medium text-purple-800">Upcoming Distributions</div>
                            <div class="text-sm text-purple-600">Next 30 days</div>
                        </div>
                        <div class="text-2xl font-bold text-purple-600">
                            {{ $summaryData['upcoming_distributions'] ?? 0 }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-800">Average Distribution</div>
                            <div class="text-sm text-gray-600">Per client</div>
                        </div>
                        <div class="text-2xl font-bold text-gray-600">
                            ${{ number_format(($summaryData['total_users_with_distributions'] ?? 0) > 0 ? ($summaryData['total_distributions'] ?? 0) / $summaryData['total_users_with_distributions'] : 0, 0) }}
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Distribution Details Table -->
        <x-filament::section>
            <x-slot name="heading">
                Distribution Details
            </x-slot>

            {{ $this->table }}
        </x-filament::section>

        <!-- Quick Actions -->
        <x-filament::section>
            <x-slot name="heading">
                Quick Actions
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 border rounded-lg text-center">
                    <div class="text-lg font-semibold mb-2">Process Pending</div>
                    <div class="text-sm text-gray-600 mb-3">Process all pending distributions</div>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Process All
                    </button>
                </div>

                <div class="p-4 border rounded-lg text-center">
                    <div class="text-lg font-semibold mb-2">Export Report</div>
                    <div class="text-sm text-gray-600 mb-3">Download distribution report</div>
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Export PDF
                    </button>
                </div>

                <div class="p-4 border rounded-lg text-center">
                    <div class="text-lg font-semibold mb-2">Schedule Distribution</div>
                    <div class="text-sm text-gray-600 mb-3">Create new distribution</div>
                    <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Schedule New
                    </button>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
