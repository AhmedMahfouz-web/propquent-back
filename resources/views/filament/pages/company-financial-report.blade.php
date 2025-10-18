<x-filament-panels::page>
    @php

        // 1. Generate all possible months from 2 years ago to 2 years in the future
        $startDate = now()->subYears(2)->startOfYear();
        $endDate = now()->addYears(2)->endOfYear();
        
        $allMonths = collect();
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $allMonths->push($current->format('Y-m-01'));
            $current->addMonth();
        }
        
        // Reverse to show latest months first
        $allMonths = $allMonths->reverse();

        // 2. Get the selected months from the request, or default to current year.
        $selectedStartMonth = request('start_month', now()->startOfYear()->format('Y-m-01')); // Default to January of current year
        $selectedEndMonth = request('end_month', now()->format('Y-m-01')); // Default to current month

        // 3. Filter months to show only those between start and end month (inclusive)
        $monthsToShow = $allMonths->filter(function ($month) use ($selectedStartMonth, $selectedEndMonth) {
            return $month >= $selectedStartMonth && $month <= $selectedEndMonth;
        })->values();

        // 4. If no months match the filter, show all months
        if ($monthsToShow->isEmpty()) {
            $monthsToShow = $allMonths;
        }

        // 5. Generate complete month range between start and end to ensure no gaps
        if ($selectedStartMonth && $selectedEndMonth) {
            $start = \Carbon\Carbon::parse($selectedStartMonth);
            $end = \Carbon\Carbon::parse($selectedEndMonth);
            $completeMonths = collect();
            
            $current = $start->copy();
            while ($current <= $end) {
                $completeMonths->push($current->format('Y-m-01'));
                $current->addMonth();
            }
            
            $monthsToShow = $completeMonths->reverse(); // Reverse to show latest first
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

    $userFinancials = ['deposits' => [], 'withdrawals' => [], 'net' => []];

    // Initialize all months with zero to prevent undefined key errors
    foreach ($monthsToShow as $month) {
        $userFinancials['deposits'][$month] = 0;
        $userFinancials['withdrawals'][$month] = 0;
        $userFinancials['net'][$month] = 0;
    }
    $userTransactions = App\Models\UserTransaction::query()
        ->select(
            DB::raw("DATE_FORMAT(transaction_date, '%Y-%m-01') as month_date"),
            DB::raw(
                "SUM(CASE WHEN transaction_type = '" .
                    App\Models\UserTransaction::TYPE_DEPOSIT .
                    "' THEN amount ELSE 0 END) as total_deposits",
            ),
            DB::raw(
                "SUM(CASE WHEN transaction_type = '" .
                    App\Models\UserTransaction::TYPE_WITHDRAWAL .
                    "' THEN amount ELSE 0 END) as total_withdrawals",
            ),
        )
        ->where('status', App\Models\UserTransaction::STATUS_DONE)
        ->whereBetween('transaction_date', [
            $monthsToShow->last(), // Earliest month
            Illuminate\Support\Carbon::parse($monthsToShow->first())->endOfMonth(), // End of the latest month
        ])
        ->groupBy('month_date')
        ->get();

    foreach ($userTransactions as $transaction) {
        $month = $transaction->month_date;
        if ($monthsToShow->contains($month)) {
            $userFinancials['deposits'][$month] = $transaction->total_deposits;
            $userFinancials['withdrawals'][$month] = $transaction->total_withdrawals;
            $userFinancials['net'][$month] = $transaction->total_deposits - $transaction->total_withdrawals;
        }
    }

    // 5. Calculate Asset Evaluation using ProjectFinancialReport instance
    $evaluation = ['asset' => [], 'operation' => [], 'total' => []];

    // Initialize evaluation arrays
    foreach ($monthsToShow as $month) {
        $evaluation['asset'][$month] = 0;
        $evaluation['operation'][$month] = 0;
        $evaluation['total'][$month] = 0;
    }

    // Create an instance of ProjectFinancialReport to use its exact calculation method
    $projectFinancialReportInstance = new \App\Filament\Pages\ProjectFinancialReport();
    
    // Set the same date range as the company report
    $projectFinancialReportInstance->startMonth = $selectedStartMonth;
    $projectFinancialReportInstance->endMonth = $selectedEndMonth;
    $projectFinancialReportInstance->readyToLoad = true;

    // Get all projects
    $projects = App\Models\Project::with(['transactions', 'statusChanges', 'valueCorrections'])->get();
    
    // For each project, get its financial data using the exact same method
    foreach ($projects as $project) {
        // Create a query that returns just this project
        $singleProjectQuery = App\Models\Project::where('key', $project->key);
        
        // Get the project's financial data using the exact same method
        $allMonthsForProject = collect();
        $startDate = \Carbon\Carbon::parse($selectedStartMonth);
        $endDate = \Carbon\Carbon::parse($selectedEndMonth);
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $allMonthsForProject->push($current->format('Y-m-01'));
            $current->addMonth();
        }
        $allMonthsForProject = $allMonthsForProject->reverse();
        
        // Use reflection to call the private method or recreate the exact logic
        $projectData = [];
        $projectData['key'] = $project->key;
        $projectData['title'] = $project->title;
        $projectData['status'] = $project->status;
        $projectData['months'] = [];
        $projectData['totals'] = array_fill_keys(['evaluation_asset', 'value_correction', 'expense_operation', 'expense_asset', 'expense_total', 'revenue_operation', 'revenue_asset', 'revenue_total', 'profit_operation', 'profit_asset', 'total_profit'], 0);
        
        foreach ($allMonthsForProject as $month) {
            $projectData['months'][$month] = array_fill_keys(array_keys($projectData['totals']), 0);
        }
        
        $today = now()->startOfDay();

        foreach ($project->transactions as $transaction) {
            $dateToUse = null;
            $transactionDate = \Carbon\Carbon::parse($transaction->transaction_date)->startOfDay();
            $actualDate = $transaction->actual_date ? \Carbon\Carbon::parse($transaction->actual_date)->startOfDay() : null;

            // Only include done transactions - EXACT same logic as ProjectFinancialReport
            if ($transaction->status === 'done') {
                if ($actualDate && $actualDate->lte($today)) {
                    $dateToUse = $transaction->actual_date;
                } elseif (!$actualDate && $transactionDate->lte($today)) {
                    $dateToUse = $transaction->transaction_date;
                } else {
                    continue;
                }
            } else {
                continue;
            }

            $month = date('Y-m-01', strtotime($dateToUse));

            if (isset($projectData['months'][$month]) && $transaction->financial_type && $transaction->serving) {
                $key = $transaction->financial_type . '_' . $transaction->serving;
                if (!isset($projectData['months'][$month][$key])) {
                    $projectData['months'][$month][$key] = 0;
                }
                $projectData['months'][$month][$key] += $transaction->amount;
            }
        }

        // Determine exit month if project is exited
        $exitMonth = null;
        if ($project->status === 'exited' && $project->exit_date) {
            $exitMonth = \Carbon\Carbon::parse($project->exit_date)->format('Y-m');
        }

        // Calculate cumulative asset evaluation from the beginning of time, not just filtered months
        // First, get ALL months that have transactions for this project to build proper cumulative total
        $allHistoricalMonths = collect();
        
        // Get the earliest transaction date for this project
        $earliestTransaction = $project->transactions()
            ->where('status', 'done')
            ->where('serving', 'asset')
            ->orderBy('transaction_date', 'asc')
            ->first();
            
        if ($earliestTransaction) {
            $startFromDate = \Carbon\Carbon::parse($earliestTransaction->transaction_date)->startOfMonth();
            $endToDate = \Carbon\Carbon::parse($selectedEndMonth)->endOfMonth();
            
            $current = $startFromDate->copy();
            while ($current <= $endToDate) {
                $allHistoricalMonths->push($current->format('Y-m-01'));
                $current->addMonth();
            }
            
            // Process ALL historical months to build cumulative total
            $runningTotal = 0;
            $allHistoricalMonths = $allHistoricalMonths->reverse(); // Newest first, then reverse for chronological processing
            $monthsChronological = $allHistoricalMonths->reverse()->toArray(); // Oldest first for cumulative calculation
            
            foreach ($monthsChronological as $month) {
                // Get transactions for this historical month (even if not in display range)
                $monthExpenses = 0;
                $monthRevenues = 0;
                
                foreach ($project->transactions as $transaction) {
                    if ($transaction->status !== 'done' || $transaction->serving !== 'asset') continue;
                    
                    $dateToUse = null;
                    $transactionDate = \Carbon\Carbon::parse($transaction->transaction_date)->startOfDay();
                    $actualDate = $transaction->actual_date ? \Carbon\Carbon::parse($transaction->actual_date)->startOfDay() : null;
                    $today = now()->startOfDay();
                    
                    if ($actualDate && $actualDate->lte($today)) {
                        $dateToUse = $transaction->actual_date;
                    } elseif (!$actualDate && $transactionDate->lte($today)) {
                        $dateToUse = $transaction->transaction_date;
                    } else {
                        continue;
                    }
                    
                    $transactionMonth = date('Y-m-01', strtotime($dateToUse));
                    
                    if ($transactionMonth === $month) {
                        if ($transaction->financial_type === 'expense') {
                            $monthExpenses += $transaction->amount;
                        } elseif ($transaction->financial_type === 'revenue') {
                            $monthRevenues += $transaction->amount;
                        }
                    }
                }
                
                // Get Value Correction for this month
                $monthCorrections = App\Models\ValueCorrection::getCorrectionForMonth($project->key, $month);
                
                // Check if this month is after project exit
                $isAfterExit = false;
                if ($exitMonth && $month >= $exitMonth) {
                    $isAfterExit = true;
                }

                if ($isAfterExit) {
                    $runningTotal = 0;
                } else {
                    $runningTotal = $runningTotal + $monthExpenses - $monthRevenues + $monthCorrections;
                }
                
                // Only store the result if this month is in our display range
                if (in_array($month, $allMonthsForProject->toArray())) {
                    $projectData['months'][$month]['evaluation_asset'] = $runningTotal;
                }
            }
        }
        
        // Add this project's asset evaluation to the company total for each month
        foreach ($monthsToShow as $month) {
            $evaluation['asset'][$month] += $projectData['months'][$month]['evaluation_asset'] ?? 0;
        }
    }

    // Calculate operation evaluation (simple expense - revenue)
    foreach ($monthsToShow as $month) {
        $operationExpense = $reportData['expense']['operation'][$month] ?? 0;
        $operationRevenue = $reportData['revenue']['operation'][$month] ?? 0;
        $evaluation['operation'][$month] = $operationExpense - $operationRevenue;
        
        // Total evaluation
        $evaluation['total'][$month] = $evaluation['asset'][$month] + $evaluation['operation'][$month];
    }

    // 6. Calculate Cash
    $cash = [];
    $previousMonthCash = 0;

    foreach (array_reverse($monthsToShow->toArray()) as $month) {
        $revenue = $monthlyTotals['revenue'][$month] ?? 0;
        $expense = $monthlyTotals['expense'][$month] ?? 0;
        $deposits = $userFinancials['deposits'][$month] ?? 0;
        $withdrawals = $userFinancials['withdrawals'][$month] ?? 0;

        $cash[$month] = $previousMonthCash + $deposits + $revenue - $withdrawals - $expense;
        $previousMonthCash = $cash[$month];
    }

    // 7. Calculate Equity Total (Asset Evaluation + Cash)
    $equityTotal = [];
    foreach ($monthsToShow as $month) {
        $equityTotal[$month] = ($evaluation['asset'][$month] ?? 0) + ($cash[$month] ?? 0);
    }

    // 8. Calculate Profit
    $profit = ['asset' => [], 'operation' => [], 'total' => []];

    // Initialize profit arrays
    foreach ($monthsToShow as $month) {
        $profit['asset'][$month] = 0;
        $profit['operation'][$month] = 0;
        $profit['total'][$month] = 0;
    }

    // Calculate profit for each serving
    $monthsArray = $monthsToShow->toArray();
    foreach ($monthsArray as $index => $month) {
        // Asset Profit = Evaluation Asset for current month - Evaluation Asset for last month + Revenue Asset current month - Expense Asset current month
        $currentEvaluationAsset = $evaluation['asset'][$month];
        $lastMonthEvaluationAsset = isset($monthsArray[$index + 1])
            ? $evaluation['asset'][$monthsArray[$index + 1]]
            : 0;
        $currentRevenueAsset = $reportData['revenue']['asset'][$month] ?? 0;
        $currentExpenseAsset = $reportData['expense']['asset'][$month] ?? 0;

        $profit['asset'][$month] =
            $currentEvaluationAsset - $lastMonthEvaluationAsset + $currentRevenueAsset - $currentExpenseAsset;

        // Operation Profit = Revenue Operation this month - Expenses Operation This Month
        $currentRevenueOperation = $reportData['revenue']['operation'][$month] ?? 0;
        $currentExpenseOperation = $reportData['expense']['operation'][$month] ?? 0;

        $profit['operation'][$month] = $currentRevenueOperation - $currentExpenseOperation;

        // Total Profit = Asset Profit + Operation Profit
        $profit['total'][$month] = $profit['asset'][$month] + $profit['operation'][$month];
            }
        }

    @endphp

    {{-- Debug Section --}}
    {{-- Month Selection Form --}}
    <form action="{{ route('filament.admin.pages.company-financial-report') }}" method="GET"
        class="mb-6 p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label for="start_month" class="block text-sm font-medium text-gray-700 dark:text-gray-200">From
                    Month</label>
                <select name="start_month" id="start_month"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @foreach ($allMonths->reverse() as $month)
                        <option value="{{ $month }}" @if ($month == $selectedStartMonth) selected @endif>
                            {{ date('F Y', strtotime($month)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="end_month" class="block text-sm font-medium text-gray-700 dark:text-gray-200">To
                    Month</label>
                <select name="end_month" id="end_month"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @foreach ($allMonths->reverse() as $month)
                        <option value="{{ $month }}" @if ($month == $selectedEndMonth) selected @endif>
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
            <div>
                <a href="{{ route('filament.admin.pages.company-financial-report') }}"
                    class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 text-center inline-block">
                    Reset Filters
                </a>
            </div>
        </div>
    </form>

    {{-- Integrated Financial Report Table --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Category</th>
                    @foreach ($monthsToShow as $month)
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ date('F Y', strtotime($month)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                {{-- User Financials Section --}}
                @if (!empty($userFinancials['deposits']) || !empty($userFinancials['withdrawals']))
                    <tr class="bg-blue-50 dark:bg-blue-900/20">
                        <th colspan="{{ 1 + $monthsToShow->count() }}"
                            class="px-6 py-4 text-left text-lg font-semibold text-blue-800 dark:text-blue-200 flex items-center gap-2">
                            @svg('heroicon-o-user-group', 'h-6 w-6')
                            <span>User Financials</span>
                        </th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300 pl-12">
                            Total Deposits</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ Illuminate\Support\Number::currency($userFinancials['deposits'][$month] ?? 0, 'USD') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300 pl-12">
                            Total Withdrawals</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ Illuminate\Support\Number::currency($userFinancials['withdrawals'][$month] ?? 0, 'USD') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr class="bg-blue-50 dark:bg-blue-900/20 font-semibold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-800 dark:text-blue-200 pl-12">Net User
                            Deposit</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-800 dark:text-blue-200">
                                {{ Illuminate\Support\Number::currency($userFinancials['net'][$month], 'USD') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr class="h-6">
                        <td colspan="{{ 1 + $monthsToShow->count() }}"></td>
                    </tr>
                @endif

                {{-- Revenue Section --}}
                @if (!empty($reportData['revenue']))
                    <tr class="bg-green-50 dark:bg-green-900/20">
                        <th colspan="{{ 1 + $monthsToShow->count() }}"
                            class="px-6 py-4 text-left text-lg font-semibold text-green-800 dark:text-green-200 flex items-center gap-2">
                            @svg('heroicon-o-banknotes', 'h-6 w-6')
                            <span>Revenue</span>
                        </th>
                    </tr>
                    @foreach ($reportData['revenue'] as $servingName => $monthlyData)
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 pl-12">
                                Revenue {{ ucfirst($servingName) }}</td>
                            @foreach ($monthsToShow as $month)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ Illuminate\Support\Number::currency($monthlyData[$month] ?? 0, 'USD') }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr class="bg-green-50 dark:bg-green-900/20 font-semibold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-800 dark:text-green-200 pl-12">
                            Total Revenue</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-800 dark:text-green-200">
                                {{ Illuminate\Support\Number::currency($monthlyTotals['revenue'][$month] ?? 0, 'USD') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr class="h-6">
                        <td colspan="{{ 1 + $monthsToShow->count() }}"></td>
                    </tr>
                @endif

                {{-- Expense Section --}}
                @if (!empty($reportData['expense']))
                    <tr class="bg-red-50 dark:bg-red-900/20">
                        <th colspan="{{ 1 + $monthsToShow->count() }}"
                            class="px-6 py-4 text-left text-lg font-semibold text-red-800 dark:text-red-200 flex items-center gap-2">
                            @svg('heroicon-o-credit-card', 'h-6 w-6')
                            <span>Expense</span>
                        </th>
                    </tr>
                    @foreach ($reportData['expense'] as $servingName => $monthlyData)
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 pl-12">
                                Expense {{ ucfirst($servingName) }}</td>
                            @foreach ($monthsToShow as $month)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ Illuminate\Support\Number::currency($monthlyData[$month] ?? 0, 'USD') }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr class="bg-red-50 dark:bg-red-900/20 font-semibold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-800 dark:text-red-200 pl-12">
                            Total Expense</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-800 dark:text-red-200">
                                {{ Illuminate\Support\Number::currency($monthlyTotals['expense'][$month] ?? 0, 'USD') }}
                            </td>
                        @endforeach
                    </tr>
                @endif

                {{-- Equity Section --}}
                @if (!empty($evaluation['asset']) || !empty($evaluation['operation']))
                    <tr class="h-6">
                        <td colspan="{{ 1 + $monthsToShow->count() }}"></td>
                    </tr>
                    <tr class="bg-purple-50 dark:bg-purple-900/20">
                        <th colspan="{{ 1 + $monthsToShow->count() }}"
                            class="px-6 py-4 text-left text-lg font-semibold text-purple-800 dark:text-purple-200 flex items-center gap-2">
                            @svg('heroicon-o-scale', 'h-6 w-6')
                            <span>Equity</span>
                        </th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 pl-12">
                            Asset Evaluation</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ Illuminate\Support\Number::currency($evaluation['asset'][$month] ?? 0, 'USD') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 pl-12">
                            Cash</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ Illuminate\Support\Number::currency($cash[$month] ?? 0, 'USD') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr class="bg-purple-50 dark:bg-purple-900/20 font-semibold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-800 dark:text-purple-200 pl-12">
                            Equity Total</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-800 dark:text-purple-200">
                                {{ Illuminate\Support\Number::currency($equityTotal[$month] ?? 0, 'USD') }}
                            </td>
                        @endforeach
                    </tr>
                @endif

                {{-- Profit Section --}}
                @if (!empty($profit['asset']) || !empty($profit['operation']))
                    <tr class="h-6">
                        <td colspan="{{ 1 + $monthsToShow->count() }}"></td>
                    </tr>
                    <tr class="bg-yellow-50 dark:bg-yellow-900/20">
                        <th colspan="{{ 1 + $monthsToShow->count() }}"
                            class="px-6 py-4 text-left text-lg font-semibold text-yellow-800 dark:text-yellow-200 flex items-center gap-2">
                            @svg('heroicon-o-trophy', 'h-6 w-6')
                            <span>Profit</span>
                        </th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 pl-12">
                            Asset Profit</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ Illuminate\Support\Number::currency($profit['asset'][$month], 'USD') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 pl-12">
                            Operation Profit</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ Illuminate\Support\Number::currency($profit['operation'][$month], 'USD') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr class="bg-yellow-50 dark:bg-yellow-900/20 font-semibold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-800 dark:text-yellow-200 pl-12">
                            Total Profit</td>
                        @foreach ($monthsToShow as $month)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-800 dark:text-yellow-200">
                                {{ Illuminate\Support\Number::currency($profit['total'][$month], 'USD') }}
                            </td>
                        @endforeach
                    </tr>
                @endif

                {{-- No Data Message --}}
                @if (empty($reportData['revenue']) &&
                        empty($reportData['expense']) &&
                        empty($userFinancials['deposits']) &&
                        empty($userFinancials['withdrawals']))
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
