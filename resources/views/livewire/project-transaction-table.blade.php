<div>
    <style>
        /* Ultra compact table rows */
        .fi-ta-row {
            padding: 0.125rem 0.25rem !important;
            height: auto !important;
            min-height: 1.5rem !important;
        }

        .fi-ta-cell {
            padding: 0.125rem 0.25rem !important;
            font-size: 0.8125rem !important;
            line-height: 1.125rem !important;
            vertical-align: middle !important;
        }

        .fi-ta-header-cell {
            padding: 0.25rem !important;
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
        }

        /* Ultra compact input fields */
        .fi-ta-cell input,
        .fi-ta-cell select {
            padding: 0.125rem 0.25rem !important;
            font-size: 0.8125rem !important;
            min-height: 1.25rem !important;
            height: 1.25rem !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.25rem !important;
            line-height: 1 !important;
        }

        .fi-ta-cell input:focus,
        .fi-ta-cell select:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 1px #3b82f6 !important;
            outline: none !important;
        }

        /* Amount column - clean sans-serif font */
        .fi-ta-col-amount input {
            font-weight: 600 !important;
            text-align: right !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            background-color: #f8fafc !important;
            border: 1px solid #e5e7eb !important;
        }

        .fi-ta-col-amount input:focus {
            background-color: #ffffff !important;
            border-color: #3b82f6 !important;
        }

        /* Clean sans-serif font for amounts in summary */
        .font-mono {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            font-weight: 600 !important;
            letter-spacing: 0 !important;
        }

        /* Ultra compact action buttons */
        .fi-ta-actions {
            padding: 0.125rem !important;
        }

        .fi-ta-actions .fi-ac-btn-action {
            padding: 0.125rem !important;
            min-height: 1.25rem !important;
            width: 1.25rem !important;
        }

        /* Status select styling */
        .fi-ta-col-status select {
            font-size: 0.75rem !important;
            padding: 0.125rem 0.25rem !important;
            height: 1.25rem !important;
        }

        /* Checkbox styling */
        .fi-ta-checkbox {
            transform: scale(0.8) !important;
        }

        /* Remove extra spacing */
        .fi-ta-content {
            padding: 0 !important;
        }
    </style>
    
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
        
        <!-- Summary Section - Smaller and with margin -->
        <div class="summary-cards mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Table Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                    Table Summary (Filtered Results)
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Records:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($tableSummary['total_records']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Amount:</span>
                        <span class="font-semibold text-gray-900 dark:text-white font-mono">{{ number_format($tableSummary['total_amount'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-green-600 dark:text-green-400">Total Revenue:</span>
                        <span class="font-semibold text-green-600 dark:text-green-400 font-mono">{{ number_format($tableSummary['total_revenue'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-red-600 dark:text-red-400">Total Expense:</span>
                        <span class="font-semibold text-red-600 dark:text-red-400 font-mono">({{ number_format($tableSummary['total_expense'], 2) }})</span>
                    </div>
                    <hr class="border-gray-200 dark:border-gray-600 my-2">
                    <div class="flex justify-between">
                        <span class="text-gray-900 dark:text-white font-semibold">Net Amount:</span>
                        <span class="font-bold font-mono {{ $tableSummary['net_amount'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $tableSummary['net_amount'] >= 0 ? number_format($tableSummary['net_amount'], 2) : '(' . number_format(abs($tableSummary['net_amount']), 2) . ')' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Selected Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                    Selected Summary
                </h3>
                @if($selectedSummary['selected_records'] > 0)
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Selected Records:</span>
                            <span class="font-semibold text-blue-600 dark:text-blue-400">{{ number_format($selectedSummary['selected_records']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Selected Amount:</span>
                            <span class="font-semibold text-gray-900 dark:text-white font-mono">{{ number_format($selectedSummary['selected_amount'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-green-600 dark:text-green-400">Selected Revenue:</span>
                            <span class="font-semibold text-green-600 dark:text-green-400 font-mono">{{ number_format($selectedSummary['selected_revenue'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-red-600 dark:text-red-400">Selected Expense:</span>
                            <span class="font-semibold text-red-600 dark:text-red-400 font-mono">({{ number_format($selectedSummary['selected_expense'], 2) }})</span>
                        </div>
                        <hr class="border-gray-200 dark:border-gray-600 my-2">
                        <div class="flex justify-between">
                            <span class="text-gray-900 dark:text-white font-semibold">Selected Net:</span>
                            <span class="font-bold font-mono {{ $selectedSummary['selected_net'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $selectedSummary['selected_net'] >= 0 ? number_format($selectedSummary['selected_net'], 2) : '(' . number_format(abs($selectedSummary['selected_net']), 2) . ')' }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="text-gray-400 dark:text-gray-500 mb-2">
                            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            Select rows to see summary calculations
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
