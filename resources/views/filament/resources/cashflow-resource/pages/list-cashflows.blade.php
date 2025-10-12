<x-filament-panels::page>
    <div class="space-y-6" wire:init="loadData">

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="flex gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Number of
                        Months</label>
                    <select wire:model.live="monthsFilter"
                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="1">1 Month</option>
                        <option value="2">2 Months</option>
                        <option value="3" selected>3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="9">9 Months</option>
                        <option value="12">12 Months</option>
                        <option value="18">18 Months</option>
                        <option value="24">24 Months</option>
                    </select>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        Revenue/Deposits
                    </span>
                    <span class="inline-flex items-center ml-4">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                        Expenses/Withdrawals
                    </span>
                </div>
            </div>
        </div>

        <!-- Stacked Tables Container -->
        <div class="space-y-6">
            <!-- Project Cashflow Table -->
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Table Header -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Project Cashflow</h3>
                </div>

                <!-- Table Container with Fixed Height and Scrolling -->
                <div class="h-96 overflow-y-auto overflow-x-auto" id="project-table-container">
                    @php
                        $monthsToShow = $this->monthsFilter ?? 3;
                        $baseWidth = 1600; // Base width for 3 months
                        $dynamicWidth = $baseWidth + ($monthsToShow - 3) * 400; // Add 400px per additional month
                        $minWidth = max($dynamicWidth, $baseWidth); // Ensure minimum width
                    @endphp
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700"
                        style="min-width: {{ $minWidth }}px; table-layout: fixed;">
                        <!-- Month Header Row -->
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th rowspan="2" style="width: 7.8125%;"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                    wire:click="sortBy('key')" wire:loading.class="opacity-50">
                                    <div class="flex items-center">
                                        Key
                                        @if ($sortField === 'key')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                                </svg>
                                            @endif
                                        @endif
                                    </div>
                                </th>
                                <th rowspan="2" style="width: 11.71875%;"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                    wire:click="sortBy('title')" wire:loading.class="opacity-50">
                                    <div class="flex items-center">
                                        Project
                                        @if ($sortField === 'title')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                                </svg>
                                            @endif
                                        @endif
                                    </div>
                                </th>
                                <th rowspan="2" style="width: 5.46875%;"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                    wire:click="sortBy('status')" wire:loading.class="opacity-50">
                                    <div class="flex items-center">
                                        Status
                                        @if ($sortField === 'status')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                                </svg>
                                            @endif
                                        @endif
                                    </div>
                                </th>
                                @php
                                    $startDate = now()->startOfWeek();
                                    $monthsToShow = $this->monthsFilter ?? 3;
                                    $totalWeeks = $monthsToShow * 4;
                                    $months = [];
                                    for ($i = 0; $i < $totalWeeks; $i++) {
                                        $weekStart = $startDate->copy()->addWeeks($i);
                                        $monthKey = $weekStart->format('M-Y');
                                        if (!isset($months[$monthKey])) {
                                            $months[$monthKey] = 0;
                                        }
                                        $months[$monthKey]++;
                                    }
                                @endphp
                                @foreach ($months as $monthName => $weekCount)
                                    <th colspan="{{ $weekCount }}"
                                        class="px-3 py-3 text-center text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600 bg-blue-50 dark:bg-blue-900">
                                        {{ $monthName }}
                                    </th>
                                @endforeach
                            </tr>
                            <!-- Week Header Row -->
                            <tr class="bg-gray-100 dark:bg-gray-600">
                                @for ($i = 0; $i < $totalWeeks; $i++)
                                    @php
                                        $weekStart = $startDate->copy()->addWeeks($i);
                                        $weekEnd = $weekStart->copy()->endOfWeek();
                                        $weekNumber = 'W' . (($i % 4) + 1);
                                        $weekField = 'week_' . $i;

                                        $expectedCash = \App\Filament\Resources\CashflowResource::calculateExpectedCashForWeek(
                                            $weekStart,
                                            $weekEnd,
                                        );
                                    @endphp
                                    <th class="px-2 py-2 text-center border-r border-gray-300 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                        style="width: 6.25%;" wire:click="sortByWeek('{{ $weekField }}')">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="flex items-center text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ $weekNumber }}
                                                @if ($weekSortField === $weekField)
                                                    @if ($weekSortDirection === 'asc')
                                                        <svg class="w-3 h-3 ml-1" fill="currentColor"
                                                            viewBox="0 0 20 20">
                                                            <path
                                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                                        </svg>
                                                    @else
                                                        <svg class="w-3 h-3 ml-1" fill="currentColor"
                                                            viewBox="0 0 20 20">
                                                            <path
                                                                d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                                        </svg>
                                                    @endif
                                                @endif
                                            </div>
                                            <div
                                                class="text-xs px-2 py-1 rounded {{ $expectedCash >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                                {{ number_format($expectedCash, 0) }}
                                            </div>
                                        </div>
                                    </th>
                                @endfor
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @php
                                $projects = $this->getFilteredProjects();
                            @endphp
                            @foreach ($projects as $project)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td style="width: 7.8125%;"
                                        class="px-6 py-4 border-r border-gray-200 dark:border-gray-600">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            {{ $project->key }}
                                        </span>
                                    </td>
                                    <td style="width: 11.71875%;"
                                        class="px-6 py-4 border-r border-gray-200 dark:border-gray-600">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $project->title }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $project->developer->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td style="width: 5.46875%;"
                                        class="px-6 py-4 whitespace-nowrap border-r border-gray-200 dark:border-gray-600">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $project->status === 'on-going' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </td>
                                    @for ($i = 0; $i < $totalWeeks; $i++)
                                        @php
                                            $weekStart = $startDate->copy()->addWeeks($i);
                                            $weekEnd = $weekStart->copy()->endOfWeek();

                                            // Get transactions using smart date logic
                                            $today = now()->startOfDay();

                                            $transactions = $project
                                                ->transactions()
                                                ->where(function ($query) use ($today, $weekStart, $weekEnd) {
                                                    $query
                                                        ->where(function ($q) use ($today, $weekStart, $weekEnd) {
                                                            // Done transactions with actual_date in past/present (already completed)
                                                            $q->where('status', 'done')
                                                                ->whereNotNull('actual_date')
                                                                ->where('actual_date', '<=', $today)
                                                                ->whereBetween('actual_date', [$weekStart, $weekEnd]);
                                                        })
                                                        ->orWhere(function ($q) use ($today, $weekStart, $weekEnd) {
                                                            // Done transactions without actual_date but transaction_date in past/present
                                                            $q->where('status', 'done')
                                                                ->whereNull('actual_date')
                                                                ->where('transaction_date', '<=', $today)
                                                                ->whereBetween('transaction_date', [
                                                                    $weekStart,
                                                                    $weekEnd,
                                                                ]);
                                                        })
                                                        ->orWhere(function ($q) use ($today, $weekStart, $weekEnd) {
                                                            // Done transactions with future actual_date (scheduled)
                                                            $q->where('status', 'done')
                                                                ->whereNotNull('actual_date')
                                                                ->where('actual_date', '>', $today)
                                                                ->whereBetween('actual_date', [$weekStart, $weekEnd]);
                                                        })
                                                        ->orWhere(function ($q) use ($today, $weekStart, $weekEnd) {
                                                            // Done transactions with future transaction_date (no actual_date)
                                                            $q->where('status', 'done')
                                                                ->whereNull('actual_date')
                                                                ->where('transaction_date', '>', $today)
                                                                ->whereBetween('transaction_date', [
                                                                    $weekStart,
                                                                    $weekEnd,
                                                                ]);
                                                        })
                                                        ->orWhere(function ($q) use ($today, $weekStart, $weekEnd) {
                                                            // Pending transactions with future due_date
                                                            $q->where('status', 'pending')
                                                                ->where('due_date', '>', $today)
                                                                ->whereBetween('due_date', [$weekStart, $weekEnd]);
                                                        });
                                                })
                                                ->get();
                                        @endphp
                                        <td style="width: 6.25%;"
                                            class="px-2 py-4 text-center border-r border-gray-200 dark:border-gray-600 min-h-[80px]">
                                            @if ($transactions->isEmpty())
                                                <div class="text-gray-400 dark:text-gray-500 text-xs">-</div>
                                            @else
                                                @foreach ($transactions as $transaction)
                                                    <div class="mb-1 p-1 rounded text-xs cursor-help"
                                                        style="{{ $transaction->financial_type === 'revenue' ? 'background-color: #dcfce7; border: 1px solid #86efac; color: #166534; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);' : 'background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);' }}"
                                                        title="{{ ucfirst($transaction->financial_type) }} ({{ ucfirst($transaction->status) }}) - Date: {{ $transaction->status === 'done' ? \Carbon\Carbon::parse($transaction->transaction_date)->format('M j') : \Carbon\Carbon::parse($transaction->due_date)->format('M j') }}">

                                                        <div style="font-size: 11px;">
                                                            {{ number_format($transaction->amount, 0) }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- User Transaction Table -->
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Table Header -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">User Transactions</h3>
                </div>

                <!-- Table Container with Fixed Height and Scrolling -->
                <div class="h-96 overflow-y-auto overflow-x-auto" id="user-table-container">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700"
                        style="min-width: {{ $minWidth }}px; table-layout: fixed;">
                        <!-- Month Header Row -->
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th rowspan="2" style="width: 7.8125%;"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">
                                    <div class="flex items-center">
                                        User ID
                                    </div>
                                </th>
                                <th rowspan="2" style="width: 11.71875%;"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">
                                    <div class="flex items-center">
                                        User Name
                                    </div>
                                </th>
                                <th rowspan="2" style="width: 5.46875%;">&nbsp;</th>
                                @php
                                    $startDate = now()->startOfWeek();
                                    $monthsToShow = $this->monthsFilter ?? 3;
                                    $totalWeeks = $monthsToShow * 4;
                                    $months = [];
                                    for ($i = 0; $i < $totalWeeks; $i++) {
                                        $weekStart = $startDate->copy()->addWeeks($i);
                                        $monthKey = $weekStart->format('M-Y');
                                        if (!isset($months[$monthKey])) {
                                            $months[$monthKey] = 0;
                                        }
                                        $months[$monthKey]++;
                                    }
                                @endphp
                                @foreach ($months as $monthName => $weekCount)
                                    <th colspan="{{ $weekCount }}"
                                        class="px-3 py-3 text-center text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600 bg-blue-50 dark:bg-blue-900">
                                        {{ $monthName }}
                                    </th>
                                @endforeach
                            </tr>
                            <!-- Week Header Row -->
                            <tr class="bg-gray-100 dark:bg-gray-600">
                                @for ($i = 0; $i < $totalWeeks; $i++)
                                    @php
                                        $weekStart = $startDate->copy()->addWeeks($i);
                                        $weekEnd = $weekStart->copy()->endOfWeek();
                                        $weekNumber = 'W' . (($i % 4) + 1);

                                        $expectedCash = \App\Filament\Resources\CashflowResource::calculateExpectedCashForWeek(
                                            $weekStart,
                                            $weekEnd,
                                        );
                                    @endphp
                                    <th class="px-2 py-2 text-center border-r border-gray-300 dark:border-gray-600"
                                        style="width: 6.25%;">
                                        <div class="flex flex-col items-center">
                                            <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ $weekNumber }}
                                            </div>
                                            <div
                                                class="text-xs px-2 py-1 rounded {{ $expectedCash >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                                {{ number_format($expectedCash, 0) }}
                                            </div>
                                        </div>
                                    </th>
                                @endfor
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @php
                                $users = \App\Models\User::with([
                                    'transactions' => function ($query) {
                                        $query->orderBy('transaction_date', 'desc');
                                    },
                                ])
                                    ->orderBy('full_name')
                                    ->get();
                            @endphp
                            @foreach ($users as $user)
                                <tr class="dark:hover:bg-gray-700">
                                    <td style="width: 7.8125%;"
                                        class="px-6 py-4 border-r border-gray-200 dark:border-gray-600">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            {{ $user->custom_id ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td style="width: 11.71875%;"
                                        class="px-6 py-4 border-r border-gray-200 dark:border-gray-600">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $user->full_name }}
                                        </div>
                                    </td>
                                    <td style="width: 5.46875%;">&nbsp;</td>
                                    @for ($i = 0; $i < $totalWeeks; $i++)
                                        @php
                                            $weekStart = $startDate->copy()->addWeeks($i);
                                            $weekEnd = $weekStart->copy()->endOfWeek();
                                            // Get user transactions using smart date logic
                                            $today = now()->startOfDay();

                                            $transactions = $user
                                                ->transactions()
                                                ->where(function ($query) use ($today, $weekStart, $weekEnd) {
                                                    $query
                                                        ->where(function ($q) use ($today, $weekStart, $weekEnd) {
                                                            // Done transactions with actual_date in past/present
                                                            $q->where('status', 'done')
                                                                ->whereNotNull('actual_date')
                                                                ->where('actual_date', '<=', $today)
                                                                ->whereBetween('actual_date', [$weekStart, $weekEnd]);
                                                        })
                                                        ->orWhere(function ($q) use ($today, $weekStart, $weekEnd) {
                                                            // Done transactions without actual_date but transaction_date in past/present
                                                            $q->where('status', 'done')
                                                                ->whereNull('actual_date')
                                                                ->where('transaction_date', '<=', $today)
                                                                ->whereBetween('transaction_date', [
                                                                    $weekStart,
                                                                    $weekEnd,
                                                                ]);
                                                        })
                                                        ->orWhere(function ($q) use ($today, $weekStart, $weekEnd) {
                                                            // Done transactions with future actual_date (scheduled)
                                                            $q->where('status', 'done')
                                                                ->whereNotNull('actual_date')
                                                                ->where('actual_date', '>', $today)
                                                                ->whereBetween('actual_date', [$weekStart, $weekEnd]);
                                                        })
                                                        ->orWhere(function ($q) use ($today, $weekStart, $weekEnd) {
                                                            // Done transactions with future transaction_date (no actual_date)
                                                            $q->where('status', 'done')
                                                                ->whereNull('actual_date')
                                                                ->where('transaction_date', '>', $today)
                                                                ->whereBetween('transaction_date', [
                                                                    $weekStart,
                                                                    $weekEnd,
                                                                ]);
                                                        })
                                                        ->orWhere(function ($q) use ($today, $weekStart, $weekEnd) {
                                                            // Pending transactions with future transaction_date
                                                            $q->where('status', 'pending')
                                                                ->where('transaction_date', '>', $today)
                                                                ->whereBetween('transaction_date', [
                                                                    $weekStart,
                                                                    $weekEnd,
                                                                ]);
                                                        });
                                                })
                                                ->get();
                                        @endphp
                                        <td style="width: 6.25%;"
                                            class="px-2 py-4 text-center border-r border-gray-200 dark:border-gray-600 min-h-[80px]">
                                            @if ($transactions->isEmpty())
                                                <div class="text-gray-400 dark:text-gray-500 text-xs">-</div>
                                            @else
                                                @foreach ($transactions as $transaction)
                                                    <div class="mb-1 p-1 rounded text-xs cursor-help"
                                                        style="{{ $transaction->transaction_type === 'deposit' ? 'background-color: #dcfce7; border: 1px solid #86efac; color: #166534; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);' : 'background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);' }}"
                                                        title="{{ ucfirst($transaction->transaction_type) }} ({{ ucfirst($transaction->status) }}) - Date: {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M j') }}">
                                                        <div
                                                            style="font-size: 10px; font-weight: 500; margin-bottom: 2px;">
                                                            {{ $transaction->transaction_type === 'deposit' ? 'DEP' : 'WTH' }}
                                                        </div>
                                                        <div style="font-size: 11px;">
                                                            {{ number_format($transaction->amount, 0) }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Synchronized Horizontal Scroll Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const projectContainer = document.getElementById('project-table-container');
            const userContainer = document.getElementById('user-table-container');

            let isScrolling = false;

            // Sync horizontal scroll from project table to user table
            projectContainer.addEventListener('scroll', function() {
                if (!isScrolling) {
                    isScrolling = true;
                    userContainer.scrollLeft = this.scrollLeft;
                    setTimeout(() => {
                        isScrolling = false;
                    }, 10);
                }
            });

            // Sync horizontal scroll from user table to project table
            userContainer.addEventListener('scroll', function() {
                if (!isScrolling) {
                    isScrolling = true;
                    projectContainer.scrollLeft = this.scrollLeft;
                    setTimeout(() => {
                        isScrolling = false;
                    }, 10);
                }
            });
        });
    </script>

</x-filament-panels::page>
