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
        $monthsToShow = $allMonths
            ->filter(function ($month) use ($selectedStartMonth, $selectedEndMonth) {
                return $month >= $selectedStartMonth && $month <= $selectedEndMonth;
            })
            ->values();

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
                // Initialize operation and asset arrays to ensure they always exist
                $reportData['revenue']['operation'][$month] = 0;
                $reportData['revenue']['asset'][$month] = 0;
                $reportData['expense']['operation'][$month] = 0;
                $reportData['expense']['asset'][$month] = 0;
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

    // 5. Calculate Asset Evaluation using PRE-CALCULATED values from database
    $evaluation = ['asset' => [], 'operation' => [], 'total' => []];

    // Initialize evaluation arrays
    foreach ($monthsToShow as $month) {
        $evaluation['asset'][$month] = 0;
        $evaluation['operation'][$month] = 0;
        $evaluation['total'][$month] = 0;
    }

    // Get pre-calculated asset evaluations from database - MANUAL SUM to debug
    foreach ($monthsToShow as $month) {
        // Get all project evaluations for this month and sum them manually
        $monthEvaluations = App\Models\MonthlyProjectEvaluation::where('month_date', $month)->get([
            'project_key',
            'asset_evaluation',
        ]);

        $monthTotal = 0;
        foreach ($monthEvaluations as $eval) {
            $monthTotal += $eval->asset_evaluation;
        }

        $evaluation['asset'][$month] = $monthTotal;

        // Debug: Check for duplicates
        $uniqueProjects = $monthEvaluations->pluck('project_key')->unique();
        if ($monthEvaluations->count() != $uniqueProjects->count()) {
            // There are duplicate project records for this month!
            error_log(
                "DUPLICATE RECORDS FOUND for month {$month}: " .
                    $monthEvaluations->count() .
                    ' records, ' .
                    $uniqueProjects->count() .
                    ' unique projects',
            );
        }

        // Debug: Log the calculation details
        error_log(
            "COMPANY REPORT DEBUG - Month {$month}: Found " .
                $monthEvaluations->count() .
                " projects, Total: {$monthTotal}",
        );
        foreach ($monthEvaluations as $eval) {
            error_log("  Project {$eval->project_key}: {$eval->asset_evaluation}");
        }
    }

    // Calculate operation evaluation (display even if zero)
    foreach ($monthsToShow as $month) {
        $operationExpense = $reportData['expense']['operation'][$month] ?? 0;
        $operationRevenue = $reportData['revenue']['operation'][$month] ?? 0;
        $evaluation['operation'][$month] = $operationExpense - $operationRevenue;

        // Total evaluation is just asset evaluation (operation doesn't affect total)
                $evaluation['total'][$month] = $evaluation['asset'][$month];
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

            // 8. Calculate Profit using the same logic as ProjectFinancialReport
            $profit = ['asset' => [], 'operation' => [], 'total' => []];

            // Initialize profit arrays
            foreach ($monthsToShow as $month) {
                $profit['asset'][$month] = 0;
                $profit['operation'][$month] = 0;
                $profit['total'][$month] = 0;
            }

            // Get all projects to calculate individual asset profits and sum them
            $projects = App\Models\Project::with(['transactions', 'valueCorrections'])->get();
            
            // Helper function to calculate project financial data (same logic as ProjectFinancialReport)
            $calculateProjectFinancialData = function($project, $allMonths) {
                $data = ['key' => $project->key, 'title' => $project->title, 'status' => $project->status, 'months' => []];
                foreach ($allMonths as $month) {
                    $data['months'][$month] = [
                        'evaluation_asset' => 0, 'expense_asset' => 0, 'revenue_asset' => 0, 
                        'expense_operation' => 0, 'revenue_operation' => 0, 'profit_asset' => 0
                    ];
                }
                $today = now()->startOfDay();

                // Process transactions
                foreach ($project->transactions as $transaction) {
                    $dateToUse = null;
                    $transactionDate = \Carbon\Carbon::parse($transaction->transaction_date)->startOfDay();
                    $actualDate = $transaction->actual_date ? \Carbon\Carbon::parse($transaction->actual_date)->startOfDay() : null;

                    // Only include done transactions
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

                    if (isset($data['months'][$month]) && $transaction->financial_type && $transaction->serving) {
                        $key = $transaction->financial_type . '_' . $transaction->serving;
                        if (!isset($data['months'][$month][$key])) {
                            $data['months'][$month][$key] = 0;
                        }
                        $data['months'][$month][$key] += $transaction->amount;
                    }
                }

                // Use pre-calculated asset evaluations from database
                $assetEvaluations = \App\Models\MonthlyProjectEvaluation::getProjectEvaluations($project->key, $allMonths);
                
                // Process months in chronological order for profit calculations
                $monthsChronological = array_reverse($allMonths);
                
                // Get the evaluation from the month before our range starts (same logic as ProjectFinancialReport)
                $firstMonth = $monthsChronological[0] ?? null;
                $previousAssetEvaluation = 0;
                if ($firstMonth) {
                    $previousMonth = \Carbon\Carbon::parse($firstMonth)->subMonth()->format('Y-m-01');
                    $previousAssetEvaluation = \App\Models\MonthlyProjectEvaluation::where('project_key', $project->key)
                        ->where('month_date', $previousMonth)
                        ->value('asset_evaluation') ?? 0;
                }

                foreach ($monthsChronological as $monthIndex => $month) {
                    // Use pre-calculated asset evaluation from database
                    $data['months'][$month]['evaluation_asset'] = $assetEvaluations[$month] ?? 0;

                    // Calculate Profit Asset using the correct formula:
                    // Profit Asset = Current month Asset Evaluation - Previous month Asset Evaluation + Current month Revenue Asset - Current month Expense Asset
                    $currentAssetEvaluation = $data['months'][$month]['evaluation_asset'];
                    $currentRevenueAsset = $data['months'][$month]['revenue_asset'] ?? 0;
                    $currentExpenseAsset = $data['months'][$month]['expense_asset'] ?? 0;

                    $data['months'][$month]['profit_asset'] = $currentAssetEvaluation - $previousAssetEvaluation + $currentRevenueAsset - $currentExpenseAsset;

                    // Debug: Log the calculation for this project and month
                    error_log("PROJECT CALC DEBUG - Project {$project->key}, Month {$month}: {$currentAssetEvaluation} - {$previousAssetEvaluation} + {$currentRevenueAsset} - {$currentExpenseAsset} = {$data['months'][$month]['profit_asset']}");

                    // Store current evaluation as previous for next iteration
                    $previousAssetEvaluation = $currentAssetEvaluation;
                }

                return $data;
            };
            
            // Calculate individual project data for each month using the same logic as ProjectFinancialReport
            $projectsData = [];
            foreach ($projects as $project) {
                $projectData = $calculateProjectFinancialData($project, $monthsToShow->toArray());
                $projectsData[$project->key] = $projectData;
            }

            // Calculate profit for each month
            foreach ($monthsToShow as $month) {
                // Asset Profit = Sum of all individual project asset profits (calculated with correct formula)
                $profit['asset'][$month] = 0;
                foreach ($projectsData as $projectKey => $projectData) {
                    $projectAssetProfit = $projectData['months'][$month]['profit_asset'] ?? 0;
                    $profit['asset'][$month] += $projectAssetProfit;
                    
                    // Debug: Log individual project contributions
                    if ($projectAssetProfit != 0) {
                        error_log("COMPANY REPORT DEBUG - Month {$month}, Project {$projectKey}: Asset Profit = {$projectAssetProfit}");
                    }
                }
                
                // Debug: Log total for the month
                error_log("COMPANY REPORT DEBUG - Month {$month}: Total Asset Profit = {$profit['asset'][$month]}");

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
                    class="w-full px-4 py-2 bg-gray-500 text-black rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 text-center inline-block">
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
