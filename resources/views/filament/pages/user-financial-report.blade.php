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
                $selectedMetrics = $this->selectedMetrics;

                // Filter metrics based on selection
                $availableMetrics = [
                    'deposits' => 'Deposits',
                    'withdrawals' => 'Withdrawals',
                    'equity' => 'Equity',
                    'equity_percentage' => 'Equity %',
                    'total_profit' => 'Total Profit',
                    'profit_asset' => 'Profit Asset',
                    'profit_operation' => 'Profit Operation',
                ];

                $metricsToShow = [];
                foreach ($selectedMetrics as $key) {
                    if (isset($availableMetrics[$key])) {
                        $metricsToShow[$key] = $availableMetrics[$key];
                    }
                }
            @endphp

            <div class="mt-6 overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
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
                                    <tr wire:key="user-{{ $user->id }}-metric-{{ $key }}"
                                        class="{{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }} bg-white dark:bg-gray-800">
                                        @if ($loop->first)
                                            <td rowspan="{{ $rowspan }}"
                                                class="px-6 py-4 align-top whitespace-nowrap border-r dark:border-gray-600 sticky left-0 bg-white dark:bg-gray-800">
                                                <div class="font-bold text-lg">{{ $userData['full_name'] }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    <span
                                                        class="inline-flex items-center py-0.5 rounded-full text-s font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        {{ $userData['custom_id'] ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $label }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold">
                                            @if ($key === 'equity_percentage')
                                                {{ number_format(array_sum($userData[$key]), 2) }}%
                                            @else
                                                ${{ number_format(array_sum($userData[$key]), 2) }}
                                            @endif
                                        </td>
                                        @foreach ($allMonths as $month)
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                @if ($key === 'equity_percentage')
                                                    {{ number_format($userData[$key][$month] ?? 0, 2) }}%
                                                @else
                                                    ${{ number_format($userData[$key][$month] ?? 0, 2) }}
                                                @endif
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
