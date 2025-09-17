<div>
    <div
        class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
        <!-- Header with Add New Row button -->
        <div class="fi-ta-header-ctn divide-y divide-gray-200 dark:divide-white/10">
            <div class="fi-ta-header-toolbar flex items-center justify-between gap-x-4 px-4 py-3 sm:px-6">
                <div class="flex shrink-0 items-center gap-x-4">
                    <h1
                        class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                        Project Transactions
                    </h1>
                </div>
                <div class="flex items-center gap-x-4">
                    <button wire:click="resetFilters"
                        class="fi-btn fi-btn-size-md fi-color-gray fi-btn-color-gray inline-flex items-center justify-center gap-1 font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-outlined ring-1 bg-white text-gray-950 hover:bg-gray-50 focus-visible:ring-gray-600 ring-gray-300 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 dark:ring-white/20 dark:focus-visible:ring-gray-500 px-3 py-2 text-sm">
                        <svg class="fi-btn-icon h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span class="fi-btn-label">Reset Filters</span>
                    </button>
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

        <!-- Instructions -->
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
                                <div class="w-4 h-3 border-2 border-danger-500 rounded-sm"></div>
                                <span>Required fields</span>
                            </div>
                        </div>
                        <p><strong>Required fields:</strong> Project, Financial Type, Amount, Date, Status</p>
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
                id="transaction-table" style="min-width: 1400px;">
                <thead class="fi-ta-header divide-y divide-gray-200 dark:divide-white/5">
                    <tr class="fi-ta-header-row">
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 {{ $isLoading ? 'opacity-50' : '' }}"
                            style="min-width: 200px;" wire:click="sortBy('project')">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Project</span>
                                @if($sortField === 'project')
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 {{ $isLoading ? 'opacity-50' : '' }}"
                            style="min-width: 150px;" wire:click="sortBy('financial_type')">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Financial
                                    Type</span>
                                @if($sortField === 'financial_type')
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 {{ $isLoading ? 'opacity-50' : '' }}"
                            style="min-width: 120px;" wire:click="sortBy('serving')">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Serving</span>
                                @if($sortField === 'serving')
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 {{ $isLoading ? 'opacity-50' : '' }}"
                            style="min-width: 120px;" wire:click="sortBy('amount')">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Amount</span>
                                @if($sortField === 'amount')
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 {{ $isLoading ? 'opacity-50' : '' }}"
                            style="min-width: 120px;" wire:click="sortBy('method')">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Method</span>
                                @if($sortField === 'method')
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 {{ $isLoading ? 'opacity-50' : '' }}"
                            style="min-width: 150px;" wire:click="sortBy('reference_no')">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Reference</span>
                                @if($sortField === 'reference_no')
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 {{ $isLoading ? 'opacity-50' : '' }}"
                            style="min-width: 120px;" wire:click="sortBy('status')">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Status</span>
                                @if($sortField === 'status')
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 {{ $isLoading ? 'opacity-50' : '' }}"
                            style="min-width: 150px;" wire:click="sortBy('transaction_date')">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Date</span>
                                @if($sortField === 'transaction_date')
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 {{ $isLoading ? 'opacity-50' : '' }}"
                            style="min-width: 150px;" wire:click="sortBy('due_date')">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Due
                                    Date</span>
                                @if($sortField === 'due_date')
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 {{ $isLoading ? 'opacity-50' : '' }}"
                            style="min-width: 150px;" wire:click="sortBy('actual_date')">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Actual
                                    Date</span>
                                @if($sortField === 'actual_date')
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 {{ $isLoading ? 'opacity-50' : '' }}"
                            style="min-width: 200px;" wire:click="sortBy('note')">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Notes</span>
                                @if($sortField === 'note')
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span
                                    class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">Actions</span>
                            </span>
                        </th>
                    </tr>
                    <!-- Filter Row -->
                    <tr class="fi-ta-header-row bg-gray-50 dark:bg-gray-800">
                        <!-- Project Filter -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6" style="min-width: 200px;">
                            <input type="text" wire:model.live.debounce.300ms="filters.project" placeholder="Search project..."
                                class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                        </th>
                        <!-- Financial Type Filter -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6" style="min-width: 150px;">
                            <select wire:model.live="filters.financial_type"
                                class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                                <option value="">All Types</option>
                                @foreach ($financialTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </th>
                        <!-- Serving Filter -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6" style="min-width: 120px;">
                            <select wire:model.live="filters.serving"
                                class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                                <option value="">All Serving</option>
                                @foreach ($servingTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </th>
                        <!-- Amount Filter -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6" style="min-width: 120px;">
                            <div class="flex flex-col gap-1">
                                <input type="number" wire:model.live.debounce.300ms="filters.amount_min" placeholder="Min"
                                    class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                                <input type="number" wire:model.live.debounce.300ms="filters.amount_max" placeholder="Max"
                                    class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                            </div>
                        </th>
                        <!-- Method Filter -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6" style="min-width: 120px;">
                            <select wire:model.live="filters.method"
                                class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                                <option value="">All Methods</option>
                                @foreach ($transactionMethods as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </th>
                        <!-- Reference Filter -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6" style="min-width: 150px;">
                            <input type="text" wire:model.live.debounce.300ms="filters.reference_no" placeholder="Search reference..."
                                class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                        </th>
                        <!-- Status Filter -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6" style="min-width: 120px;">
                            <select wire:model.live="filters.status"
                                class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                                <option value="">All Status</option>
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </th>
                        <!-- Transaction Date Filter -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6" style="min-width: 150px;">
                            <div class="flex flex-col gap-1">
                                <input type="date" wire:model.live="filters.transaction_date_from"
                                    class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                                <input type="date" wire:model.live="filters.transaction_date_to"
                                    class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                            </div>
                        </th>
                        <!-- Due Date Filter -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6" style="min-width: 150px;">
                            <div class="flex flex-col gap-1">
                                <input type="date" wire:model.live="filters.due_date_from"
                                    class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                                <input type="date" wire:model.live="filters.due_date_to"
                                    class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                            </div>
                        </th>
                        <!-- Actual Date Filter -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6" style="min-width: 150px;">
                            <div class="flex flex-col gap-1">
                                <input type="date" wire:model.live="filters.actual_date_from"
                                    class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                                <input type="date" wire:model.live="filters.actual_date_to"
                                    class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                            </div>
                        </th>
                        <!-- Notes Filter -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6" style="min-width: 200px;">
                            <input type="text" wire:model.live.debounce.300ms="filters.note" placeholder="Search notes..."
                                class="w-full text-xs border-gray-300 dark:border-gray-500 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
                        </th>
                        <!-- Actions Column (no filter) -->
                        <th class="fi-ta-header-cell px-3 py-2 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <!-- Empty for actions column -->
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
                                class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 {{ in_array('project_key', $validationErrors) ? 'ring-2 ring-danger-600 dark:ring-danger-500' : '' }}">
                                <select
                                    wire:change="updateDraftRow('{{ $rowId }}', 'project_key', $event.target.value)"
                                    data-row="{{ $rowId }}" data-col="0"
                                    class="fi-select-input block w-full border-none bg-transparent py-1.5 pe-8 ps-3 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 dark:[&>option]:bg-gray-800 dark:[&>option]:text-white">
                                    <option value="">Select Project...</option>
                                    @foreach ($projects as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $row['project_key'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td
                                class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                <select
                                    wire:change="updateDraftRow('{{ $rowId }}', 'financial_type', $event.target.value)"
                                    data-row="{{ $rowId }}" data-col="1"
                                    class="fi-select-input block w-full border-none bg-transparent py-1.5 pe-8 ps-3 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 dark:[&>option]:bg-gray-800 dark:[&>option]:text-white">
                                    <option value="">Select Financial Type...</option>
                                    @foreach ($financialTypes as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $row['financial_type'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td
                                class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                <select
                                    wire:change="updateDraftRow('{{ $rowId }}', 'serving', $event.target.value)"
                                    data-row="{{ $rowId }}" data-col="2"
                                    class="fi-select-input block w-full border-none bg-transparent py-1.5 pe-8 ps-3 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 dark:[&>option]:bg-gray-800 dark:[&>option]:text-white">
                                    <option value="">Serving...</option>
                                    @foreach ($servingTypes as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $row['serving'] == $key ? 'selected' : '' }}>
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
                                    data-col="3"
                                    class="fi-input block w-full border-none bg-transparent py-1.5 ps-3 pe-3 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 text-right">
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 p-0 bg-yellow-50 dark:bg-yellow-900/30">
                                <select
                                    wire:change="updateDraftRow('{{ $rowId }}', 'method', $event.target.value)"
                                    data-row="{{ $rowId }}" data-col="4"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200 dark:[&>option]:bg-gray-800 dark:[&>option]:text-white">
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
                                    data-row="{{ $rowId }}" data-col="5"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td
                                class="border p-0 bg-yellow-50 dark:bg-yellow-900/30 {{ in_array('status', $validationErrors) ? 'border-red-500 border-2' : 'border-gray-300 dark:border-gray-600' }}">
                                <select
                                    wire:change="updateDraftRow('{{ $rowId }}', 'status', $event.target.value)"
                                    data-row="{{ $rowId }}" data-col="6"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200 dark:[&>option]:bg-gray-800 dark:[&>option]:text-white">
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
                                    data-col="7"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 p-0 bg-yellow-50 dark:bg-yellow-900/30">
                                <input type="text" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD"
                                    wire:change="updateDraftRow('{{ $rowId }}', 'due_date', $event.target.value)"
                                    value="{{ $row['due_date'] }}" data-row="{{ $rowId }}" data-col="8"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 p-0 bg-yellow-50 dark:bg-yellow-900/30">
                                <input type="text" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD"
                                    wire:change="updateDraftRow('{{ $rowId }}', 'actual_date', $event.target.value)"
                                    value="{{ $row['actual_date'] }}" data-row="{{ $rowId }}" data-col="9"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 p-0 bg-yellow-50 dark:bg-yellow-900/30">
                                <input type="text"
                                    wire:blur="updateDraftRow('{{ $rowId }}', 'note', $event.target.value)"
                                    value="{{ $row['note'] }}" placeholder="Note..."
                                    data-row="{{ $rowId }}" data-col="10"
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
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'project_key', $event.target.value)"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="0"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200 dark:[&>option]:bg-gray-800 dark:[&>option]:text-white">
                                    @foreach ($projects as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $transaction['project_key'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <select
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'financial_type', $event.target.value)"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="1"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200 dark:[&>option]:bg-gray-800 dark:[&>option]:text-white">
                                    @foreach ($financialTypes as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $transaction['financial_type'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <select
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'serving', $event.target.value)"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="2"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200 dark:[&>option]:bg-gray-800 dark:[&>option]:text-white">
                                    <option value="">Serving...</option>
                                    @foreach ($servingTypes as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $transaction['serving'] == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <input type="number" step="0.01"
                                    wire:blur="updateExistingRow({{ $transaction['id'] }}, 'amount', $event.target.value)"
                                    value="{{ $transaction['amount'] }}"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="3"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 text-right focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <select
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'method', $event.target.value)"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="4"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200 dark:[&>option]:bg-gray-800 dark:[&>option]:text-white">
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
                                    data-row="existing-{{ $transaction['id'] }}" data-col="5"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <select
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'status', $event.target.value)"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="6"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200 dark:[&>option]:bg-gray-800 dark:[&>option]:text-white">
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
                                    data-row="existing-{{ $transaction['id'] }}" data-col="7"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <input type="text" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD"
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'due_date', $event.target.value)"
                                    value="{{ $transaction['due_date'] }}"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="8"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <input type="text" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD"
                                    wire:change="updateExistingRow({{ $transaction['id'] }}, 'actual_date', $event.target.value)"
                                    value="{{ $transaction['actual_date'] }}"
                                    data-row="existing-{{ $transaction['id'] }}" data-col="9"
                                    class="w-full h-full border-0 bg-transparent dark:text-white text-sm p-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200">
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 p-0 bg-white dark:bg-gray-900">
                                <input type="text"
                                    wire:blur="updateExistingRow({{ $transaction['id'] }}, 'note', $event.target.value)"
                                    value="{{ $transaction['note'] }}" data-row="existing-{{ $transaction['id'] }}"
                                    data-col="10"
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
            const totalCols = 11; // 0-10 columns (excluding actions column)

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
                const table = document.getElementById('transaction-table');
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
                const table = document.getElementById('transaction-table');
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
