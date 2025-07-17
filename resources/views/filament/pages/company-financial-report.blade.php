<x-filament-panels::page>
    @php
        // 1. Get all available months for the filter dropdown from project transactions.
        $allMonths = App\Models\ProjectTransaction::query()
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

        // 3. Build and execute the query for project transactions using a cursor for memory efficiency.
        $reportData = ['revenue' => [], 'expense' => []];
        $monthlyTotals = ['revenue' => [], 'expense' => []];

        if ($monthsToShow->isNotEmpty()) {
            // Initialize totals for all months to ensure they exist
            foreach ($monthsToShow as $month) {
                $monthlyTotals['revenue'][$month] = 0;
                $monthlyTotals['expense'][$month] = 0;
            }

            $projectTransactions = DB::table('project_transaction as pt')
                ->select(
                    DB::raw("DATE_FORMAT(pt.transaction_date, '%Y-%m-01') as month_date"),
                    'pt.type',
                    'pt.serving as serving_name',
                    DB::raw('SUM(pt.amount) as total_amount')
                )
                ->whereBetween('pt.transaction_date', [
                    $monthsToShow->last(),
                    Illuminate\Support\Carbon::parse($monthsToShow->first())->endOfMonth(),
                ])
                ->groupBy('month_date', 'pt.type', 'pt.serving')
                ->orderBy('month_date', 'desc')
                ->cursor(); // Use a cursor to process results one by one

            // Structure data for the view
            foreach ($projectTransactions as $transaction) {
                $type = strtolower($transaction->type);
                if ($type !== 'revenue' && $type !== 'expense') {
                    continue;
                }

                $servingName = $transaction->serving_name;
                $month = $transaction->month_date;

                // Ensure the month from the transaction is one of the selected months to show
                if (!$monthsToShow->contains($month)) {
                    continue;
                }

                // Initialize the serving array for the type if it doesn't exist
                if (!isset($reportData[$type][$servingName])) {
                    // Initialize all months for this new serving to 0
                    foreach ($monthsToShow as $m) {
                        $reportData[$type][$servingName][$m] = 0;
                    }
                }

                // Assign the amount and add to monthly totals
                $reportData[$type][$servingName][$month] = $transaction->total_amount;
                $monthlyTotals[$type][$month] += $transaction->total_amount;
            }
        }

        // 4. حساب الماليات للمستخدمين - بسيط وسهل
        $userFinancials = ['deposits' => [], 'withdrawals' => [], 'net' => []];

        // تهيئة الشهور بصفر
        foreach ($monthsToShow as $month) {
            $userFinancials['deposits'][$month] = 0;
            $userFinancials['withdrawals'][$month] = 0;
            $userFinancials['net'][$month] = 0;
        }

        // جلب كل المعاملات المكتملة من البداية
        $allTransactions = App\Models\UserTransaction::where('status', 'Done')
            ->orderBy('transaction_date', 'asc')
            ->get();

        // حساب الصافي التراكمي من البداية
        $runningTotal = 0;
        $monthlyData = [];

        // تجميع المعاملات حسب الشهر
        foreach ($allTransactions as $transaction) {
            $month = $transaction->transaction_date->format('Y-m-01');
            
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = ['deposits' => 0, 'withdrawals' => 0];
            }
            
            if ($transaction->type == 'Deposit') {
                $monthlyData[$month]['deposits'] += $transaction->amount;
            } else {
                $monthlyData[$month]['withdrawals'] += $transaction->amount;
            }
        }

        // حساب الصافي التراكمي لكل شهر
        foreach ($monthlyData as $month => $data) {
            $monthNet = $data['deposits'] - $data['withdrawals'];
            $runningTotal += $monthNet;
            
            // إذا كان الشهر في التقرير، احفظ البيانات
            if ($monthsToShow->contains($month)) {
                $userFinancials['deposits'][$month] = $data['deposits'];
                $userFinancials['withdrawals'][$month] = $data['withdrawals'];
                $userFinancials['net'][$month] = $runningTotal;
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

    {{-- Integrated Financial Report Table --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                    @foreach ($monthsToShow as $month)
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ date('F Y', strtotime($month)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                {{-- User Financials Section --}}
                @if (!empty($userFinancials['deposits']) || !empty($userFinancials['withdrawals']))
                    <tr class="bg-blue-50 dark:bg-blue-900/20">
                        <th colspan="{{ 1 + $monthsToShow->count() }}" class="px-6 py-4 text-left text-lg font-semibold text-blue-800 dark:text-blue-200 flex items-center gap-2">
                            @svg('heroicon-o-user-group', 'h-6 w-6')
                            <span>User Financials</span>
                        </th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300 pl-12">Total Deposits</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ Illuminate\Support\Number::currency($userFinancials['deposits'][$month] ?? 0, 'USD') }}</td>
                        @endforeach
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300 pl-12">Total Withdrawals</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ Illuminate\Support\Number::currency($userFinancials['withdrawals'][$month] ?? 0, 'USD') }}</td>
                        @endforeach
                    </tr>
                    <tr class="bg-blue-50 dark:bg-blue-900/20 font-semibold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-800 dark:text-blue-200 pl-12">Net User Deposit</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-800 dark:text-blue-200">{{ Illuminate\Support\Number::currency($userFinancials['net'][$month] ?? 0, 'USD') }}</td>
                        @endforeach
                    </tr>
                    <tr class="h-6"><td colspan="{{ 1 + $monthsToShow->count() }}"></td></tr>
                @endif

                {{-- Revenue and Expense Sections --}}
                @foreach (['revenue', 'expense'] as $type)
                    @if (!empty($reportData[$type]))
                        @php
                            $sectionColor = $type === 'revenue' ? 'green' : 'red';
                            $icon = $type === 'revenue' ? 'heroicon-o-banknotes' : 'heroicon-o-credit-card';
                        @endphp
                        <tr class="bg-{{ $sectionColor }}-50 dark:bg-{{ $sectionColor }}-900/20">
                            <th colspan="{{ 1 + $monthsToShow->count() }}" class="px-6 py-4 text-left text-lg font-semibold text-{{ $sectionColor }}-800 dark:text-{{ $sectionColor }}-200 flex items-center gap-2">
                                @svg($icon, 'h-6 w-6')
                                <span>{{ ucfirst($type) }}</span>
                            </th>
                        </tr>
                        @foreach ($reportData[$type] as $servingName => $monthlyData)
                            <tr class="bg-white dark:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 pl-12">{{ $servingName }}</td>
                                @foreach ($monthsToShow as $month)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ Illuminate\Support\Number::currency($monthlyData[$month] ?? 0, 'USD') }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="bg-{{ $sectionColor }}-50 dark:bg-{{ $sectionColor }}-900/20 font-semibold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-{{ $sectionColor }}-800 dark:text-{{ $sectionColor }}-200 pl-12">Total {{ ucfirst($type) }}</td>
                            @foreach ($monthsToShow as $month)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-{{ $sectionColor }}-800 dark:text-{{ $sectionColor }}-200">{{ Illuminate\Support\Number::currency($monthlyTotals[$type][$month] ?? 0, 'USD') }}</td>
                            @endforeach
                        </tr>
                        @if (!$loop->last)
                            <tr class="h-6"><td colspan="{{ 1 + $monthsToShow->count() }}"></td></tr>
                        @endif
                    @endif
                @endforeach

                {{-- No Data Message --}}
                @if (empty($reportData['revenue']) && empty($reportData['expense']) && empty($userFinancials['deposits']) && empty($userFinancials['withdrawals']))
                    <tr>
                        <td colspan="{{ 1 + $monthsToShow->count() }}" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                @svg('heroicon-o-chart-pie', 'h-12 w-12 text-gray-400')
                                <p class="mt-4 text-lg">No financial data found for the selected period.</p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
