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

                // Maintain consistent metric order regardless of selection
                $metricsToShow = [];
                foreach ($availableMetrics as $key => $label) {
                    if (in_array($key, $selectedMetrics)) {
                        $metricsToShow[$key] = $label;
                    }
                }
            @endphp
            <div class="mt-6 overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm compact-table">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-0 bg-gray-50 dark:bg-gray-700 z-10">
                                <button wire:click="sortBy('key')" class="flex items-center hover:text-gray-700 dark:hover:text-gray-100">
                                    Code
                                    @if ($sortField === 'key')
                                        @if ($sortDirection === 'asc')
                                            <x-heroicon-s-chevron-up class="w-4 h-4 ml-1" />
                                        @else
                                            <x-heroicon-s-chevron-down class="w-4 h-4 ml-1" />
                                        @endif
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-12 bg-gray-50 dark:bg-gray-700 z-10"
                                style="max-width: 220px;">
                                <button wire:click="sortBy('title')" class="flex items-center hover:text-gray-700 dark:hover:text-gray-100">
                                    Project
                                    @if ($sortField === 'title')
                                        @if ($sortDirection === 'asc')
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
                                <button wire:click="sortBy('created_at')" class="flex items-center hover:text-gray-700 dark:hover:text-gray-100">
                                    Total
                                    @if ($sortField === 'created_at')
                                        @if ($sortDirection === 'asc')
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
                                    <button wire:click="sortBy('month_{{ $month }}')" class="flex items-center hover:text-gray-700 dark:hover:text-gray-100">
                                        {{ date('M Y', strtotime($month)) }}
                                        @if ($sortField === 'month_' . $month)
                                            @if ($sortDirection === 'asc')
                                                <x-heroicon-s-chevron-up class="w-4 h-4 ml-1" />
                                            @else
                                                <x-heroicon-s-chevron-down class="w-4 h-4 ml-1" />
                                            @endif
                                        @endif
                                    </button>
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
                                    $totalColorClasses = $this->getMetricTotalColorClasses($key);
                                    $isEvenRow = ($loop->index % 2 == 0);
                                    $rowBgClass = $isEvenRow ? 'bg-gray-50 dark:bg-gray-900' : 'bg-white dark:bg-gray-800';
                                @endphp
                                <tr wire:key="project-{{ $projectKey }}-metric-{{ $key }}"
                                    class="{{ $rowBgClass }} metric-row">
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
                                        class="px-4 py-2 whitespace-nowrap {{ $colorClasses['bg'] }} metric-{{ $config['color'] ?? 'gray' }} border-l-4 {{ $colorClasses['border'] }} {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
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
                                        class="px-4 py-2 whitespace-nowrap text-right font-bold {{ $totalColorClasses['bg'] }} metric-total-{{ $config['color'] ?? 'gray' }} {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
                                        <span class="text-sm {{ $totalColorClasses['text'] }}">
                                            ${{ number_format($projectData['totals'][$key] ?? 0, 2) }}
                                        </span>
                                    </td>
                                    @foreach ($allMonths as $month)
                                        <td
                                            class="px-3 py-2 whitespace-nowrap text-right {{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }}">
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
                                                <span class="font-medium text-xs text-gray-700 dark:text-gray-300">
                                                    ${{ number_format($projectData['months'][$month][$key] ?? 0, 2) }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-700 dark:text-gray-300">
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

        /* Fallback colors for metrics */
        .metric-green { background-color: #dcfce7 !important; color: #166534 !important; border-left-color: #22c55e !important; }
        .metric-emerald { background-color: #d1fae5 !important; color: #065f46 !important; border-left-color: #10b981 !important; }
        .metric-teal { background-color: #ccfdf7 !important; color: #134e4a !important; border-left-color: #14b8a6 !important; }
        .metric-red { background-color: #fee2e2 !important; color: #991b1b !important; border-left-color: #ef4444 !important; }
        .metric-rose { background-color: #ffe4e6 !important; color: #9f1239 !important; border-left-color: #f43f5e !important; }
        .metric-pink { background-color: #fce7f3 !important; color: #831843 !important; border-left-color: #ec4899 !important; }
        .metric-blue { background-color: #dbeafe !important; color: #1e40af !important; border-left-color: #3b82f6 !important; }
        .metric-cyan { background-color: #cffafe !important; color: #155e75 !important; border-left-color: #06b6d4 !important; }
        .metric-amber { background-color: #fef3c7 !important; color: #92400e !important; border-left-color: #f59e0b !important; }
        .metric-orange { background-color: #fed7aa !important; color: #9a3412 !important; border-left-color: #f97316 !important; }
        .metric-lime { background-color: #ecfccb !important; color: #365314 !important; border-left-color: #84cc16 !important; }
        .metric-violet { background-color: #ede9fe !important; color: #5b21b6 !important; border-left-color: #8b5cf6 !important; }
        .metric-purple { background-color: #f3e8ff !important; color: #6b21a8 !important; border-left-color: #a855f7 !important; }
        .metric-indigo { background-color: #e0e7ff !important; color: #3730a3 !important; border-left-color: #6366f1 !important; }

        /* Dark mode fallback colors */
        .dark .metric-green { background-color: #14532d !important; color: #bbf7d0 !important; }
        .dark .metric-emerald { background-color: #064e3b !important; color: #a7f3d0 !important; }
        .dark .metric-teal { background-color: #134e4a !important; color: #99f6e4 !important; }
        .dark .metric-red { background-color: #7f1d1d !important; color: #fecaca !important; }
        .dark .metric-rose { background-color: #881337 !important; color: #fda4af !important; }
        .dark .metric-pink { background-color: #831843 !important; color: #f9a8d4 !important; }
        .dark .metric-blue { background-color: #1e3a8a !important; color: #bfdbfe !important; }
        .dark .metric-cyan { background-color: #164e63 !important; color: #a5f3fc !important; }
        .dark .metric-amber { background-color: #78350f !important; color: #fde68a !important; }
        .dark .metric-orange { background-color: #9a3412 !important; color: #fed7aa !important; }
        .dark .metric-lime { background-color: #365314 !important; color: #d9f99d !important; }
        .dark .metric-violet { background-color: #4c1d95 !important; color: #ddd6fe !important; }
        .dark .metric-purple { background-color: #581c87 !important; color: #e9d5ff !important; }
        .dark .metric-indigo { background-color: #312e81 !important; color: #c7d2fe !important; }

        /* Darker total colors */
        .metric-total-green { background-color: #bbf7d0 !important; color: #14532d !important; }
        .metric-total-emerald { background-color: #a7f3d0 !important; color: #064e3b !important; }
        .metric-total-teal { background-color: #99f6e4 !important; color: #134e4a !important; }
        .metric-total-red { background-color: #fecaca !important; color: #7f1d1d !important; }
        .metric-total-rose { background-color: #fda4af !important; color: #881337 !important; }
        .metric-total-pink { background-color: #f9a8d4 !important; color: #831843 !important; }
        .metric-total-blue { background-color: #bfdbfe !important; color: #1e3a8a !important; }
        .metric-total-cyan { background-color: #a5f3fc !important; color: #164e63 !important; }
        .metric-total-amber { background-color: #fde68a !important; color: #78350f !important; }
        .metric-total-orange { background-color: #fed7aa !important; color: #9a3412 !important; }
        .metric-total-lime { background-color: #d9f99d !important; color: #365314 !important; }
        .metric-total-violet { background-color: #ddd6fe !important; color: #4c1d95 !important; }
        .metric-total-purple { background-color: #e9d5ff !important; color: #581c87 !important; }
        .metric-total-indigo { background-color: #c7d2fe !important; color: #312e81 !important; }

        /* Dark mode darker total colors */
        .dark .metric-total-green { background-color: #166534 !important; color: #bbf7d0 !important; }
        .dark .metric-total-emerald { background-color: #065f46 !important; color: #a7f3d0 !important; }
        .dark .metric-total-teal { background-color: #134e4a !important; color: #99f6e4 !important; }
        .dark .metric-total-red { background-color: #991b1b !important; color: #fecaca !important; }
        .dark .metric-total-rose { background-color: #9f1239 !important; color: #fda4af !important; }
        .dark .metric-total-pink { background-color: #831843 !important; color: #f9a8d4 !important; }
        .dark .metric-total-blue { background-color: #1e40af !important; color: #bfdbfe !important; }
        .dark .metric-total-cyan { background-color: #155e75 !important; color: #a5f3fc !important; }
        .dark .metric-total-amber { background-color: #92400e !important; color: #fde68a !important; }
        .dark .metric-total-orange { background-color: #9a3412 !important; color: #fed7aa !important; }
        .dark .metric-total-lime { background-color: #365314 !important; color: #d9f99d !important; }
        .dark .metric-total-violet { background-color: #5b21b6 !important; color: #ddd6fe !important; }
        .dark .metric-total-purple { background-color: #6b21a8 !important; color: #e9d5ff !important; }
        .dark .metric-total-indigo { background-color: #3730a3 !important; color: #c7d2fe !important; }

        /* Sortable column header styles */
        th button {
            transition: all 0.2s ease-in-out;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            width: 100%;
            justify-content: flex-start;
        }

        th button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            transform: translateY(-1px);
        }

        .dark th button:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        th button:active {
            transform: translateY(0);
        }

        /* Active sort indicator */
        th button.active-sort {
            background-color: rgba(59, 130, 246, 0.1);
            color: rgb(59, 130, 246);
        }

        .dark th button.active-sort {
            background-color: rgba(59, 130, 246, 0.2);
            color: rgb(147, 197, 253);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .project-name-cell {
                max-width: 180px;
            }
            
            th button {
                font-size: 0.7rem;
                padding: 0.125rem 0.25rem;
            }
        }
    </style>
@endpush
