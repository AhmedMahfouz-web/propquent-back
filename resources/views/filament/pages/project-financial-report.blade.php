<x-filament-panels::page>
    <div class="overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-0 bg-gray-50 dark:bg-gray-700 z-10">Project</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Metric</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total</th>
                    @foreach ($allMonths as $month)
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">{{ date('M Y', strtotime($month)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($financialSummary as $projectKey => $projectData)
                    <tr class="bg-white dark:bg-gray-800">
                        <td rowspan="3" class="px-6 py-4 align-top whitespace-nowrap border-r dark:border-gray-600 sticky left-0 bg-white dark:bg-gray-800 z-10">
                            <div class="font-bold text-lg">{{ $projectData['title'] }}</div>
                            <div class="text-sm text-gray-500">{{ $projectData['status'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-green-600 dark:text-green-400">Revenue</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-green-600 dark:text-green-400">${{ number_format($projectData['totals']['revenue'], 2) }}</td>
                        @foreach ($allMonths as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-right text-green-600 dark:text-green-400">${{ number_format($projectData['months'][$month]['revenue'] ?? 0, 2) }}</td>
                        @endforeach
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-6 py-4 whitespace-nowrap text-red-600 dark:text-red-400">Expense</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-red-600 dark:text-red-400">${{ number_format($projectData['totals']['expense'], 2) }}</td>
                        @foreach ($allMonths as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-right text-red-600 dark:text-red-400">${{ number_format($projectData['months'][$month]['expense'] ?? 0, 2) }}</td>
                        @endforeach
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-700 font-bold">
                        <td class="px-6 py-4 whitespace-nowrap">Profit</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">${{ number_format($projectData['totals']['profit'], 2) }}</td>
                        @foreach ($allMonths as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-right">${{ number_format($projectData['months'][$month]['profit'] ?? 0, 2) }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + count($allMonths) }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No projects found</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">There is no financial data to display.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
