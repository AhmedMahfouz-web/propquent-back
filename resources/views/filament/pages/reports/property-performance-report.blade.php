<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Report Filters -->
        <x-filament::section>
            <x-slot name="heading">
                Report Filters
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        <!-- Performance Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-filament::card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary-600">
                        {{ $performanceData['total_properties'] ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Properties with Investments</div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-success-600">
                        {{ count($performanceData['top_performers'] ?? []) }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Top Performers</div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-info-600">
                        {{ count($performanceData['property_types'] ?? []) }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Property Types</div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-warning-600">
                        {{ count($performanceData['developer_performance'] ?? []) }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Active Developers</div>
                </div>
            </x-filament::card>
        </div>

        <!-- Performance Analysis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Performing Properties -->
            <x-filament::section>
                <x-slot name="heading">
                    Top Performing Properties
                </x-slot>

                <div class="space-y-3">
                    @forelse($performanceData['top_performers'] ?? [] as $property)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-medium">{{ $property['project_name'] }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ $property['project_key'] }} • {{ $property['unique_investors'] }} investors
                                </div>
                            </div>
                            <div class="text-right">
                                <div
                                    class="font-semibold {{ $property['return_percentage'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                    {{ number_format($property['return_percentage'], 2) }}%
                                </div>
                                <div class="text-sm text-gray-600">
                                    ${{ number_format($property['total_invested'], 0) }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            No performance data available
                        </div>
                    @endforelse
                </div>
            </x-filament::section>

            <!-- Developer Performance -->
            <x-filament::section>
                <x-slot name="heading">
                    Top Performing Developers
                </x-slot>

                <div class="space-y-3">
                    @forelse($performanceData['developer_performance'] ?? [] as $developer)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-medium">{{ $developer['name'] }}</div>
                                <div class="text-sm text-gray-600">{{ $developer['project_count'] }} properties</div>
                            </div>
                            <div class="text-right">
                                <div
                                    class="font-semibold {{ $developer['return_percentage'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                    {{ number_format($developer['return_percentage'], 2) }}%
                                </div>
                                <div class="text-sm text-gray-600">
                                    ${{ number_format($developer['total_invested'], 0) }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            No developer data available
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        <!-- Property Type Performance -->
        @if (!empty($performanceData['property_types']))
            <x-filament::section>
                <x-slot name="heading">
                    Performance by Property Type
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($performanceData['property_types'] as $typeData)
                        <div class="p-4 border rounded-lg">
                            <div class="text-lg font-semibold mb-3 capitalize">{{ $typeData['type'] }}</div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Properties:</span>
                                    <span class="font-medium">{{ $typeData['count'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Invested:</span>
                                    <span
                                        class="font-medium">${{ number_format($typeData['total_invested'], 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Current Value:</span>
                                    <span
                                        class="font-medium">${{ number_format($typeData['current_value'], 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Return:</span>
                                    <span
                                        class="font-medium {{ $typeData['return_percentage'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                        {{ number_format($typeData['return_percentage'], 2) }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        <!-- Performance Metrics Chart -->
        <x-filament::section>
            <x-slot name="heading">
                Property Performance Distribution
            </x-slot>

            <div class="h-64">
                <canvas id="performanceChart"></canvas>
            </div>
        </x-filament::section>

        <!-- Property Details Table -->
        <x-filament::section>
            <x-slot name="heading">
                Property Performance Details
            </x-slot>

            {{ $this->table }}
        </x-filament::section>

        <!-- Performance Insights -->
        <x-filament::section>
            <x-slot name="heading">
                Performance Insights
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h4 class="font-semibold text-lg">Key Observations</h4>

                    @if (!empty($performanceData['property_types']))
                        @php
                            $bestType = collect($performanceData['property_types'])
                                ->sortByDesc('return_percentage')
                                ->first();
                        @endphp
                        @if ($bestType)
                            <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="font-medium text-green-800">Best Performing Type</div>
                                <div class="text-sm text-green-700">
                                    {{ ucfirst($bestType['type']) }} properties are showing the highest returns at
                                    {{ number_format($bestType['return_percentage'], 2) }}%
                                </div>
                            </div>
                        @endif
                    @endif

                    @if (!empty($performanceData['developer_performance']))
                        @php
                            $topDeveloper = collect($performanceData['developer_performance'])->first();
                        @endphp
                        @if ($topDeveloper)
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="font-medium text-blue-800">Top Developer</div>
                                <div class="text-sm text-blue-700">
                                    {{ $topDeveloper['name'] }} leads with
                                    {{ number_format($topDeveloper['return_percentage'], 2) }}% average return across
                                    {{ $topDeveloper['project_count'] }} properties
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="space-y-4">
                    <h4 class="font-semibold text-lg">Investment Opportunities</h4>

                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="font-medium text-yellow-800">Diversification Opportunity</div>
                        <div class="text-sm text-yellow-700">
                            Consider expanding investment in underrepresented property types for better portfolio
                            diversification
                        </div>
                    </div>

                    <div class="p-3 bg-purple-50 border border-purple-200 rounded-lg">
                        <div class="font-medium text-purple-800">Growth Potential</div>
                        <div class="text-sm text-purple-700">
                            Properties in early development stages may offer higher long-term returns
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('performanceChart').getContext('2d');
                const propertyTypes = @json($performanceData['property_types'] ?? []);

                if (propertyTypes.length > 0) {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: propertyTypes.map(type => type.type.charAt(0).toUpperCase() + type.type
                                .slice(1)),
                            datasets: [{
                                label: 'Return Percentage (%)',
                                data: propertyTypes.map(type => type.return_percentage),
                                backgroundColor: [
                                    'rgba(59, 130, 246, 0.8)',
                                    'rgba(16, 185, 129, 0.8)',
                                    'rgba(245, 158, 11, 0.8)',
                                    'rgba(139, 92, 246, 0.8)',
                                    'rgba(239, 68, 68, 0.8)'
                                ],
                                borderColor: [
                                    'rgb(59, 130, 246)',
                                    'rgb(16, 185, 129)',
                                    'rgb(245, 158, 11)',
                                    'rgb(139, 92, 246)',
                                    'rgb(239, 68, 68)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Return Percentage (%)'
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Property Type'
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
