<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Statement Parameters -->
        <x-filament::section>
            <x-slot name="heading">
                Statement Parameters
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        @if ($this->selectedClientId)
            <!-- Statement Header -->
            <div class="bg-white border rounded-lg p-6 print:shadow-none">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Investment Statement</h1>
                        <p class="text-gray-600">{{ $statementDate->format('F j, Y') }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-semibold">PROPQUENT Investment Platform</div>
                        <div class="text-sm text-gray-600">Professional Investment Management</div>
                    </div>
                </div>

                <!-- Client Information -->
                @if ($selectedClient)
                    <div class="border-t border-b py-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-2">Client Information</h3>
                                <div class="space-y-1 text-sm">
                                    <div><strong>Name:</strong> {{ $selectedClient->full_name }}</div>
                                    <div><strong>Email:</strong> {{ $selectedClient->email }}</div>
                                    <div><strong>Phone:</strong> {{ $selectedClient->phone_number ?? 'N/A' }}</div>
                                    <div><strong>Country:</strong> {{ $selectedClient->country ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-2">Account Summary</h3>
                                <div class="space-y-1 text-sm">
                                    <div><strong>Client ID:</strong> {{ $selectedClient->id }}</div>
                                    <div><strong>Account Status:</strong>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $selectedClient->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $selectedClient->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div><strong>Statement Period:</strong> {{ ucfirst($this->statementPeriod) }}</div>
                                    <div><strong>Total Investments:</strong>
                                        {{ $portfolioData['investment_count'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Portfolio Summary -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Portfolio Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-blue-600">
                                ${{ number_format($portfolioData['total_invested'] ?? 0, 2) }}
                            </div>
                            <div class="text-sm text-blue-800">Total Invested</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-green-600">
                                ${{ number_format($portfolioData['current_value'] ?? 0, 2) }}
                            </div>
                            <div class="text-sm text-green-800">Current Value</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg text-center">
                            <div
                                class="text-2xl font-bold {{ ($portfolioData['total_return'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($portfolioData['total_return'] ?? 0, 2) }}
                            </div>
                            <div class="text-sm text-purple-800">Total Return</div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg text-center">
                            <div
                                class="text-2xl font-bold {{ ($portfolioData['return_percentage'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($portfolioData['return_percentage'] ?? 0, 2) }}%
                            </div>
                            <div class="text-sm text-yellow-800">Return Percentage</div>
                        </div>
                    </div>
                </div>

                <!-- Investment Holdings -->
                @if (!empty($portfolioData['investments']))
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Investment Holdings</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Property</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Investment Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Investment Amount</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Current Value</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Return</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Return %</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($portfolioData['investments'] as $investment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $investment['project_name'] }}</div>
                                                <div class="text-sm text-gray-500">{{ $investment['project_key'] }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($investment['investment_date'])->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${{ number_format($investment['investment_amount'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${{ number_format($investment['current_value'], 2) }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm {{ $investment['current_value'] - $investment['investment_amount'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                ${{ number_format($investment['current_value'] - $investment['investment_amount'], 2) }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm {{ $investment['return_percentage'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ number_format($investment['return_percentage'], 2) }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Profit Distribution Summary -->
                @if (!empty($distributionData))
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Profit Distribution Summary</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-lg font-semibold text-green-600">
                                    ${{ number_format($distributionData['realized_gains'] ?? 0, 2) }}
                                </div>
                                <div class="text-sm text-green-800">Realized Gains</div>
                                <div class="text-xs text-green-600 mt-1">Distributions received</div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-lg font-semibold text-blue-600">
                                    ${{ number_format($distributionData['unrealized_gains'] ?? 0, 2) }}
                                </div>
                                <div class="text-sm text-blue-800">Unrealized Gains</div>
                                <div class="text-xs text-blue-600 mt-1">Paper gains</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-lg font-semibold text-purple-600">
                                    {{ number_format($distributionData['gains_percentage'] ?? 0, 2) }}%
                                </div>
                                <div class="text-sm text-purple-800">Total Gains</div>
                                <div class="text-xs text-purple-600 mt-1">Overall performance</div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Performance Chart -->
                @if (!empty($performanceData['monthly_trends']))
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Performance Trends (Last 12 Months)</h3>
                        <div class="h-64">
                            <canvas id="performanceTrendChart"></canvas>
                        </div>
                    </div>
                @endif

                <!-- Distribution History -->
                @if (!empty($performanceData['distribution_stats']))
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Distribution History</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-700 mb-2">Distribution Summary</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>Total Paid:</span>
                                        <span
                                            class="font-medium">${{ number_format($performanceData['distribution_stats']['total_paid'] ?? 0, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Pending:</span>
                                        <span
                                            class="font-medium">${{ number_format($performanceData['distribution_stats']['total_pending'] ?? 0, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Total Distributions:</span>
                                        <span
                                            class="font-medium">{{ $performanceData['distribution_stats']['distribution_count'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-700 mb-2">Recent Activity</h4>
                                <div class="space-y-2 text-sm">
                                    @if (!empty($performanceData['distribution_stats']['last_distribution']))
                                        <div>
                                            <span class="text-gray-600">Last Distribution:</span>
                                            <div class="ml-2">
                                                <div>
                                                    ${{ number_format($performanceData['distribution_stats']['last_distribution']['amount'], 2) }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ \Carbon\Carbon::parse($performanceData['distribution_stats']['last_distribution']['date'])->format('M j, Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if (!empty($performanceData['distribution_stats']['next_distribution']))
                                        <div>
                                            <span class="text-gray-600">Next Distribution:</span>
                                            <div class="ml-2">
                                                <div>
                                                    ${{ number_format($performanceData['distribution_stats']['next_distribution']['amount'], 2) }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ \Carbon\Carbon::parse($performanceData['distribution_stats']['next_distribution']['date'])->format('M j, Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Statement Footer -->
                <div class="border-t pt-4 mt-6">
                    <div class="text-xs text-gray-500 space-y-1">
                        <p><strong>Important Notice:</strong> This statement is for informational purposes only and
                            should not be considered as investment advice.</p>
                        <p><strong>Disclaimer:</strong> Past performance does not guarantee future results. All
                            investments carry risk of loss.</p>
                        <p><strong>Contact:</strong> For questions about this statement, please contact your investment
                            advisor.</p>
                        <p class="mt-2"><strong>Generated:</strong> {{ now()->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center space-x-4 print:hidden">
                <button wire:click="generatePdfStatement"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <span>Download PDF</span>
                </button>

                <button wire:click="emailStatement"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span>Email to Client</span>
                </button>

                <button onclick="window.print()"
                    class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    <span>Print</span>
                </button>
            </div>
        @else
            <!-- Default State -->
            <x-filament::section>
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">📄</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Client Investment Statement</h3>
                    <p class="text-gray-600 mb-6">Generate professional investment statements for clients showing their
                        portfolio performance, distributions, and investment details.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-700">{{ $clientData['total_clients'] ?? 0 }}
                            </div>
                            <div class="text-sm text-gray-600">Total Clients</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-700">
                                ${{ number_format($clientData['total_portfolio_value'] ?? 0, 0) }}</div>
                            <div class="text-sm text-gray-600">Total Portfolio Value</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-700">
                                {{ number_format($clientData['average_return'] ?? 0, 1) }}%</div>
                            <div class="text-sm text-gray-600">Average Return</div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>

    @if (!empty($performanceData['monthly_trends']))
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('performanceTrendChart').getContext('2d');
                    const monthlyTrends = @json($performanceData['monthly_trends']);

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: monthlyTrends.map(trend => trend.month_name),
                            datasets: [{
                                label: 'Monthly Distributions ($)',
                                data: monthlyTrends.map(trend => trend.total_amount),
                                borderColor: '#3B82F6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4
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
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + new Intl.NumberFormat().format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        @endpush
    @endif

    <style>
        @media print {
            .print\:hidden {
                display: none !important;
            }

            .print\:shadow-none {
                box-shadow: none !important;
            }

            body {
                font-size: 12px;
            }

            .text-2xl {
                font-size: 1.25rem;
            }

            .text-lg {
                font-size: 1rem;
            }
        }
    </style>
</x-filament-panels::page>
