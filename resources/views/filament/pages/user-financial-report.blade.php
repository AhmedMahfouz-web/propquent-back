<x-filament-panels::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}
    </form>

    <div wire:init="loadData">
        <div wire:loading wire:target="loadData" class="w-full">
            <div class="p-12 text-center text-gray-500">
                Loading user financial data...
            </div>
        </div>
        <div wire:loading.remove wire:target="loadData">
            @php
                $reportData = $this->reportData;
                $users = $reportData['users'];
                $userFinancialData = $reportData['userFinancialData'];
                $allMonths = $reportData['allMonths'];
                $debugInfo = $reportData['debugInfo'] ?? [];
                $selectedMetrics = $this->selectedMetrics;

                // Get metric configuration with colors and labels
                $metricConfig = $this->getMetricConfig();
                $availableMetrics = $this->getAvailableMetrics();

                // Maintain consistent metric order regardless of selection
                $metricsToShow = [];
                foreach ($availableMetrics as $key => $label) {
                    if (in_array($key, $selectedMetrics)) {
                        $metricsToShow[$key] = $label;
                    }
                }
            @endphp

            {{-- Debug Information --}}
            @if (!empty($debugInfo))
                <div class="mt-6 mb-6 p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                    <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-3">Debug Information</h3>
                    <div class="space-y-4">
                        @if (isset($debugInfo['project_transactions_by_status']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">Project Transactions by Status:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['project_transactions_by_status'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['user_transactions_by_status']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">User Transactions by Status:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['user_transactions_by_status'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['debug_test']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">Debug Test:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['debug_test'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['all_months']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">All Months:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['all_months'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['total_done_project_transactions_main']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">Total Done Project Transactions:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['total_done_project_transactions_main'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['sample_done_transaction_dates']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">Sample Done Transaction Dates:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['sample_done_transaction_dates'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['project_transactions_query']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">Project Transactions Query:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['project_transactions_query'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['project_transactions_found']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">Project Transactions Found:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['project_transactions_found'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['project_transactions_count']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">Project Transactions Count:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['project_transactions_count'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['user_transactions_for_company']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">User Transactions for Company:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['user_transactions_for_company'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['cash_calculations']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">Cash Calculations:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['cash_calculations'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['equity_calculations']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">Equity Calculations:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['equity_calculations'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        
                        @if (isset($debugInfo['company_equity_calculations']))
                            <div>
                                <h4 class="font-medium text-yellow-700 dark:text-yellow-300">Company Equity Calculations:</h4>
                                <pre class="text-sm bg-white dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($debugInfo['company_equity_calculations'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="mt-6 overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 compact-table">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-0 bg-gray-50 dark:bg-gray-700">
                                <button wire:click="sortByField('full_name')" class="flex items-center">
                                    User
                                    @if ($this->sortBy === 'full_name')
                                        @if ($this->sortDirection === 'asc')
                                            <x-heroicon-s-chevron-up class="w-4 h-4 ml-1" />
                                        @else
                                            <x-heroicon-s-chevron-down class="w-4 h-4 ml-1" />
                                        @endif
                                    @endif
                                </button>
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                Metric
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                <button wire:click="sortByField('total_equity')" class="flex items-center ml-auto">
                                    Total
                                    @if ($this->sortBy === 'total_equity')
                                        @if ($this->sortDirection === 'asc')
                                            <x-heroicon-s-chevron-up class="w-4 h-4 ml-1" />
                                        @else
                                            <x-heroicon-s-chevron-down class="w-4 h-4 ml-1" />
                                        @endif
                                    @endif
                                </button>
                            </th>
                            @foreach ($allMonths as $month)
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                    {{ date('M Y', strtotime($month)) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($users as $user)
                            @php
                                $userData = $userFinancialData[$user->id] ?? null;
                                $rowspan = count($metricsToShow);
                            @endphp
                            @if ($userData && $rowspan > 0)
                                @foreach ($metricsToShow as $key => $label)
                                    @php
                                        $config = $metricConfig[$key] ?? [];
                                        $colorClasses = $this->getMetricColorClasses($key);
                                        $totalColorClasses = $this->getMetricTotalColorClasses($key);
                                        $isEvenRow = ($loop->index % 2 == 0);
                                        $rowBgClass = $isEvenRow ? 'bg-gray-50 dark:bg-gray-900' : 'bg-white dark:bg-gray-800';
                                    @endphp
                                    <tr wire:key="user-{{ $user->id }}-metric-{{ $key }}"
                                        class="{{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }} {{ $rowBgClass }} metric-row">
                                        @if ($loop->first)
                                            <td rowspan="{{ $rowspan }}"
                                                class="px-4 py-2 align-top whitespace-nowrap border-r dark:border-gray-600 sticky left-0 {{ $rowBgClass }} user-name-cell">
                                                <div class="font-bold text-sm">{{ $userData['full_name'] }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    <span
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        {{ $userData['custom_id'] ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            </td>
                                        @endif
                                        <td class="px-4 py-2 whitespace-nowrap {{ $colorClasses['bg'] }} metric-{{ $config['color'] ?? 'gray' }} border-l-4 {{ $colorClasses['border'] }}">
                                            <div class="flex items-center space-x-1.5">
                                                @if(isset($config['icon']))
                                                    <x-dynamic-component 
                                                        :component="$config['icon']" 
                                                        class="w-3.5 h-3.5 {{ $colorClasses['text'] }}" 
                                                    />
                                                @endif
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium metric-badge {{ $colorClasses['badge'] }}">
                                                    {{ $config['label'] ?? $label }}
                                                </span>
                                            </div>
                                            @if(isset($config['description']))
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    {{ $config['description'] }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right font-bold {{ $totalColorClasses['bg'] }} metric-total-{{ $config['color'] ?? 'gray' }}">
                                            <span class="text-sm {{ $totalColorClasses['text'] }}">
                                                @if ($key === 'equity_percentage')
                                                    {{ number_format(array_sum($userData[$key]), 2) }}%
                                                @else
                                                    ${{ number_format(array_sum($userData[$key]), 2) }}
                                                @endif
                                            </span>
                                        </td>
                                        @foreach ($allMonths as $month)
                                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                                <span class="text-xs text-gray-700 dark:text-gray-300">
                                                    @if ($key === 'equity_percentage')
                                                        {{ number_format($userData[$key][$month] ?? 0, 2) }}%
                                                    @else
                                                        ${{ number_format($userData[$key][$month] ?? 0, 2) }}
                                                    @endif
                                                </span>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        @empty
                            <tr>
                                <td colspan="{{ 3 + count($allMonths) }}" class="px-6 py-12 whitespace-nowrap">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" aria-hidden="true">
                                            <path vector-effect="non-scaling-stroke" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No users
                                            found</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your
                                            filters or search criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                @if ($users->count() > 0)
                    {{ $users->links() }}
                @endif
            </div>

        </div>
    </div>

</x-filament-panels::page>

@push('styles')
    <style>
        /* Compact table styling */
        .compact-table {
            line-height: 1.2;
        }

        .compact-table td {
            padding: 0.375rem 0.75rem;
        }

        /* Metric badge hover effects */
        .metric-badge {
            transition: all 0.2s ease-in-out;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .metric-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Metric row hover effects */
        .metric-row {
            transition: all 0.15s ease-in-out;
        }

        .metric-row:hover {
            transform: translateX(2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .dark .metric-row:hover {
            box-shadow: 0 2px 8px rgba(255, 255, 255, 0.1);
        }

        /* Enhanced border colors */
        .border-l-4 {
            border-left-width: 4px;
        }

        /* Alternating row colors with subtle gradients */
        .metric-row.bg-gray-50 {
            background: linear-gradient(90deg, rgba(249, 250, 251, 0.8) 0%, rgba(249, 250, 251, 0.4) 100%);
        }

        .dark .metric-row.bg-gray-900 {
            background: linear-gradient(90deg, rgba(17, 24, 39, 0.8) 0%, rgba(17, 24, 39, 0.4) 100%);
        }

        /* Compact text sizing */
        .text-compact {
            font-size: 0.75rem;
            line-height: 1rem;
        }

        /* Fallback colors for user metrics */
        .metric-green { background-color: #dcfce7 !important; color: #166534 !important; border-left-color: #22c55e !important; }
        .metric-red { background-color: #fee2e2 !important; color: #991b1b !important; border-left-color: #ef4444 !important; }
        .metric-blue { background-color: #dbeafe !important; color: #1e40af !important; border-left-color: #3b82f6 !important; }
        .metric-purple { background-color: #f3e8ff !important; color: #6b21a8 !important; border-left-color: #a855f7 !important; }
        .metric-amber { background-color: #fef3c7 !important; color: #92400e !important; border-left-color: #f59e0b !important; }
        .metric-cyan { background-color: #cffafe !important; color: #155e75 !important; border-left-color: #06b6d4 !important; }
        .metric-indigo { background-color: #e0e7ff !important; color: #3730a3 !important; border-left-color: #6366f1 !important; }

        /* Dark mode fallback colors */
        .dark .metric-green { background-color: #14532d !important; color: #bbf7d0 !important; }
        .dark .metric-red { background-color: #7f1d1d !important; color: #fecaca !important; }
        .dark .metric-blue { background-color: #1e3a8a !important; color: #bfdbfe !important; }
        .dark .metric-purple { background-color: #581c87 !important; color: #e9d5ff !important; }
        .dark .metric-amber { background-color: #78350f !important; color: #fde68a !important; }
        .dark .metric-cyan { background-color: #164e63 !important; color: #a5f3fc !important; }
        .dark .metric-indigo { background-color: #312e81 !important; color: #c7d2fe !important; }

        /* Darker total colors */
        .metric-total-green { background-color: #bbf7d0 !important; color: #14532d !important; }
        .metric-total-red { background-color: #fecaca !important; color: #7f1d1d !important; }
        .metric-total-blue { background-color: #bfdbfe !important; color: #1e3a8a !important; }
        .metric-total-purple { background-color: #e9d5ff !important; color: #581c87 !important; }
        .metric-total-amber { background-color: #fde68a !important; color: #78350f !important; }
        .metric-total-cyan { background-color: #a5f3fc !important; color: #164e63 !important; }
        .metric-total-indigo { background-color: #c7d2fe !important; color: #312e81 !important; }

        /* Dark mode darker total colors */
        .dark .metric-total-green { background-color: #166534 !important; color: #bbf7d0 !important; }
        .dark .metric-total-red { background-color: #991b1b !important; color: #fecaca !important; }
        .dark .metric-total-blue { background-color: #1e40af !important; color: #bfdbfe !important; }
        .dark .metric-total-purple { background-color: #6b21a8 !important; color: #e9d5ff !important; }
        .dark .metric-total-amber { background-color: #92400e !important; color: #fde68a !important; }
        .dark .metric-total-cyan { background-color: #155e75 !important; color: #a5f3fc !important; }
        .dark .metric-total-indigo { background-color: #3730a3 !important; color: #c7d2fe !important; }

        /* User name column styling */
        .user-name-cell {
            min-width: 200px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .user-name-cell {
                min-width: 150px;
            }
        }
    </style>
@endpush
