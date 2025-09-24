<x-filament-panels::page>
    <div class="space-y-6" wire:init="loadData">

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <form wire:submit.prevent="filterTable" class="flex gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Number of
                        Months</label>
                    <select wire:model="monthsFilter"
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
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus:ring-primary-400 transition duration-150 ease-in-out shadow-sm border border-transparent">
                    Apply Filters
                </button>
            </form>
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
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700" style="min-width: 1200px;">
                            <!-- Month Header Row -->
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th rowspan="2" style="width: 120px;"
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
                                    <th rowspan="2" style="width: 250px;"
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
                                    <th rowspan="2" style="width: 120px;"
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
                                        <th class="px-2 py-2 text-center border-r border-gray-300 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" style="width: 80px;"
                                            wire:click="sortByWeek('{{ $weekField }}')">
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
                                        <td style="width: 120px;"
                                            class="px-6 py-4 whitespace-nowrap border-r border-gray-200 dark:border-gray-600">
                                            <span
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                                {{ $project->key }}
                                            </span>
                                        </td>
                                        <td style="width: 250px;"
                                            class="px-6 py-4 whitespace-nowrap border-r border-gray-200 dark:border-gray-600">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $project->title }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $project->developer->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td style="width: 120px;"
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

                                                // Get done transactions by transaction_date and pending by due_date
                                                $doneTransactions = $project
                                                    ->transactions()
                                                    ->where('status', 'done')
                                                    ->whereBetween('transaction_date', [$weekStart, $weekEnd])
                                                    ->get();

                                                $pendingTransactions = $project
                                                    ->transactions()
                                                    ->where('status', 'pending')
                                                    ->whereBetween('due_date', [$weekStart, $weekEnd])
                                                    ->get();

                                                $transactions = $doneTransactions->merge($pendingTransactions);
                                            @endphp
                                            <td style="width: 80px;"
                                                class="px-2 py-4 text-center border-r border-gray-200 dark:border-gray-600 min-h-[80px]">
                                                @if ($transactions->isEmpty())
                                                    <div class="text-gray-400 dark:text-gray-500 text-xs">-</div>
                                                @else
                                                    @foreach ($transactions as $transaction)
                                                        <div class="mb-1 p-1 rounded text-xs cursor-help
                                                    {{ $transaction->financial_type === 'revenue' ? 'bg-green-50 border border-green-200 text-green-800 dark:bg-green-900 dark:border-green-700 dark:text-green-100' : 'bg-red-50 border border-red-200 text-red-800 dark:bg-red-900 dark:border-red-700 dark:text-red-100' }}"
                                                            title="{{ ucfirst($transaction->financial_type) }} ({{ ucfirst($transaction->status) }}) - Date: {{ $transaction->status === 'done' ? \Carbon\Carbon::parse($transaction->transaction_date)->format('M j') : \Carbon\Carbon::parse($transaction->due_date)->format('M j') }}">
                                                            {{ number_format($transaction->amount, 0) }}
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
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700" style="min-width: 1200px;">
                            <!-- Month Header Row -->
                            <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th rowspan="2" style="width: 120px;"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">
                                    <div class="flex items-center">
                                        User ID
                                    </div>
                                </th>
                                <th rowspan="2" style="width: 250px;"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">
                                    <div class="flex items-center">
                                        User Name
                                    </div>
                                </th>
                                <th rowspan="2" style="width: 120px;"></th>
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
                                    <th class="px-2 py-2 text-center border-r border-gray-300 dark:border-gray-600" style="width: 80px;">
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
                                    <td style="width: 120px;"
                                        class="px-6 py-4 whitespace-nowrap border-r border-gray-200 dark:border-gray-600">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            {{ $user->custom_id ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td style="width: 250px;"
                                        class="px-6 py-4 whitespace-nowrap border-r border-gray-200 dark:border-gray-600">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $user->full_name }}
                                        </div>
                                    </td>
                                    <td style="width: 120px;"></td>
                                    @for ($i = 0; $i < $totalWeeks; $i++)
                                        @php
                                            $weekStart = $startDate->copy()->addWeeks($i);
                                            $weekEnd = $weekStart->copy()->endOfWeek();
                                            $transactions = $user
                                                ->transactions()
                                                ->where('status', '!=', 'cancelled')
                                                ->whereBetween('transaction_date', [$weekStart, $weekEnd])
                                                ->get();
                                        @endphp
                                        <td style="width: 80px;"
                                            class="px-2 py-4 text-center border-r border-gray-200 dark:border-gray-600 min-h-[80px]">
                                            @if ($transactions->isEmpty())
                                                <div class="text-gray-400 dark:text-gray-500 text-xs">-</div>
                                            @else
                                                @foreach ($transactions as $transaction)
                                                    <div class="mb-1 p-1 rounded text-xs cursor-help
                                                    {{ $transaction->transaction_type === 'deposit' ? 'bg-green-50 border border-green-200 text-green-800 dark:bg-green-900 dark:border-green-700 dark:text-green-100' : 'bg-red-50 border border-red-200 text-red-800 dark:bg-red-900 dark:border-red-700 dark:text-red-100' }}"
                                                        title="{{ ucfirst($transaction->transaction_type) }} ({{ ucfirst($transaction->status) }}) - Date: {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M j') }}">
                                                        {{ number_format($transaction->amount, 0) }}
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
