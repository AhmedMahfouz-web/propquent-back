<x-filament-panels::page>
    <div class="space-y-6">

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
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus:ring-primary-400 transition duration-150 ease-in-out shadow-sm border border-transparent">
                    Apply Filters
                </button>
            </form>
        </div>

        <!-- Custom Cashflow Table -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Project Cashflow Projection</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Ongoing projects with weekly installments</p>
            </div>

            <!-- Month Headers -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Month Header Row -->
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th rowspan="2"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">
                                Key
                            </th>
                            <th rowspan="2"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">
                                Project
                            </th>
                            <th rowspan="2"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">
                                Status
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
                                    $expectedCash = \App\Filament\Resources\CashflowResource::calculateExpectedCashForWeek(
                                        $weekStart,
                                        $weekEnd,
                                    );
                                @endphp
                                <th class="px-2 py-2 text-center border-r border-gray-300 dark:border-gray-600">
                                    <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ $weekNumber }}</div>
                                    <div
                                        class="text-xs px-2 py-1 rounded {{ $expectedCash >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                        {{ number_format($expectedCash, 0) }}
                                    </div>
                                </th>
                            @endfor
                        </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($this->getFilteredProjects() as $project)
                            <tr class="dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-200 dark:border-gray-600">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $project->key }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-200 dark:border-gray-600">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $project->title }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-200 dark:border-gray-600">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $project->status === 'active'
                                            ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                            : ($project->status === 'pending'
                                                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                        {{ ucfirst($project->status) }}
                                    </span>
                                </td>
                                @for ($i = 0; $i < $totalWeeks; $i++)
                                    @php
                                        $weekStart = $startDate->copy()->addWeeks($i);
                                        $weekEnd = $weekStart->copy()->endOfWeek();
                                        $transactions = $project
                                            ->transactions()
                                            ->where('status', 'pending')
                                            ->whereBetween('due_date', [$weekStart, $weekEnd])
                                            ->get();
                                    @endphp
                                    <td
                                        class="px-2 py-4 text-center border-r border-gray-200 dark:border-gray-600 min-h-[80px]">
                                        @if ($transactions->isEmpty())
                                            <div class="text-gray-400 dark:text-gray-500 text-xs">-</div>
                                        @else
                                            @foreach ($transactions as $transaction)
                                                <div class="mb-1 p-1 rounded text-xs cursor-help
                                                    {{ $transaction->financial_type === 'revenue' ? 'bg-green-50 border border-green-200 text-green-800 dark:bg-green-900 dark:border-green-700 dark:text-green-100' : 'bg-red-50 border border-red-200 text-red-800 dark:bg-red-900 dark:border-red-700 dark:text-red-100' }}"
                                                    title="{{ ucfirst($transaction->financial_type) }} - Due: {{ \Carbon\Carbon::parse($transaction->due_date)->format('M j') }}">
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
</x-filament-panels::page>
