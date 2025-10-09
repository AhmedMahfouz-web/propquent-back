<div>
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Project Transactions
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Manage project transactions with inline editing, filtering, and bulk import capabilities.
            </p>
        </div>
        
        <!-- Full page scroll - no height restriction -->
        {{ $this->table }}
        
        <!-- Summary Section -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Table Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Table Summary (Filtered Results)
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Records:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($tableSummary['total_records']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Amount:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">EGP {{ number_format($tableSummary['total_amount'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-green-600 dark:text-green-400">Total Revenue:</span>
                        <span class="font-semibold text-green-600 dark:text-green-400">EGP {{ number_format($tableSummary['total_revenue'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-red-600 dark:text-red-400">Total Expense:</span>
                        <span class="font-semibold text-red-600 dark:text-red-400">EGP {{ number_format($tableSummary['total_expense'], 2) }}</span>
                    </div>
                    <hr class="border-gray-200 dark:border-gray-600">
                    <div class="flex justify-between">
                        <span class="text-gray-900 dark:text-white font-semibold">Net Amount:</span>
                        <span class="font-bold {{ $tableSummary['net_amount'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            EGP {{ number_format($tableSummary['net_amount'], 2) }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Selected Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Selected Summary
                </h3>
                @if($selectedSummary['selected_records'] > 0)
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Selected Records:</span>
                            <span class="font-semibold text-blue-600 dark:text-blue-400">{{ number_format($selectedSummary['selected_records']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Selected Amount:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">EGP {{ number_format($selectedSummary['selected_amount'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-green-600 dark:text-green-400">Selected Revenue:</span>
                            <span class="font-semibold text-green-600 dark:text-green-400">EGP {{ number_format($selectedSummary['selected_revenue'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-red-600 dark:text-red-400">Selected Expense:</span>
                            <span class="font-semibold text-red-600 dark:text-red-400">EGP {{ number_format($selectedSummary['selected_expense'], 2) }}</span>
                        </div>
                        <hr class="border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between">
                            <span class="text-gray-900 dark:text-white font-semibold">Selected Net:</span>
                            <span class="font-bold {{ $selectedSummary['selected_net'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                EGP {{ number_format($selectedSummary['selected_net'], 2) }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 dark:text-gray-500 mb-2">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">
                            Select rows to see summary calculations
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
