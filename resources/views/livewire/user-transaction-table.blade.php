<div>
    <style>
        /* Fixed height table rows - exactly 30px */
        table tbody tr {
            height: 30px !important;
            min-height: 30px !important;
            max-height: 30px !important;
            padding: 0 !important;
        }

        table tbody td {
            height: 30px !important;
            padding: 2px 8px !important;
            vertical-align: middle !important;
            font-size: 14px !important;
            line-height: 1.2 !important;
        }

        table thead th {
            padding: 8px !important;
            font-size: 14px !important;
        }

        /* Input fields styling */
        table tbody td input {
            padding: 2px 6px !important;
            font-size: 14px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 4px !important;
            line-height: 1.2 !important;
        }

        table tbody td select {
            padding: 2px 6px !important;
            font-size: 14px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 4px !important;
            line-height: 1.2 !important;
        }

        /* Amount column - right aligned */
        table tbody td input[type="number"] {
            text-align: right !important;
            font-weight: 500 !important;
        }

        /* Normal button styling */
        table tbody td button {
            width: 26px !important;
            padding: 4px !important;
            border-radius: 4px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 12px !important;
            line-height: 1 !important;
        }

        /* Summary row styling */
        .summary-row {
            background-color: #f9fafb !important;
            font-weight: 600 !important;
            border-top: 2px solid #e5e7eb !important;
        }

        .dark .summary-row {
            background-color: #374151 !important;
            border-top-color: #4b5563 !important;
        }

        .fi-ta-cell {
            padding: 2px 8px !important;
        }

        .fi-ta-actions {
            padding: 0 !important;
        }

        .py-4 {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Listen for checkbox changes to update selected summary
            document.addEventListener('change', function(e) {
                if (e.target.type === 'checkbox' && e.target.closest('.fi-ta-table')) {
                    // Small delay to let Filament update its state
                    setTimeout(() => {
                        // Get all selected checkboxes
                        const selectedCheckboxes = document.querySelectorAll('.fi-ta-table input[type="checkbox"]:checked');
                        const selectedIds = [];
                        
                        selectedCheckboxes.forEach(checkbox => {
                            const value = checkbox.value;
                            if (value && value !== 'on' && !isNaN(value)) {
                                selectedIds.push(parseInt(value));
                            }
                        });
                        
                        // Update Livewire component with selected IDs
                        if (window.Livewire) {
                            const component = window.Livewire.find('{{ $this->getId() }}');
                            if (component) {
                                component.set('customSelectedRecords', selectedIds);
                                component.call('$refresh');
                            }
                        }
                    }, 100);
                }
            });
        });
    </script>

    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                User Transactions
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Manage user transactions with advanced filtering, sorting, and summary capabilities.
            </p>
        </div>

        <!-- Full page scroll - no height restriction -->
        {{ $this->table }}

        <!-- Summary Section - Side by side with flex -->
        <div class="summary-cards mt-6 flex gap-4">
            <!-- Table Summary -->
            <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                    Current Page Summary
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
                        <span class="text-green-600 dark:text-green-400">Total Salary:</span>
                        <span class="font-semibold text-green-600 dark:text-green-400 font-mono">{{ number_format($tableSummary['total_salary'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-blue-600 dark:text-blue-400">Total Bonus:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400 font-mono">{{ number_format($tableSummary['total_bonus'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-red-600 dark:text-red-400">Total Deduction:</span>
                        <span class="font-semibold text-red-600 dark:text-red-400 font-mono">({{ number_format($tableSummary['total_deduction'], 2) }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-yellow-600 dark:text-yellow-400">Total Advance:</span>
                        <span class="font-semibold text-yellow-600 dark:text-yellow-400 font-mono">({{ number_format($tableSummary['total_advance'], 2) }})</span>
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
            <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                    Selected Summary
                </h3>
                @if ($selectedSummary['selected_records'] > 0)
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
                            <span class="text-green-600 dark:text-green-400">Selected Salary:</span>
                            <span class="font-semibold text-green-600 dark:text-green-400 font-mono">{{ number_format($selectedSummary['selected_salary'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-600 dark:text-blue-400">Selected Bonus:</span>
                            <span class="font-semibold text-blue-600 dark:text-blue-400 font-mono">{{ number_format($selectedSummary['selected_bonus'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-red-600 dark:text-red-400">Selected Deduction:</span>
                            <span class="font-semibold text-red-600 dark:text-red-400 font-mono">({{ number_format($selectedSummary['selected_deduction'], 2) }})</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-yellow-600 dark:text-yellow-400">Selected Advance:</span>
                            <span class="font-semibold text-yellow-600 dark:text-yellow-400 font-mono">({{ number_format($selectedSummary['selected_advance'], 2) }})</span>
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
                    <div class="text-center py-2">
                        <div class="text-gray-400 dark:text-gray-500 mb-2">
                            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                </path>
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
