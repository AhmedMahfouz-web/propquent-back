<x-filament-panels::page>
    @php
        // 1. Get all available months for the filter dropdown from project transactions.
        $allMonths = \App\Models\ProjectTransaction::query()
            ->select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
            ->distinct()
            ->orderBy('month_date', 'desc')
            ->pluck('month_date');

        // 2. Get the selected month from the request, or default to the latest one.
        $selectedMonth = request('start_month', $allMonths->first());

        $monthsToShow = collect();
        if ($selectedMonth) {
            $startIndex = $allMonths->search($selectedMonth);
            if ($startIndex !== false) {
                $monthsToShow = $allMonths->slice($startIndex, 12); // Show 12 months
            }
        }
        if ($monthsToShow->isEmpty()) {
            $monthsToShow = $allMonths->take(12); // Default to last 12 months
        }

        // 3. Build and execute the pivot query.
        $results = [];
        $monthlyTotals = ['revenue' => [], 'expense' => []];

        if ($monthsToShow->isNotEmpty()) {
            $selects = [];
            foreach ($monthsToShow as $month) {
                $monthColumn = 'm_' . date('Y_m', strtotime($month));
                $selects[] = "SUM(CASE WHEN DATE_FORMAT(pt.transaction_date, '%Y-%m-01') = '{$month}' THEN pt.amount ELSE 0 END) as `{$monthColumn}`";
                // Initialize totals
                $monthlyTotals['revenue'][$month] = 0;
                $monthlyTotals['expense'][$month] = 0;
            }
            $selectsString = implode(', ', $selects);

            $query = "
                SELECT
                    pt.type,
                    pt.serving as serving_name,
                    {$selectsString}
                FROM project_transaction pt
                WHERE pt.transaction_date BETWEEN '{$monthsToShow->last()}' AND LAST_DAY('{$monthsToShow->first()}')
                GROUP BY pt.type, pt.serving
                ORDER BY pt.type, pt.serving
            ";

            $results = DB::select($query);

            // Structure data for the view, ensuring keys always exist
            $reportData = ['revenue' => [], 'expense' => []];
            foreach ($results as $row) {
                $type = strtolower($row->type); // Standardize to lowercase
                $servingName = $row->serving_name;

                // Skip any unexpected types
                if ($type !== 'revenue' && $type !== 'expense') {
                    continue;
                }

                // Initialize the serving array for the type if it doesn't exist
        if (!isset($reportData[$type][$servingName])) {
            $reportData[$type][$servingName] = [];
        }

        foreach ($monthsToShow as $month) {
            $monthColumn = 'm_' . date('Y_m', strtotime($month));
                    $amount = $row->$monthColumn;
                    $reportData[$type][$servingName][$month] = $amount;
                    $monthlyTotals[$type][$month] += $amount;
                }
            }
        }
    @endphp

    {{-- Month Selection Form --}}
    <form action="{{ route('filament.admin.pages.company-financial-report') }}" method="GET"
        class="mb-6 p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
            <div>
                <label for="start_month" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Starting
                    Month</label>
                <select name="start_month" id="start_month"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @foreach ($allMonths as $month)
                        <option value="{{ $month }}" @if ($month == $selectedMonth) selected @endif>
                            {{ date('F Y', strtotime($month)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit"
                    class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Generate Report
                </button>
            </div>
        </div>
    </form>

    {{-- Financial Report Table --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Category/Serving</th>
                    @foreach ($monthsToShow as $month)
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                            {{ date('F Y', strtotime($month)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach (['revenue', 'expense'] as $type)
                    @if (!empty($reportData[$type]))
                        @php
                            $servings = $reportData[$type];
                            $typeTotal = $monthlyTotals[$type];
                        @endphp
                        {{-- Section Header --}}
                        <tr class="bg-gray-100 dark:bg-gray-700/50">
                            <th colspan="{{ 1 + $monthsToShow->count() }}"
                                class="px-6 py-3 text-left text-md font-bold text-gray-900 dark:text-white">
                                {{ ucfirst($type) }}
                            </th>
                        </tr>

                        {{-- Data Rows --}}
                        @foreach ($servings as $servingName => $monthlyData)
                            <tr class="bg-white dark:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $servingName }}</td>
                                @foreach ($monthsToShow as $month)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ \Illuminate\Support\Number::currency($monthlyData[$month] ?? 0, 'USD') }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        {{-- Total Row --}}
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b-2 border-gray-300 dark:border-gray-600">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                Total {{ ucfirst($type) }}</td>
                            @foreach ($monthsToShow as $month)
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                    {{ \Illuminate\Support\Number::currency($typeTotal[$month] ?? 0, 'USD') }}</td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach

                {{-- No Data Message --}}
                @if (empty($reportData['revenue']) && empty($reportData['expense']))
                    <tr class="bg-white dark:bg-gray-800">
                        <td colspan="{{ 1 + $monthsToShow->count() }}"
                            class="px-6 py-4 text-center text-sm text-gray-500">No transaction data found for the
                            selected period.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
