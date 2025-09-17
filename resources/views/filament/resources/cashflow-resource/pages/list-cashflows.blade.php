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

        <!-- Project Transactions Table -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Project Transactions</h3>
            </div>

            <!-- Scrollable Table Container -->
            <div class="overflow-x-auto" style="max-height: 400px; overflow-y: auto;">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Project Transactions Header -->
                    <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Project Key
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Project Title
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Due Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>

                    <!-- Project Transactions Body -->
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $projectTransactions = \App\Models\ProjectTransaction::with('project')
                                ->orderBy('due_date', 'desc')
                                ->limit(50)
                                ->get();
                        @endphp
                        @foreach ($projectTransactions as $transaction)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $transaction->project->key ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $transaction->project->title ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $transaction->financial_type === 'revenue' 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                            : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                        {{ ucfirst($transaction->financial_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    ${{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($transaction->due_date)->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $transaction->status === 'done' 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                            : ($transaction->status === 'pending'
                                                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Transactions Table -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mt-6">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">User Transactions</h3>
            </div>

            <!-- Scrollable Table Container -->
            <div class="overflow-x-auto" style="max-height: 400px; overflow-y: auto;">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- User Transactions Header -->
                    <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Custom ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Transaction Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>

                    <!-- User Transactions Body -->
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $userTransactions = \App\Models\UserTransaction::with('user')
                                ->orderBy('transaction_date', 'desc')
                                ->limit(50)
                                ->get();
                        @endphp
                        @foreach ($userTransactions as $transaction)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $transaction->user->full_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                        {{ $transaction->user->custom_id ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $transaction->transaction_type === 'deposit' 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                            : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                        {{ ucfirst($transaction->transaction_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    ${{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $transaction->status === 'done' 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                            : ($transaction->status === 'pending'
                                                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
