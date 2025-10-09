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
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm compact-table">
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
                                @php
                                    $config = $metricConfig[$key] ?? [];
                                    $colorClasses = $this->getMetricColorClasses($key);
                                    $isEvenRow = ($loop->index % 2 == 0);
                                    $rowBgClass = $isEvenRow ? 'bg-gray-50 dark:bg-gray-900' : 'bg-white dark:bg-gray-800';
                                @endphp
                                <tr wire:key="project-{{ $projectKey }}-metric-{{ $key }}"
                                    class="{{ $rowBgClass }} metric-row border-l-4 {{ $colorClasses['border'] }}">
                                    <td
                                        class="px-2 py-2 align-top whitespace-nowrap border-r dark:border-gray-600 sticky left-0 {{ $rowBgClass }} z-10 {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
                                        @if ($loop->first)
                                            <div class="font-mono text-xs">{{ $projectData['key'] }}</div>
                                        @endif
                                    </td>
                                    <td
                                        class="px-4 py-2 align-top border-r dark:border-gray-600 sticky left-12 {{ $rowBgClass }} z-10 {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }} project-name-cell">
                                        @if ($loop->first)
                                            <div class="font-bold text-xs">{{ $projectData['title'] }}</div>
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $projectData['status'] }}</div>
                                        @endif
                                    </td>
                                    <td
                                        class="px-4 py-2 whitespace-nowrap {{ $colorClasses['bg'] }} {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
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
                                    <td
                                        class="px-4 py-2 whitespace-nowrap text-right font-bold {{ $colorClasses['bg'] }} {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
                                        <span class="text-sm {{ $colorClasses['text'] }}">
                                            ${{ number_format($projectData['totals'][$key] ?? 0, 2) }}
                                        </span>
                                    </td>
                                    @foreach ($allMonths as $month)
                                        <td
                                            class="px-3 py-2 whitespace-nowrap text-right {{ $colorClasses['bg'] }} {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
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
                                                <span class="font-medium text-xs {{ $colorClasses['text'] }}">
                                                    ${{ number_format($projectData['months'][$month][$key] ?? 0, 2) }}
                                                </span>
                                            @else
                                                <span class="text-xs {{ $colorClasses['text'] }}">
                                                    ${{ number_format($projectData['months'][$month][$key] ?? 0, 2) }}
                                                </span>
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .project-name-cell {
                max-width: 180px;
            }
        }
    </style>
@endpush
