<x-filament-panels::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}
    </form>

    <div wire:init="loadData">
        <div wire:loading wire:target="loadData" class="w-full">
            <div class="p-12 text-center text-gray-500">
                Loading financial data...
            </div>
        </div>
        <div wire:loading.remove wire:target="loadData">
            @php
                $reportData = $this->reportData;
                $projects = $reportData['projects'];
                $projectsData = $reportData['projectsData'];
                $financialSummary = $reportData['financialSummary'];
                $allMonths = $reportData['allMonths'];
                $selectedMetrics = $this->selectedMetrics;

                // Filter metrics based on selection
                $availableMetrics = [
                    'evaluation_asset' => 'Evaluation Asset',
                    'revenue_operation' => 'Revenue Operation',
                    'revenue_asset' => 'Revenue Asset',
                    'expense_operation' => 'Expense Operation',
                    'expense_asset' => 'Expense Asset',
                    'profit_operation' => 'Profit Operation',
                    'profit_asset' => 'Profit Asset',
                    'total_profit' => 'Total Profit',
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
                                class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-0 bg-gray-50 dark:bg-gray-700 z-10">
                                Code
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-12 bg-gray-50 dark:bg-gray-700 z-10">
                                <button wire:click="sortBy('created_at')" class="flex items-center">
                                    Project
                                    @if ($sortDirection === 'asc')
                                        <x-heroicon-s-chevron-up class="w-4 h-4 ml-1" />
                                    @else
                                        <x-heroicon-s-chevron-down class="w-4 h-4 ml-1" />
                                    @endif
                                </button>
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                Metric
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                Total
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
                        @forelse ($projectsData as $projectKey => $projectData)
                            @foreach ($metricsToShow as $key => $label)
                                <tr wire:key="project-{{ $projectKey }}-metric-{{ $key }}"
                                    class="bg-white dark:bg-gray-800">
                                    <td class="px-2 py-4 align-top whitespace-nowrap border-r dark:border-gray-600 sticky left-0 bg-white dark:bg-gray-800 z-10 {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
                                        @if ($loop->first)
                                            <div class="font-mono text-sm">{{ $projectData['key'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 align-top whitespace-nowrap border-r dark:border-gray-600 sticky left-12 bg-white dark:bg-gray-800 z-10 {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
                                        @if ($loop->first)
                                            <div class="font-bold text-lg">{{ $projectData['title'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $projectData['status'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">{{ $label }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-bold {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
                                        @if ($key === 'evaluation_asset')
                                            @php
                                                $totalEvaluation = 0;
                                                foreach ($allMonths as $month) {
                                                    $totalEvaluation += \App\Models\ProjectEvaluation::getEvaluationForMonth(
                                                        $projectData['key'],
                                                        $month,
                                                    );
                                                }
                                            @endphp
                                            ${{ number_format($totalEvaluation, 2) }}
                                        @else
                                            ${{ number_format($projectData['totals'][$key] ?? 0, 2) }}
                                        @endif
                                    </td>
                                    @foreach ($allMonths as $month)
                                        <td class="px-6 py-4 whitespace-nowrap text-right {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
                                            @if ($key === 'evaluation_asset')
                                                @livewire(
                                                    'quick-evaluation-edit',
                                                    [
                                                        'projectKey' => $projectData['key'],
                                                        'month' => $month,
                                                        'projectTitle' => $projectData['title'],
                                                    ],
                                                    key($projectData['key'] . '-' . $month . '-eval')
                                                )
                                            @else
                                                ${{ number_format($projectData['months'][$month][$key] ?? 0, 2) }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="{{ count($allMonths) + 4 }}" class="py-12">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path vector-effect="non-scaling-stroke" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="2"
                                                d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No projects
                                            found</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">There are no projects
                                            to display.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $projects->links() }}
            </div>

        </div>
    </div>
</x-filament-panels::page>
