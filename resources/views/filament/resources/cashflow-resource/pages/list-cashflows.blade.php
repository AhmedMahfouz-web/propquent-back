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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Projects & Transactions</h3>
            </div>

            <!-- Scrollable Table Container -->
            <div class="overflow-x-auto" style="max-height: 400px; overflow-y: auto;">
                <div class="space-y-4">
                    @php
                        $projects = \App\Models\Project::with(['transactions' => function($query) {
                            $query->orderBy('due_date', 'desc');
                        }])->orderBy('title')->limit(20)->get();
                    @endphp
                    
                    @foreach ($projects as $project)
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                            <!-- Project Header -->
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $project->key }} - {{ $project->title }}
                                        </h4>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $project->status === 'active'
                                                ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                                : ($project->status === 'pending'
                                                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $project->transactions->count() }} transactions
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Project Transactions -->
                            @if($project->transactions->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Due Date</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Note</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($project->transactions as $transaction)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <td class="px-4 py-2 whitespace-nowrap">
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                            {{ $transaction->financial_type === 'revenue' 
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                                                : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                                            {{ ucfirst($transaction->financial_type) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                        ${{ number_format($transaction->amount, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                        {{ \Carbon\Carbon::parse($transaction->due_date)->format('M j, Y') }}
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap">
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                            {{ $transaction->status === 'done' 
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                                                : ($transaction->status === 'pending'
                                                                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
                                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                                            {{ ucfirst($transaction->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                                        {{ $transaction->note ?? '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No transactions found for this project
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- User Transactions Table -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mt-6">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Users & Transactions</h3>
            </div>

            <!-- Scrollable Table Container -->
            <div class="overflow-x-auto" style="max-height: 400px; overflow-y: auto;">
                <div class="space-y-4">
                    @php
                        $users = \App\Models\User::with(['transactions' => function($query) {
                            $query->orderBy('transaction_date', 'desc');
                        }])->orderBy('full_name')->limit(20)->get();
                    @endphp
                    
                    @foreach ($users as $user)
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                            <!-- User Header -->
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $user->full_name }}
                                        </h4>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            {{ $user->custom_id ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $user->transactions->count() }} transactions
                                    </span>
                                </div>
                            </div>
                            
                            <!-- User Transactions -->
                            @if($user->transactions->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Transaction Date</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Note</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($user->transactions as $transaction)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <td class="px-4 py-2 whitespace-nowrap">
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                            {{ $transaction->transaction_type === 'deposit' 
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                                                : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                                            {{ ucfirst($transaction->transaction_type) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                        ${{ number_format($transaction->amount, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                        {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M j, Y') }}
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap">
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                            {{ $transaction->status === 'done' 
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                                                : ($transaction->status === 'pending'
                                                                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
                                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                                            {{ ucfirst($transaction->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                                        {{ $transaction->note ?? '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No transactions found for this user
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
