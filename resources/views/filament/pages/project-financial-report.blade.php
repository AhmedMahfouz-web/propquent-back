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

                // Get metric configuration with colors and labels
                $metricConfig = $this->getMetricConfig();
                $availableMetrics = $this->getAvailableMetrics();

                $metricsToShow = [];
                foreach ($selectedMetrics as $key) {
                    if (isset($availableMetrics[$key])) {
                        $metricsToShow[$key] = $availableMetrics[$key];
                    }
                }
            @endphp
            <div class="mt-6 overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-0 bg-gray-50 dark:bg-gray-700 z-10">
                                Code
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-12 bg-gray-50 dark:bg-gray-700 z-10"
                                style="max-width: 220px;">
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
                                    class="bg-white dark:bg-gray-800 metric-row">
                                    <td
                                        class="px-2 py-4 align-top whitespace-nowrap border-r dark:border-gray-600 sticky left-0 bg-white dark:bg-gray-800 z-10 {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
                                        @if ($loop->first)
                                            <div class="font-mono text-sm">{{ $projectData['key'] }}</div>
                                        @endif
                                    </td>
                                    <td
                                        class="px-6 py-4 align-top border-r dark:border-gray-600 sticky left-12 bg-white dark:bg-gray-800 z-10 {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }} project-name-cell">
                                        @if ($loop->first)
                                            <div class="font-bold text-sm">{{ $projectData['title'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $projectData['status'] }}</div>
                                        @endif
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
                                        @php
                                            $config = $metricConfig[$key] ?? [];
                                            $colorClasses = $this->getMetricColorClasses($key);
                                        @endphp
                                        <div class="flex items-center space-x-2">
                                            @if(isset($config['icon']))
                                                <x-dynamic-component 
                                                    :component="$config['icon']" 
                                                    class="w-4 h-4 {{ $colorClasses['text'] }}" 
                                                />
                                            @endif
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium metric-badge {{ $colorClasses['badge'] }}">
                                                {{ $config['label'] ?? $label }}
                                            </span>
                                        </div>
                                        @if(isset($config['description']))
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ $config['description'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right font-bold {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
                                        ${{ number_format($projectData['totals'][$key] ?? 0, 2) }}
                                    </td>
                                    @foreach ($allMonths as $month)
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
                                            @if ($key === 'value_correction')
                                                @livewire(
                                                    'quick-value-correction-edit',
                                                    [
                                                        'projectKey' => $projectData['key'],
                                                        'month' => $month,
                                                        'projectTitle' => $projectData['title'],
                                                    ],
                                                    key($projectData['key'] . '-' . $month . '-correction')
                                                )
                                            @elseif ($key === 'evaluation_asset')
                                                <span class="font-medium text-gray-700 dark:text-gray-300">
                                                    ${{ number_format($projectData['months'][$month][$key] ?? 0, 2) }}
                                                </span>
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

@push('styles')
    <style>
        /* Project name column styling */
        .project-name-cell {
            max-width: 220px;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
        }

        .project-name-cell::-webkit-scrollbar {
            height: 4px;
        }

        .project-name-cell::-webkit-scrollbar-track {
            background: #f7fafc;
            border-radius: 2px;
        }

        .project-name-cell::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 2px;
        }

        .project-name-cell::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        /* Metric badge hover effects */
        .metric-badge {
            transition: all 0.2s ease-in-out;
        }

        .metric-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Metric row hover effects */
        .metric-row:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .dark .metric-row:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .project-name-cell {
                max-width: 180px;
            }
        }
    </style>
@endpush
