<div class="space-y-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Transaction History</h3>
        
        @if(empty($transactions))
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <p>No transactions found for this project.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Method
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Reference
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Note
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($transactions as $transaction)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($transaction['transaction_date'])->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transaction['financial_type'] === 'revenue' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                        {{ ucfirst($transaction['financial_type']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $transaction['financial_type'] === 'revenue' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    ${{ number_format($transaction['amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $statusColors = [
                                            'done' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
                                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
                                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$transaction['status']] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100' }}">
                                        {{ ucfirst($transaction['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $transaction['method'] ? ucfirst(str_replace('_', ' ', $transaction['method'])) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $transaction['reference_no'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                    {{ $transaction['note'] ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                    <div class="text-sm font-medium text-green-800 dark:text-green-200">Completed Revenue</div>
                    <div class="text-xl font-bold text-green-600 dark:text-green-400">
                        ${{ number_format(collect($transactions)->where('financial_type', 'revenue')->where('status', 'done')->sum('amount'), 2) }}
                    </div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                    <div class="text-sm font-medium text-red-800 dark:text-red-200">Completed Expenses</div>
                    <div class="text-xl font-bold text-red-600 dark:text-red-400">
                        ${{ number_format(collect($transactions)->where('financial_type', 'expense')->where('status', 'done')->sum('amount'), 2) }}
                    </div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                    <div class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Pending Revenue</div>
                    <div class="text-xl font-bold text-yellow-600 dark:text-yellow-400">
                        ${{ number_format(collect($transactions)->where('financial_type', 'revenue')->where('status', 'pending')->sum('amount'), 2) }}
                    </div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <div class="text-sm font-medium text-blue-800 dark:text-blue-200">Net Completed</div>
                    <div class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        ${{ number_format(
                            collect($transactions)->where('status', 'done')->where('financial_type', 'revenue')->sum('amount') - 
                            collect($transactions)->where('status', 'done')->where('financial_type', 'expense')->sum('amount'), 2
                        ) }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
