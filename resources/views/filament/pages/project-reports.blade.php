<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filters Section -->
        <x-filament::section>
            <x-slot name="heading">
                Report Filters
            </x-slot>

            <x-slot name="description">
                Configure the date range and filters for your project report
            </x-slot>

            <form wire:submit="applyFilters">
                {{ $this->getFiltersForm() }}

                <div class="mt-6">
                    <x-filament::button type="submit" :disabled="$isLoading">
                        @if ($isLoading)
                            <x-filament::loading-indicator class="h-4 w-4 mr-2" />
                        @endif
                        Apply Filters
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        @if (!empty($reportData['summary']))
            <!-- Summary Section -->
            <x-filament::section>
                <x-slot name="heading">
                    Report Summary
                </x-slot>

                <x-slot name="description">
                    Overview of project metrics for the selected period
                </x-slot>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-6">
                    <div
                        class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col items-center justify-center text-center">
                        <x-heroicon-o-archive-box-arrow-down class="w-10 h-10 mb-3 text-green-500" />
                        <p class="text-sm font-medium text-green-700 dark:text-green-300">Total New Projects</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                            {{ number_format($reportData['summary']['total_new_projects']) }}
                        </p>
                    </div>

                    <div
                        class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col items-center justify-center text-center">
                        <x-heroicon-o-chart-pie class="w-10 h-10 mb-3 text-blue-500" />
                        <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Total Profit</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                            {{ number_format($reportData['summary']['total_profit'], 2) }}
                        </p>
                    </div>

                    <div
                        class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col items-center justify-center text-center">
                        <x-heroicon-o-building-library class="w-10 h-10 mb-3 text-purple-500" />
                        <p class="text-sm font-medium text-purple-700 dark:text-purple-300">Total Evaluation</p>
                        <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                            {{ number_format($reportData['summary']['total_evaluation'], 2) }}
                        </p>
                    </div>

                    <div
                        class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col items-center justify-center text-center">
                        <x-heroicon-o-arrow-path class="w-10 h-10 mb-3 text-blue-500" />
                        <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Ongoing Projects</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                            {{ number_format($reportData['summary']['current_ongoing_projects']) }}
                        </p>
                    </div>

                    <div
                        class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col items-center justify-center text-center">
                        <x-heroicon-o-banknotes class="w-10 h-10 mb-3 text-purple-500" />
                        <p class="text-sm font-medium text-purple-700 dark:text-purple-300">Total Investment</p>
                        <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                            ${{ number_format($reportData['summary']['total_investment'], 2) }}
                        </p>
                    </div>

                    <div
                        class="bg-gradient-to-r from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col items-center justify-center text-center">
                        <x-heroicon-o-chart-bar-square class="w-10 h-10 mb-3 text-emerald-500" />
                        <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Total Revenue</p>
                        <p class="text-2xl font-bold text-emerald-900 dark:text-emerald-100">
                            ${{ number_format($reportData['summary']['total_revenue'], 2) }}
                        </p>
                    </div>

                    <div
                        class="bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col items-center justify-center text-center">
                        <x-heroicon-o-receipt-percent class="w-10 h-10 mb-3 text-orange-500" />
                        <p class="text-sm font-medium text-orange-700 dark:text-orange-300">Average ROI</p>
                        <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">
                            {{ number_format($reportData['summary']['average_roi'], 2) }}%
                        </p>
                    </div>

                    <div
                        class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col items-center justify-center text-center">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-10 h-10 mb-3 text-red-500" />
                        <p class="text-sm font-medium text-red-700 dark:text-red-300">Exited Projects</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">
                            {{ number_format($reportData['summary']['total_exited_projects']) }}
                        </p>
                    </div>

                    @if ($reportData['summary']['best_month'])
                        <div
                            class="bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col items-center justify-center text-center">
                            <x-heroicon-o-calendar-days class="w-10 h-10 mb-3 text-yellow-500" />
                            <p class="text-sm font-medium text-yellow-700 dark:text-yellow-300">Best Month</p>
                            <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">
                                {{ $reportData['summary']['best_month']['month'] }}
                            </p>
                            <div class="text-sm text-yellow-700 dark:text-yellow-300">
                                ${{ number_format($reportData['summary']['best_month']['revenue'], 2) }}
                            </div>
                        </div>
                    @endif

                    <div
                        class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900/20 dark:to-gray-800/20 p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col items-center justify-center text-center">
                        <x-heroicon-o-arrows-right-left class="w-10 h-10 mb-3 text-gray-500" />
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Transactions</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($reportData['summary']['total_transactions']) }}
                        </p>
                    </div>
                </div>
            </x-filament::section>
        @endif

        @if (!empty($reportData['metrics']))
            <!-- Monthly Grid Report -->
            <x-filament::section>
                <x-slot name="heading">
                    Monthly Project Report
                </x-slot>

                <x-slot name="description">
                    Project metrics organized by month (similar to financial reports)
                </x-slot>

                <!-- Navigation Controls -->
                <div class="flex items-center justify-between mb-4 no-print">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Navigate:</span>
                        <x-filament::button size="sm" color="gray" x-data=""
                            x-on:click="document.querySelector('.project-report-table').scrollLeft = 0">
                            <x-heroicon-o-chevron-double-left class="w-4 h-4 mr-1" />
                            Start
                        </x-filament::button>

                        <x-filament::button size="sm" color="gray" x-data=""
                            x-on:click="document.querySelector('.project-report-table').scrollLeft = document.querySelector('.project-report-table').scrollWidth">
                            End
                            <x-heroicon-o-chevron-double-right class="w-4 h-4 ml-1" />
                        </x-filament::button>
                    </div>

                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Use Ctrl+← → for navigation
                        </span>
                        <x-filament::button size="sm" color="gray" x-on:click="window.printReport()">
                            <x-heroicon-o-printer class="w-4 h-4 mr-1" />
                            Print
                        </x-filament::button>
                    </div>
                </div>

                <div class="overflow-x-auto project-report-table relative">
                    <div class="scroll-indicator-left">←</div>
                    <div class="scroll-indicator-right">→</div>
                    <table
                        class="w-full border-collapse bg-white dark:bg-gray-800 rounded-lg shadow project-report-container">
                        <!-- Header Row -->
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700">
                                <th
                                    class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300 sticky left-0 bg-gray-50 dark:bg-gray-700 z-10">
                                    Metric
                                </th>
                                @foreach ($reportData['months'] as $month)
                                    <th
                                        class="border border-gray-200 dark:border-gray-600 px-3 py-3 text-center font-medium text-gray-700 dark:text-gray-300 min-w-[120px]">
                                        {{ $month['label'] }}
                                    </th>
                                @endforeach
                                <th
                                    class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $metricsToShow = [
                                    'new_projects',
                                    'exited_projects',
                                    'ongoing_projects',
                                    'total_investment',
                                    'revenue_generated',
                                    'active_transactions',
                                    'roi_percentage',
                                ];
                            @endphp

                            @foreach ($metricsToShow as $metricKey)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td
                                        class="border border-gray-200 dark:border-gray-600 px-4 py-3 font-medium text-gray-900 dark:text-gray-100 sticky left-0 bg-white dark:bg-gray-800 z-10">
                                        {{ $this->getMetricLabel($metricKey) }}
                                    </td>
                                    @php $total = 0; @endphp
                                    @foreach ($reportData['months'] as $month)
                                        @php
                                            $value = $reportData['metrics'][$month['key']][$metricKey] ?? 0;
                                            if (
                                                in_array($metricKey, [
                                                    'total_investment',
                                                    'revenue_generated',
                                                    'active_transactions',
                                                    'new_projects',
                                                    'exited_projects',
                                                ])
                                            ) {
                                                $total += $value;
                                            }
                                        @endphp
                                        <td
                                            class="border border-gray-200 dark:border-gray-600 px-3 py-3 text-center {{ $this->getMetricColor($metricKey, $value) }}">
                                            {{ $this->formatMetricValue($metricKey, $value) }}
                                        </td>
                                    @endforeach
                                    <td
                                        class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-center font-semibold bg-gray-50 dark:bg-gray-700 {{ $this->getMetricColor($metricKey, $total) }}">
                                        @if ($metricKey === 'roi_percentage')
                                            {{ number_format($reportData['summary']['average_roi'], 2) }}%
                                        @elseif($metricKey === 'ongoing_projects')
                                            {{ number_format($reportData['summary']['current_ongoing_projects']) }}
                                        @else
                                            {{ $this->formatMetricValue($metricKey, $total) }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif

        @if (empty($reportData['metrics']) && !$isLoading)
            <!-- Empty State -->
            <x-filament::section>
                <div class="text-center py-12">
                    <div class="mx-auto h-12 w-12 text-gray-400">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No data available</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        No project data found for the selected filters and date range.
                    </p>
                    <div class="mt-6">
                        <x-filament::button wire:click="resetFilters" color="primary">
                            Reset Filters
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        @endif

        @if ($isLoading)
            <!-- Loading State -->
            <x-filament::section>
                <div class="text-center py-12">
                    <x-filament::loading-indicator class="h-8 w-8 mx-auto" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Loading Report Data</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Please wait while we generate your project report...
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced table navigation
            const reportTable = document.querySelector('.project-report-table');
            if (reportTable) {
                // Add keyboard navigation
                document.addEventListener('keydown', function(e) {
                    if (e.ctrlKey || e.metaKey) {
                        switch (e.key) {
                            case 'ArrowLeft':
                                e.preventDefault();
                                reportTable.scrollLeft -= 120;
                                break;
                            case 'ArrowRight':
                                e.preventDefault();
                                reportTable.scrollLeft += 120;
                                break;
                            case 'Home':
                                e.preventDefault();
                                reportTable.scrollLeft = 0;
                                break;
                            case 'End':
                                e.preventDefault();
                                reportTable.scrollLeft = reportTable.scrollWidth;
                                break;
                        }
                    }
                });

                // Add scroll indicators
                function updateScrollIndicators() {
                    const scrollLeft = reportTable.scrollLeft;
                    const scrollWidth = reportTable.scrollWidth;
                    const clientWidth = reportTable.clientWidth;

                    const leftIndicator = document.querySelector('.scroll-indicator-left');
                    const rightIndicator = document.querySelector('.scroll-indicator-right');

                    if (leftIndicator) {
                        leftIndicator.style.opacity = scrollLeft > 0 ? '1' : '0';
                    }

                    if (rightIndicator) {
                        rightIndicator.style.opacity = scrollLeft < (scrollWidth - clientWidth) ? '1' : '0';
                    }
                }

                reportTable.addEventListener('scroll', updateScrollIndicators);
                updateScrollIndicators();
            }

            // Cell highlighting on hover
            const tableCells = document.querySelectorAll('.project-report-table td, .project-report-table th');
            tableCells.forEach(cell => {
                cell.addEventListener('mouseenter', function() {
                    const table = this.closest('table');
                    const cellIndex = Array.from(this.parentNode.children).indexOf(this);
                    const rowIndex = Array.from(table.rows).indexOf(this.parentNode);

                    // Highlight column
                    table.querySelectorAll(
                            `tr td:nth-child(${cellIndex + 1}), tr th:nth-child(${cellIndex + 1})`)
                        .forEach(c => {
                            c.classList.add('bg-blue-50', 'dark:bg-blue-900/20');
                        });

                    // Highlight row
                    this.parentNode.querySelectorAll('td, th').forEach(c => {
                        c.classList.add('bg-yellow-50', 'dark:bg-yellow-900/20');
                    });
                });

                cell.addEventListener('mouseleave', function() {
                    const table = this.closest('table');
                    table.querySelectorAll('td, th').forEach(c => {
                        c.classList.remove('bg-blue-50', 'dark:bg-blue-900/20',
                            'bg-yellow-50', 'dark:bg-yellow-900/20');
                    });
                });
            });

            // Add tooltips for metric values
            const metricCells = document.querySelectorAll('[data-metric-value]');
            metricCells.forEach(cell => {
                const value = cell.getAttribute('data-metric-value');
                const metric = cell.getAttribute('data-metric-type');
                const month = cell.getAttribute('data-month');

                if (value && metric && month) {
                    cell.title = `${metric} for ${month}: ${value}`;
                }
            });

        });
    </script>
@endpush

@push('styles')
    <style>
        .project-report-table {
            position: relative;
        }

        .scroll-indicator-left,
        .scroll-indicator-right {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.1);
            color: white;
            padding: 10px 5px;
            border-radius: 5px;
            pointer-events: none;
            transition: opacity 0.3s ease;
            z-index: 10;
        }

        .scroll-indicator-left {
            left: 10px;
        }

        .scroll-indicator-right {
            right: 10px;
        }

        .project-report-table th:first-child,
        .project-report-table td:first-child {
            position: sticky;
            left: 0;
            z-index: 5;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .project-report-table th:last-child,
        .project-report-table td:last-child {
            position: sticky;
            right: 0;
            z-index: 5;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
        }

        /* Smooth scrolling */
        .project-report-table {
            scroll-behavior: smooth;
        }

        /* Enhanced hover effects */
        .project-report-table tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.05) !important;
        }

        /* Reduce overall font size */
        .project-report-table {
            font-size: 0.8rem;
        }

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

        /* Responsive improvements */
        @media (max-width: 768px) {
            .project-report-table {
                font-size: 0.75rem;
            }

            .project-report-table th,
            .project-report-table td {
                padding: 0.5rem 0.25rem;
                min-width: 80px;
            }

            .project-name-cell {
                max-width: 180px;
            }
        }

        /* Print styles */
        @media print {
            .project-report-table {
                font-size: 10px;
            }

            .no-print {
                display: none !important;
            }

            .project-report-table th,
            .project-report-table td {
                padding: 4px;
                border: 1px solid #000;
            }
        }
    </style>
@endpush
