<div class="space-y-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Upcoming Installment Schedule</h3>
        
        @if(empty($installments))
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <p>No pending installments found for this project.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Due Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Days Until Due
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Note
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($installments as $installment)
                            @php
                                $dueDate = \Carbon\Carbon::parse($installment['transaction_date']);
                                $daysUntilDue = now()->diffInDays($dueDate, false);
                                $isOverdue = $daysUntilDue < 0;
                                $isUpcoming = $daysUntilDue <= 7 && $daysUntilDue >= 0;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $isOverdue ? 'bg-red-50 dark:bg-red-900/20' : ($isUpcoming ? 'bg-yellow-50 dark:bg-yellow-900/20' : '') }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $dueDate->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $installment['financial_type'] === 'revenue' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                        {{ ucfirst($installment['financial_type']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $installment['financial_type'] === 'revenue' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    ${{ number_format($installment['amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($isOverdue)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                            {{ abs($daysUntilDue) }} days overdue
                                        </span>
                                    @elseif($isUpcoming)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                            {{ $daysUntilDue }} days left
                                        </span>
                                    @else
                                        <span class="text-gray-600 dark:text-gray-400">
                                            {{ $daysUntilDue }} days
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                    {{ $installment['note'] ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                    <div class="text-sm font-medium text-red-800 dark:text-red-200">Overdue</div>
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                        ${{ number_format(collect($installments)->filter(function($item) {
                            return \Carbon\Carbon::parse($item['transaction_date'])->isPast();
                        })->sum('amount'), 2) }}
                    </div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                    <div class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Due This Week</div>
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                        ${{ number_format(collect($installments)->filter(function($item) {
                            $due = \Carbon\Carbon::parse($item['transaction_date']);
                            return $due->isFuture() && $due->diffInDays(now()) <= 7;
                        })->sum('amount'), 2) }}
                    </div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <div class="text-sm font-medium text-blue-800 dark:text-blue-200">Total Pending</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        ${{ number_format(collect($installments)->sum('amount'), 2) }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
