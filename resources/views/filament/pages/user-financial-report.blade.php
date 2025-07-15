<x-filament-panels::page>
    @php
        // Get all available months for the filter dropdown.
        $allMonths = \App\Models\UserTransaction::query()
            ->select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
            ->distinct()
            ->orderBy('month_date', 'desc')
            ->pluck('month_date');

        // Get filter values from the request.
        $selectedMonth = request('start_month', $allMonths->first());
        $search = request('search');

        $monthsToShow = collect();
        if ($selectedMonth) {
            $startIndex = $allMonths->search($selectedMonth);
            if ($startIndex !== false) {
                $monthsToShow = $allMonths->slice($startIndex, 6); // Show 6 months
            }
        }

        // If no valid selection, default to the last 6 months.
        if ($monthsToShow->isEmpty()) {
            $monthsToShow = $allMonths->take(6); // Show 6 months
        }

        // Build the pivot query.
        $query = \App\Models\User::query()
            ->select('users.id', 'users.full_name')
            ->selectRaw('SUM(CASE WHEN ut.type = \'deposit\' THEN ut.amount ELSE -ut.amount END) as total_net_deposit');

        // Apply search filter if a search term is provided.
        if ($search) {
            $query->where('users.full_name', 'like', '%' . $search . '%');
        }

        if ($monthsToShow->isNotEmpty()) {
            foreach ($monthsToShow as $month) {
                $monthName = date('F_Y', strtotime($month));
                $query->selectRaw(
                    "SUM(CASE WHEN ut.type = 'deposit' AND DATE_FORMAT(ut.transaction_date, '%Y-%m-01') = ? THEN ut.amount ELSE 0 END) as `Deposit_{$monthName}`",
                    [$month]
                );
                $query->selectRaw(
                    "SUM(CASE WHEN ut.type = 'withdraw' AND DATE_FORMAT(ut.transaction_date, '%Y-%m-01') = ? THEN ut.amount ELSE 0 END) as `Withdraw_{$monthName}`",
                    [$month]
                );
            }
            $results = $query
                ->join('user_transactions as ut', 'users.id', '=', 'ut.user_id')
                ->groupBy('users.id', 'users.full_name')
                ->paginate(15)
                ->withQueryString(); // Preserve filters in pagination
        } else {
            $results = collect();
        }

    @endphp

    {{-- Filters Form --}}
    <form action="{{ route('filament.admin.pages.user-financial-report') }}" method="GET" class="mb-6 p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            {{-- Month Selector --}}
            <div>
                <label for="start_month" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Starting Month</label>
                <select name="start_month" id="start_month" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @foreach($allMonths as $month)
                        <option value="{{ $month }}" @if($month == $selectedMonth) selected @endif>
                            {{ date('F Y', strtotime($month)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- User Search --}}
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Search User</label>
                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Enter user's full name..." class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            {{-- Submit Button --}}
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
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
                    <th rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 align-bottom">Full Name</th>
                    <th rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 align-bottom border-r border-gray-300 dark:border-gray-600">Overall Net Deposit</th>
                    @foreach($monthsToShow as $month)
                        <th colspan="2" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 border-l border-gray-300 dark:border-gray-600">{{ date('F Y', strtotime($month)) }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach($monthsToShow as $month)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 border-l border-gray-300 dark:border-gray-600">Deposit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Withdraw</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800">
                @forelse($results as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $user->full_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm @if($user->total_net_deposit >= 0) text-success-600 @else text-danger-600 @endif border-r border-gray-300 dark:border-gray-600">{{ \Illuminate\Support\Number::currency($user->total_net_deposit, 'USD') }}</td>
                        @foreach($monthsToShow as $month)
                            @php
                                $depositMonthName = 'Deposit_' . date('F_Y', strtotime($month));
                                $withdrawMonthName = 'Withdraw_' . date('F_Y', strtotime($month));
                                $deposit = $user->{$depositMonthName};
                                $withdraw = $user->{$withdrawMonthName};
                            @endphp
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-success-600 border-l border-gray-300 dark:border-gray-600">{{ \Illuminate\Support\Number::currency($deposit, 'USD') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-danger-600">{{ \Illuminate\Support\Number::currency($withdraw, 'USD') }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 2 + ($monthsToShow->count() * 2) }}" class="px-6 py-4 text-center text-sm text-gray-500">No matching records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{-- Add pagination links --}}
        @if($results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->hasPages())
            <div class="p-4">
                {{ $results->links() }}
            </div>
        @endif
    </div>

</x-filament-panels::page>
