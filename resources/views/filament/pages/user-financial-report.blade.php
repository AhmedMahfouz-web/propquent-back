<x-filament-panels::page>
    @php
        // Get all available months for the filter dropdown from both project and user transactions.
        $projectMonths = App\Models\ProjectTransaction::query()
            ->select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
            ->distinct();

        $userMonths = App\Models\UserTransaction::query()
            ->select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
            ->distinct();

        $allMonths = $projectMonths->union($userMonths)->orderBy('month_date', 'desc')->pluck('month_date');

        // Get filter values from the request.
        $selectedMonth = request('start_month', $allMonths->first());
        $search = request('search');
        $perPage = request('per_page', 10);

        $monthsToShow = $allMonths;

        // Get company financial data (needed for profit calculations)
        $reportData = ['revenue' => [], 'expense' => []];
        $monthlyTotals = ['revenue' => [], 'expense' => []];

        if ($monthsToShow->isNotEmpty()) {
            // Initialize totals for all months
            foreach ($monthsToShow as $month) {
                $monthlyTotals['revenue'][$month] = 0;
                $monthlyTotals['expense'][$month] = 0;
            }

            $projectTransactions = DB::table('project_transactions as pt')
                ->select(
                    DB::raw("DATE_FORMAT(pt.transaction_date, '%Y-%m-01') as month_date"),
                    'pt.financial_type as type',
                    'pt.serving as serving_name',
                    DB::raw('SUM(pt.amount) as total_amount'),
                )
                ->whereBetween('pt.transaction_date', [
                    $monthsToShow->last(),
                    Illuminate\Support\Carbon::parse($monthsToShow->first())->endOfMonth(),
                ])
                ->groupBy('month_date', 'pt.financial_type', 'pt.serving')
                ->orderBy('month_date', 'desc')
                ->cursor();

            foreach ($projectTransactions as $transaction) {
                $type = strtolower($transaction->type);
                if ($type !== 'revenue' && $type !== 'expense') {
                    continue;
                }

                $servingName = $transaction->serving_name;
                $month = $transaction->month_date;

                if (!$monthsToShow->contains($month)) {
                    continue;
                }

                if (!isset($reportData[$type][$servingName])) {
                    foreach ($monthsToShow as $m) {
                        $reportData[$type][$servingName][$m] = 0;
                    }
                }

                $reportData[$type][$servingName][$month] = $transaction->total_amount;
                $monthlyTotals[$type][$month] += $transaction->total_amount;
            }

            // Calculate company equity and profit data
            $evaluation = ['asset' => [], 'operation' => [], 'total' => []];
            $cash = [];
            $equityTotal = [];
            $profit = ['asset' => [], 'operation' => [], 'total' => []];

            // Initialize arrays
            foreach ($monthsToShow as $month) {
                $evaluation['asset'][$month] = 0;
                $evaluation['operation'][$month] = 0;
                $evaluation['total'][$month] = 0;
                $profit['asset'][$month] = 0;
                $profit['operation'][$month] = 0;
                $profit['total'][$month] = 0;
            }

            // Calculate evaluation for each serving
            foreach (['asset', 'operation'] as $serving) {
                foreach ($monthsToShow as $month) {
                    $expense = $reportData['expense'][$serving][$month] ?? 0;
                    $revenue = $reportData['revenue'][$serving][$month] ?? 0;
                    $evaluation[$serving][$month] = $expense - $revenue;
                    $evaluation['total'][$month] += $evaluation[$serving][$month];
                }
            }

            // Calculate cash
            $userFinancials = ['deposits' => [], 'withdrawals' => []];
            foreach ($monthsToShow as $month) {
                $userFinancials['deposits'][$month] = 0;
                $userFinancials['withdrawals'][$month] = 0;
            }

            $userTransactions = App\Models\UserTransaction::query()
                ->select(
                    DB::raw("DATE_FORMAT(transaction_date, '%Y-%m-01') as month_date"),
                    DB::raw("SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE 0 END) as total_deposits"),
                    DB::raw("SUM(CASE WHEN transaction_type = 'withdraw' THEN amount ELSE 0 END) as total_withdrawals"),
                )
                ->where('status', App\Models\UserTransaction::STATUS_DONE)
                ->whereBetween('transaction_date', [
                    $monthsToShow->last(),
                    Illuminate\Support\Carbon::parse($monthsToShow->first())->endOfMonth(),
                ])
                ->groupBy('month_date')
                ->get();

            foreach ($userTransactions as $transaction) {
                $month = $transaction->month_date;
                if ($monthsToShow->contains($month)) {
                    $userFinancials['deposits'][$month] = $transaction->total_deposits;
                    $userFinancials['withdrawals'][$month] = $transaction->total_withdrawals;
                }
            }

            $previousMonthCash = 0;
            foreach (array_reverse($monthsToShow->toArray()) as $month) {
                $revenue = $monthlyTotals['revenue'][$month] ?? 0;
                $expense = $monthlyTotals['expense'][$month] ?? 0;
                $deposits = $userFinancials['deposits'][$month] ?? 0;
                $withdrawals = $userFinancials['withdrawals'][$month] ?? 0;

                $cash[$month] = $previousMonthCash + $deposits + $revenue - $withdrawals - $expense;
                $previousMonthCash = $cash[$month];
            }

            // Calculate equity total
            foreach ($monthsToShow as $month) {
                $equityTotal[$month] = ($evaluation['total'][$month] ?? 0) + ($cash[$month] ?? 0);
            }

            // Calculate profit
            $monthsArray = $monthsToShow->toArray();
            foreach ($monthsArray as $index => $month) {
                $currentEvaluationAsset = $evaluation['asset'][$month];
                $lastMonthEvaluationAsset = isset($monthsArray[$index + 1])
                    ? $evaluation['asset'][$monthsArray[$index + 1]]
                    : 0;
                $currentRevenueAsset = $reportData['revenue']['asset'][$month] ?? 0;
                $currentExpenseAsset = $reportData['expense']['asset'][$month] ?? 0;

                $profit['asset'][$month] =
                    $currentEvaluationAsset - $lastMonthEvaluationAsset + $currentRevenueAsset - $currentExpenseAsset;

                $currentRevenueOperation = $reportData['revenue']['operation'][$month] ?? 0;
                $currentExpenseOperation = $reportData['expense']['operation'][$month] ?? 0;

                $profit['operation'][$month] = $currentRevenueOperation - $currentExpenseOperation;
                $profit['total'][$month] = $profit['asset'][$month] + $profit['operation'][$month];
            }

            // Get users with transactions
            $usersQuery = App\Models\User::query()
                ->select('users.id', 'users.full_name')
                ->join('user_transactions as ut', 'users.id', '=', 'ut.user_id')
                ->where('ut.status', App\Models\UserTransaction::STATUS_DONE)
                ->whereBetween('ut.transaction_date', [
                    $monthsToShow->last(),
                    Illuminate\Support\Carbon::parse($monthsToShow->first())->endOfMonth(),
                ])
                ->groupBy('users.id', 'users.full_name')
                ->orderBy('users.full_name');

            if ($search) {
                $usersQuery->where('users.full_name', 'like', '%' . $search . '%');
            }

            $users = $usersQuery->paginate($perPage)->withQueryString();

            // Calculate user financial data
            $userFinancialData = [];
            foreach ($users as $user) {
                $userFinancialData[$user->id] = [
                    'full_name' => $user->full_name,
                    'deposits' => [],
                    'withdrawals' => [],
                    'equity' => [],
                    'equity_percentage' => [],
                    'total_profit' => [],
                    'profit_asset' => [],
                    'profit_operation' => [],
                ];

                // Initialize all months
                foreach ($monthsToShow as $month) {
                    $userFinancialData[$user->id]['deposits'][$month] = 0;
                    $userFinancialData[$user->id]['withdrawals'][$month] = 0;
                    $userFinancialData[$user->id]['equity'][$month] = 0;
                    $userFinancialData[$user->id]['equity_percentage'][$month] = 0;
                    $userFinancialData[$user->id]['total_profit'][$month] = 0;
                    $userFinancialData[$user->id]['profit_asset'][$month] = 0;
                    $userFinancialData[$user->id]['profit_operation'][$month] = 0;
                }

                // Get user transactions
                $userTransactionsData = App\Models\UserTransaction::query()
                    ->select(
                        DB::raw("DATE_FORMAT(transaction_date, '%Y-%m-01') as month_date"),
                        DB::raw("SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE 0 END) as deposits"),
                        DB::raw("SUM(CASE WHEN transaction_type = 'withdraw' THEN amount ELSE 0 END) as withdrawals"),
                    )
                    ->where('user_id', $user->id)
                    ->where('status', App\Models\UserTransaction::STATUS_DONE)
                    ->whereBetween('transaction_date', [
                        $monthsToShow->last(),
                        Illuminate\Support\Carbon::parse($monthsToShow->first())->endOfMonth(),
                    ])
                    ->groupBy('month_date')
                    ->get()
                    ->keyBy('month_date');

                // Calculate user equity and profits
                $previousEquity = 0;
                $previousEquityPercentage = 0;

                foreach (array_reverse($monthsToShow->toArray()) as $month) {
                    $deposits = $userTransactionsData[$month]->deposits ?? 0;
                    $withdrawals = $userTransactionsData[$month]->withdrawals ?? 0;
                    $totalProfit = $profit['total'][$month] ?? 0;
                    $assetProfit = $profit['asset'][$month] ?? 0;
                    $operationProfit = $profit['operation'][$month] ?? 0;

                    $userFinancialData[$user->id]['deposits'][$month] = $deposits;
                    $userFinancialData[$user->id]['withdrawals'][$month] = $withdrawals;

                    // Calculate equity: deposit this month + equity previous month - withdrawn this month + profit this month
                    $userProfitThisMonth = ($totalProfit * $previousEquityPercentage) / 100;
                    $userFinancialData[$user->id]['equity'][$month] =
                        $deposits + $previousEquity - $withdrawals + $userProfitThisMonth;

                    // Calculate equity percentage
                    $totalEquity = $equityTotal[$month] ?? 0;
                    if ($totalEquity > 0) {
                        $userFinancialData[$user->id]['equity_percentage'][$month] =
                            ($userFinancialData[$user->id]['equity'][$month] / $totalEquity) * 100;
                    }

                    // Calculate profits based on previous month's equity percentage
            $userFinancialData[$user->id]['total_profit'][$month] =
                ($totalProfit * $previousEquityPercentage) / 100;
            $userFinancialData[$user->id]['profit_asset'][$month] =
                ($assetProfit * $previousEquityPercentage) / 100;
            $userFinancialData[$user->id]['profit_operation'][$month] =
                ($operationProfit * $previousEquityPercentage) / 100;

            $previousEquity = $userFinancialData[$user->id]['equity'][$month];
            $previousEquityPercentage = $userFinancialData[$user->id]['equity_percentage'][$month];
                }
            }
        } else {
            $users = collect();
            $userFinancialData = [];
        }
    @endphp

    <x-filament::section collapsible>
        <x-slot name="heading">
            <h2 class="text-lg font-semibold">Filter Options</h2>
        </x-slot>
        <form action="{{ route('filament.admin.pages.user-financial-report') }}" method="GET">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label for="search"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Search</label>
                    <x-filament::input id="search" name="search" type="text" value="{{ $search }}"
                        placeholder="Search users..." />
                </div>
                <div>
                    <label for="start_month"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Start
                        Month</label>
                    <x-filament::input.select id="start_month" name="start_month">
                        @foreach ($allMonths as $month)
                            <option value="{{ $month }}" @if ($month == $selectedMonth) selected @endif>
                                {{ date('M Y', strtotime($month)) }}
                            </option>
                        @endforeach
                    </x-filament::input.select>
                </div>
                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Per
                        Page</label>
                    <x-filament::input.select id="per_page" name="per_page">
                        <option value="10" @if ($perPage == 10) selected @endif>10 per page</option>
                        <option value="25" @if ($perPage == 25) selected @endif>25 per page</option>
                        <option value="50" @if ($perPage == 50) selected @endif>50 per page</option>
                        <option value="{{ $users->total() }}" @if ($perPage == $users->total()) selected @endif>All
                        </option>
                    </x-filament::input.select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Generate Report
                    </button>
                </div>
            </div>
        </form>
    </x-filament::section>

    <div class="mt-6 overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-0 bg-gray-50 dark:bg-gray-700 z-10">
                        User
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Metric
                    </th>
                    <th
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Total
                    </th>
                    @foreach ($monthsToShow as $month)
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
                        $metrics = [
                            'deposits' => 'Deposit',
                            'withdrawals' => 'Withdraw',
                            'equity' => 'Equity',
                            'equity_percentage' => 'Equity %',
                            'total_profit' => 'Total Profit',
                            'profit_asset' => 'Profit Asset',
                            'profit_operation' => 'Profit Operation',
                        ];
                        $rowspan = count($metrics);
                    @endphp
                    @if ($userData)
                        @foreach ($metrics as $key => $label)
                            <tr
                                class="{{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }} bg-white dark:bg-gray-800">
                                @if ($loop->first)
                                    <td rowspan="{{ $rowspan }}"
                                        class="px-6 py-4 align-top whitespace-nowrap border-r dark:border-gray-600 sticky left-0 bg-white dark:bg-gray-800 z-10">
                                        <div class="font-bold text-lg">{{ $userData['full_name'] }}</div>
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
                                @foreach ($monthsToShow as $month)
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
                        <td colspan="{{ 3 + count($monthsToShow) }}" class="px-6 py-12 whitespace-nowrap">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" aria-hidden="true">
                                    <path vector-effect="non-scaling-stroke" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No users found</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">There are no users to display.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4">
        @if ($users->count() > 0)
            {{ $users->links() }}
        @endif
    </div>

</x-filament-panels::page>
