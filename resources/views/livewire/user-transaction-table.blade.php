<div>
    <div
        class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
        <!-- Header with Add New Row button -->
        <div class="fi-ta-header-ctn divide-y divide-gray-200 dark:divide-white/10">
            <div class="fi-ta-header-toolbar flex items-center justify-between gap-x-4 px-4 py-3 sm:px-6">
                <div class="flex items-center gap-x-4">
                    <h1
                        class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                        User Transactions
                    </h1>
                </div>
                <div class="flex items-center gap-x-4">
                    <button wire:click="addNewRow"
                        class="fi-btn fi-btn-size-md fi-color-primary fi-btn-color-primary inline-flex items-center justify-center gap-1 font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-outlined ring-1 bg-white text-gray-950 hover:bg-gray-50 focus-visible:ring-primary-600 ring-gray-300 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 dark:ring-white/20 dark:focus-visible:ring-primary-500 px-3 py-2 text-sm">
                        <svg class="fi-btn-icon h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        <span class="fi-btn-label">Add New Row</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="fi-ta-filters-form fi-fo-component-ctn grid gap-y-4 px-4 py-4 sm:px-6">
            <div class="fi-in-affixes flex items-center gap-x-3">
                <div class="fi-in-affix flex items-center gap-x-3 text-sm leading-6 text-gray-950 dark:text-white">
                    <svg class="fi-in-affix-icon h-5 w-5 text-gray-400 dark:text-gray-500" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm">
                        <div class="flex items-center gap-6 mb-2">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-4 h-3 bg-warning-100 dark:bg-warning-900 border-l-4 border-warning-400 rounded-sm">
                                </div>
                                <span>Draft rows (auto-save when complete)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-4 h-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-sm">
                                </div>
                                <span>Saved transactions</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-3 border-2 border-red-500 rounded-sm"></div>
                                <span>Required fields</span>
                            </div>
                        </div>
                        <p><strong>Required fields:</strong> User, Type, Amount, Date, Status</p>
                        <p><strong>Navigation:</strong> Use arrow keys to move between cells • Click any cell to edit •
                            Changes save automatically</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filament Table -->
        <div class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10"
            style="min-height: 400px;">
            <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5"
                id="user-transaction-table" style="min-width: 1400px;">
                <thead class="fi-ta-header divide-y divide-gray-200 dark:divide-white/5">
                    <tr class="fi-ta-header-row">
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6"
                            style="min-width: 200px;">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">User</span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6"
                            style="min-width: 120px;">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Type</span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6"
                            style="min-width: 120px;">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Amount</span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6"
                            style="min-width: 120px;">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Method</span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6"
                            style="min-width: 150px;">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Reference</span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6"
                            style="min-width: 120px;">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Status</span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6"
                            style="min-width: 150px;">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Date</span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6"
                            style="min-width: 150px;">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Actual
                                    Date</span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6"
                            style="min-width: 200px;">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Notes</span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Actions</span>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Draft Rows (not saved to database) -->
                    @foreach ($draftRows as $rowId => $row)
                        @php
                            $validationErrors = $this->getValidationErrors($row);
                        @endphp
                        <tr
                            class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5 {{ count($validationErrors) > 0 ? 'bg-warning-50 dark:bg-warning-400/10' : 'bg-warning-50 dark:bg-warning-400/10' }}">
                            <td
                                class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 {{ in_array('user_id', $validationErrors) ? 'ring-2 ring-danger-600 dark:ring-danger-500' : '' }}">
                                <select
                                    wire:change="updateDraftRow('{{ $rowId }}', 'user_id', $event.target.value)"
                                    data-row="{{ $rowId }}" data-col="0"
                                    class="fi-select-input block w-full border-none bg-transparent py-1.5 pe-8 ps-3 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6">
                                    <option value="">Select User...</option>
                                    @foreach ($users as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $row['user_id'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td
                                class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 {{ in_array('transaction_type', $validationErrors) ? 'ring-2 ring-danger-600 dark:ring-danger-500' : '' }}">
                                <select
                                    wire:change="updateDraftRow('{{ $rowId }}', 'transaction_type', $event.target.value)"
                                    data-row="{{ $rowId }}" data-col="1"
                                    class="fi-select-input block w-full border-none bg-transparent py-1.5 pe-8 ps-3 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6">
                                    <option value="">Type...</option>
                                    @foreach ($transactionTypes as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $row['transaction_type'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td
                                class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 {{ in_array('amount', $validationErrors) ? 'ring-2 ring-danger-600 dark:ring-danger-500' : '' }}">
                                <input type="number" step="0.01"
                                    wire:blur="updateDraftRow('{{ $rowId }}', 'amount', $event.target.value)"
                                    value="{{ $row['amount'] }}" placeholder="0.00" data-row="{{ $rowId }}"
                                    data-col="2"
                                    class="fi-input block w-full border-none bg-transparent py-1.5 ps-3 pe-3 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 text-right">
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 p-0 bg-yellow-50 dark:bg-yellow-900/30">
                                <select
                                    wire:change="updateDraftRow('{{ $rowId }}', 'method', $event.target.value)"
                                    data-row="{{ $rowId }}" data-col="3"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                                    <option value="">Method...</option>
                                    @foreach ($transactionMethods as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $row['method'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 p-0 bg-yellow-50 dark:bg-yellow-900/30">
                                <input type="text"
                                    wire:blur="updateDraftRow('{{ $rowId }}', 'reference_no', $event.target.value)"
                                    value="{{ $row['reference_no'] }}" placeholder="Reference..."
                                    data-row="{{ $rowId }}" data-col="4"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td
                                class="border p-0 bg-yellow-50 dark:bg-yellow-900/30 {{ in_array('status', $validationErrors) ? 'border-red-500 border-2' : 'border-gray-300 dark:border-gray-600' }}">
                                <select
                                    wire:change="updateDraftRow('{{ $rowId }}', 'status', $event.target.value)"
                                    data-row="{{ $rowId }}" data-col="5"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                                    <option value="">Status...</option>
                                    @foreach ($statuses as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $row['status'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td
                                class="border p-0 bg-yellow-50 dark:bg-yellow-900/30 {{ in_array('transaction_date', $validationErrors) ? 'border-red-500 border-2' : 'border-gray-300 dark:border-gray-600' }}">
                                <input type="text" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD"
                                    wire:change="updateDraftRow('{{ $rowId }}', 'transaction_date', $event.target.value)"
                                    value="{{ $row['transaction_date'] }}" data-row="{{ $rowId }}"
                                    data-col="6"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 p-0 bg-yellow-50 dark:bg-yellow-900/30">
                                <input type="text" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD"
                                    wire:change="updateDraftRow('{{ $rowId }}', 'actual_date', $event.target.value)"
                                    value="{{ $row['actual_date'] }}" data-row="{{ $rowId }}" data-col="7"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 p-0 bg-yellow-50 dark:bg-yellow-900/30">
                                <input type="text"
                                    wire:blur="updateDraftRow('{{ $rowId }}', 'note', $event.target.value)"
                                    value="{{ $row['note'] }}" placeholder="Note..."
                                    data-row="{{ $rowId }}" data-col="8"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 p-2 bg-yellow-50 dark:bg-yellow-900/30 text-center">
                                <button wire:click="deleteDraftRow('{{ $rowId }}')"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm px-2 py-1 rounded transition-colors duration-200">
                                    ×
                                </button>
                            </td>
                        </tr>
                    @endforeach

                    <!-- Existing Transactions (saved in database) -->
                    @foreach ($transactions as $transaction)
                        <tr class="hover:bg-blue-50 dark:hover:bg-blue-900/30">
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <select
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'user_id', $event.target.value)"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="0"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                                    @foreach ($users as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $transaction['user_id'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <select
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'transaction_type', $event.target.value)"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="1"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                                    @foreach ($transactionTypes as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $transaction['transaction_type'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <input type="number" step="0.01"
                                    wire:blur="updateExistingRow({{ $transaction['id'] }}, 'amount', $event.target.value)"
                                    value="{{ $transaction['amount'] }}"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="2"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 text-right focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <select
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'method', $event.target.value)"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="3"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                                    <option value="">Method...</option>
                                    @foreach ($transactionMethods as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $transaction['method'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <input type="text"
                                    wire:blur="updateExistingRow({{ $transaction['id'] }}, 'reference_no', $event.target.value)"
                                    value="{{ $transaction['reference_no'] }}"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="4"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <select
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'status', $event.target.value)"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="5"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                                    @foreach ($statuses as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $transaction['status'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <input type="text" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD"
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'transaction_date', $event.target.value)"
                                    value="{{ $transaction['transaction_date'] }}"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="6"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <input type="text" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD"
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'actual_date', $event.target.value)"
                                    value="{{ $transaction['actual_date'] }}"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="7"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <input type="text"
                                    wire:blur="updateExistingRow({{ $transaction['id'] }}, 'note', $event.target.value)"
                                    value="{{ $transaction['note'] }}" data-row="existing-{{ $transaction['id'] }}"
                                    data-col="8"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 p-2 bg-white dark:bg-gray-900 text-center">
                                <button wire:click="deleteTransaction({{ $transaction['id'] }})"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm px-2 py-1 rounded transition-colors duration-200">
                                    ×
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .fi-ta-cell-focused {
            background-color: rgb(59 130 246 / 0.1) !important;
            ring: 2px solid rgb(59 130 246) !important;
            ring-offset: 1px !important;
        }

        .dark .fi-ta-cell-focused {
            background-color: rgb(59 130 246 / 0.2) !important;
            ring: 2px solid rgb(147 197 253) !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentCell = null;
            const totalCols = 8; // 0-8 columns (excluding actions column)

            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (!currentCell) return;

                const row = currentCell.getAttribute('data-row');
                const col = parseInt(currentCell.getAttribute('data-col'));

                if (!row || col === null) return;

                let newRow = row;
                let newCol = col;

                switch (e.key) {
                    case 'ArrowUp':
                        e.preventDefault();
                        newRow = getPreviousRow(row);
                        break;
                    case 'ArrowDown':
                        e.preventDefault();
                        newRow = getNextRow(row);
                        break;
                    case 'ArrowLeft':
                        e.preventDefault();
                        newCol = col > 0 ? col - 1 : totalCols;
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        newCol = col < totalCols ? col + 1 : 0;
                        break;
                    case 'Tab':
                        e.preventDefault();
                        if (e.shiftKey) {
                            // Shift+Tab - go backwards
                            if (col > 0) {
                                newCol = col - 1;
                            } else {
                                newRow = getPreviousRow(row);
                                newCol = totalCols;
                            }
                        } else {
                            // Tab - go forwards
                            if (col < totalCols) {
                                newCol = col + 1;
                            } else {
                                newRow = getNextRow(row);
                                newCol = 0;
                            }
                        }
                        break;
                    case 'Enter':
                        e.preventDefault();
                        newRow = getNextRow(row);
                        break;
                    default:
                        return;
                }

                focusCell(newRow, newCol);
            });

            // Track focus on inputs and selects
            document.addEventListener('focusin', function(e) {
                if (e.target.matches('input, select') && e.target.hasAttribute('data-row')) {
                    // Remove previous focus indicator
                    document.querySelectorAll('.fi-ta-cell-focused').forEach(cell => {
                        cell.classList.remove('fi-ta-cell-focused');
                    });

                    // Add focus indicator to current cell's parent td
                    const parentTd = e.target.closest('td');
                    if (parentTd) {
                        parentTd.classList.add('fi-ta-cell-focused');
                    }

                    currentCell = e.target;
                }
            });

            // Remove focus indicator when clicking outside
            document.addEventListener('focusout', function(e) {
                setTimeout(() => {
                    if (!document.activeElement || !document.activeElement.hasAttribute(
                            'data-row')) {
                        document.querySelectorAll('.fi-ta-cell-focused').forEach(cell => {
                            cell.classList.remove('fi-ta-cell-focused');
                        });
                        currentCell = null;
                    }
                }, 10);
            });

            function getPreviousRow(currentRow) {
                const table = document.getElementById('user-transaction-table');
                const rows = Array.from(table.querySelectorAll('tbody tr'));
                const currentIndex = rows.findIndex(row => {
                    const firstInput = row.querySelector('[data-row]');
                    return firstInput && firstInput.getAttribute('data-row') === currentRow;
                });

                if (currentIndex > 0) {
                    const prevRow = rows[currentIndex - 1];
                    const firstInput = prevRow.querySelector('[data-row]');
                    return firstInput ? firstInput.getAttribute('data-row') : currentRow;
                }
                return currentRow;
            }

            function getNextRow(currentRow) {
                const table = document.getElementById('user-transaction-table');
                const rows = Array.from(table.querySelectorAll('tbody tr'));
                const currentIndex = rows.findIndex(row => {
                    const firstInput = row.querySelector('[data-row]');
                    return firstInput && firstInput.getAttribute('data-row') === currentRow;
                });

                if (currentIndex < rows.length - 1) {
                    const nextRow = rows[currentIndex + 1];
                    const firstInput = nextRow.querySelector('[data-row]');
                    return firstInput ? firstInput.getAttribute('data-row') : currentRow;
                }
                return currentRow;
            }

            function focusCell(row, col) {
                const selector = `[data-row="${row}"][data-col="${col}"]`;
                const cell = document.querySelector(selector);
                if (cell) {
                    cell.focus();
                    currentCell = cell;
                }
            }

            // Auto-focus first cell when page loads
            setTimeout(() => {
                const firstCell = document.querySelector('[data-row][data-col="0"]');
                if (firstCell) {
                    firstCell.focus();
                    currentCell = firstCell;
                }
            }, 100);
        });
    </script>

</div>
