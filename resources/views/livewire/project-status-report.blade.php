<div>
    <div class="space-y-6">
        <!-- Simple Search and Controls -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div>
                        <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search projects..."
                            class="text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <select wire:model.live="perPage"
                            class="text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button wire:click="clearAllFilters"
                        class="px-4 py-2 text-sm bg-gray-500 hover:bg-gray-600 text-white rounded-md transition-colors">
                        Clear All Filters
                    </button>
                    <!-- Hidden button for closing filters via JavaScript -->
                    <button id="close-filters-btn" wire:click="closeColumnFilter" style="display: none;"></button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        @if (!$readyToLoad)
            <div class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Loading projects...</span>
            </div>
        @else
            <!-- Project Status Table with Foldable Sections -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="project-status-table-container">
                    <table class="project-status-table">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <!-- Fixed Project Column -->
                                <th class="project-column-header">
                                    <div class="flex items-center justify-between">
                                        <div class="flex flex-col space-y-1 flex-1">
                                            <div class="excel-column-header">
                                                <button wire:click="sortByColumn('title')"
                                                    class="sortable-header text-left">
                                                    Title
                                                    @if ($sortBy === 'title')
                                                        <span
                                                            class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                    @endif
                                                </button>
                                                <button wire:click="toggleColumnFilter('title')"
                                                    class="filter-btn {{ !empty($columnFilters['title']) ? 'active' : '' }}">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                    </svg>
                                                </button>
                                                @if ($openFilterColumn === 'title')
                                                    <div class="filter-dropdown">
                                                        <div class="filter-content">
                                                            <input type="text"
                                                                wire:model.live.debounce.300ms="columnFilters.title"
                                                                placeholder="Search titles..."
                                                                class="w-full px-2 py-1 text-sm border border-gray-300 rounded">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="excel-column-header">
                                                <button wire:click="sortByColumn('key')"
                                                    class="sortable-header text-left">
                                                    Key
                                                    @if ($sortBy === 'key')
                                                        <span
                                                            class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                    @endif
                                                </button>
                                                <button wire:click="toggleColumnFilter('key')"
                                                    class="filter-btn {{ !empty($columnFilters['key']) ? 'active' : '' }}">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                    </svg>
                                                </button>
                                                @if ($openFilterColumn === 'key')
                                                    <div class="filter-dropdown">
                                                        <div class="filter-content">
                                                            <input type="text"
                                                                wire:model.live.debounce.300ms="columnFilters.key"
                                                                placeholder="Search keys..."
                                                                class="w-full px-2 py-1 text-sm border border-gray-300 rounded">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </th>

                                <!-- Project Details Section Header -->
                                <th class="section-header details-header" data-state="{{ $sectionStates['details'] }}"
                                    wire:click.prevent="toggleSectionState('details')">
                                    <div class="header-content">
                                        <span class="section-title-full">Project Details</span>
                                        <span class="section-title-short">P.D</span>
                                        <svg class="toggle-arrow" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </th>

                                <!-- Contract Details Section Header -->
                                <th class="section-header contract-header" data-state="{{ $sectionStates['contract'] }}"
                                    wire:click.prevent="toggleSectionState('contract')">
                                    <div class="header-content">
                                        <span class="section-title-full">Contract Details</span>
                                        <span class="section-title-short">C.D</span>
                                        <svg class="toggle-arrow" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </th>

                                <!-- Expenses Section Header -->
                                <th class="section-header expenses-header" data-state="{{ $sectionStates['expenses'] }}"
                                    wire:click.prevent="toggleSectionState('expenses')">
                                    <div class="header-content">
                                        <span class="section-title-full">Expenses</span>
                                        <span class="section-title-short">Exp</span>
                                        <svg class="toggle-arrow" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </th>

                                <!-- Status Section Header -->
                                <th class="section-header status-header" data-state="{{ $sectionStates['status'] }}"
                                    wire:click.prevent="toggleSectionState('status')">
                                    <div class="header-content">
                                        <span class="section-title-full">Status & Dates</span>
                                        <span class="section-title-short">S&D</span>
                                        <svg class="toggle-arrow" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </th>
                            </tr>

                            <!-- Excel-style Sub-headers with filters -->
                            <tr class="sub-headers-row">
                                <th></th> <!-- Empty for project column -->

                                <!-- Project Details Sub-headers -->
                                <th class="section-subheader details-section"
                                    data-state="{{ $sectionStates['details'] }}" wire:key="subheader-details">
                                    <div class="sub-header-grid details-grid">
                                        <!-- Unit Column -->
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('unit_no')" class="column-sort-btn">
                                                Unit
                                                @if ($sortBy === 'unit_no')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('unit_no')"
                                                class="filter-btn {{ !empty($columnFilters['unit_no']) ? 'active' : '' }}">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'unit_no')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content">
                                                        @foreach ($this->getUniqueValues['unit_no'] as $value)
                                                            <label class="filter-option">
                                                                <input type="checkbox"
                                                                    wire:model.live="columnFilters.unit_no"
                                                                    value="{{ $value }}"
                                                                    class="filter-checkbox">
                                                                <span>{{ $value ?: 'N/A' }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Area Column -->
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('area')" class="column-sort-btn">
                                                Area
                                                @if ($sortBy === 'area')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('area_range')" class="filter-btn">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'area_range')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content range-filter">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.area_range.min"
                                                            placeholder="Min" class="range-input">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.area_range.max"
                                                            placeholder="Max" class="range-input">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Garden Column -->
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('garden_area')" class="column-sort-btn">
                                                Garden
                                                @if ($sortBy === 'garden_area')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('garden_area_range')"
                                                class="filter-btn">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'garden_area_range')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content range-filter">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.garden_area_range.min"
                                                            placeholder="Min" class="range-input">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.garden_area_range.max"
                                                            placeholder="Max" class="range-input">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Compound Column -->
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('compound')" class="column-sort-btn">
                                                Compound
                                                @if ($sortBy === 'compound')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('compound')" class="filter-btn">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'compound')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content">
                                                        @foreach ($this->getUniqueValues['compound'] as $value)
                                                            <label class="filter-option">
                                                                <input type="checkbox"
                                                                    wire:model.live="columnFilters.compound"
                                                                    value="{{ $value }}"
                                                                    class="filter-checkbox">
                                                                <span>{{ $value ?: 'N/A' }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </th>

                                <!-- Contract Details Sub-headers -->
                                <th class="section-subheader contract-section"
                                    data-state="{{ $sectionStates['contract'] }}" wire:key="subheader-contract">
                                    <div class="sub-header-grid contract-grid">
                                        <!-- Reserved Date Column -->
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('reservation_date')"
                                                class="column-sort-btn">
                                                Reserved
                                                @if ($sortBy === 'reservation_date')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('reservation_date_range')"
                                                class="filter-btn">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'reservation_date_range')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content date-filter">
                                                        <input type="date"
                                                            wire:model.live="columnFilters.reservation_date_range.from"
                                                            class="date-input">
                                                        <input type="date"
                                                            wire:model.live="columnFilters.reservation_date_range.to"
                                                            class="date-input">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Contract Date Column -->
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('contract_date')"
                                                class="column-sort-btn">
                                                Contract Date
                                                @if ($sortBy === 'contract_date')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('contract_date_range')"
                                                class="filter-btn">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'contract_date_range')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content date-filter">
                                                        <input type="date"
                                                            wire:model.live="columnFilters.contract_date_range.from"
                                                            class="date-input">
                                                        <input type="date"
                                                            wire:model.live="columnFilters.contract_date_range.to"
                                                            class="date-input">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Total Value Column -->
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('total_contract_value')"
                                                class="column-sort-btn">
                                                Total Value
                                                @if ($sortBy === 'total_contract_value')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('contract_value_range')"
                                                class="filter-btn">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'contract_value_range')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content range-filter">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.contract_value_range.min"
                                                            placeholder="Min" class="range-input">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.contract_value_range.max"
                                                            placeholder="Max" class="range-input">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Years Column -->
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('years_of_installment')"
                                                class="column-sort-btn">
                                                Years
                                                @if ($sortBy === 'years_of_installment')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('years_range')" class="filter-btn">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'years_range')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content range-filter">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.years_range.min"
                                                            placeholder="Min" class="range-input">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.years_range.max"
                                                            placeholder="Max" class="range-input">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </th>

                                <!-- Expenses Sub-headers -->
                                <th class="section-subheader expenses-section"
                                    data-state="{{ $sectionStates['expenses'] }}" wire:key="subheader-expenses">
                                    <div class="sub-header-grid expenses-grid">
                                        <span>Asset</span>
                                        <span>Operation</span>
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('total_expenses')"
                                                class="column-sort-btn">
                                                Total
                                                @if ($sortBy === 'total_expenses')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('expenses_range')"
                                                class="filter-btn {{ !empty($columnFilters['expenses_range']['min']) || !empty($columnFilters['expenses_range']['max']) ? 'active' : '' }}">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'expenses_range')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content range-filter">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.expenses_range.min"
                                                            placeholder="Min" class="range-input">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.expenses_range.max"
                                                            placeholder="Max" class="range-input">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('net_profit')" class="column-sort-btn">
                                                Net Profit
                                                @if ($sortBy === 'net_profit')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('net_profit_range')"
                                                class="filter-btn">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'net_profit_range')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content range-filter">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.net_profit_range.min"
                                                            placeholder="Min" class="range-input">
                                                        <input type="number"
                                                            wire:model.live="columnFilters.net_profit_range.max"
                                                            placeholder="Max" class="range-input">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </th>

                                <!-- Status Sub-headers -->
                                <th class="section-subheader status-section"
                                    data-state="{{ $sectionStates['status'] }}" wire:key="subheader-status">
                                    <div class="sub-header-grid status-grid">
                                        <!-- Status Column -->
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('status')" class="column-sort-btn">
                                                Status
                                                @if ($sortBy === 'status')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('status')" class="filter-btn">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'status')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content">
                                                        @foreach ($this->getUniqueValues['status'] as $value)
                                                            <label class="filter-option">
                                                                <input type="checkbox"
                                                                    wire:model.live="columnFilters.status"
                                                                    value="{{ $value }}"
                                                                    class="filter-checkbox">
                                                                <span>{{ ucfirst($value) }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Stage Column -->
                                        <div class="excel-column-header">
                                            <button wire:click="sortByColumn('stage')" class="column-sort-btn">
                                                Stage
                                                @if ($sortBy === 'stage')
                                                    <span
                                                        class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="toggleColumnFilter('stage')" class="filter-btn">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                                </svg>
                                            </button>
                                            @if ($openFilterColumn === 'stage')
                                                <div class="filter-dropdown">
                                                    <div class="filter-content">
                                                        @foreach ($this->getUniqueValues['stage'] as $value)
                                                            <label class="filter-option">
                                                                <input type="checkbox"
                                                                    wire:model.live="columnFilters.stage"
                                                                    value="{{ $value }}"
                                                                    class="filter-checkbox">
                                                                <span>{{ ucfirst($value) }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <span>Start Date</span>
                                        <span>End Date</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>

                        <tbody wire:ignore.self
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($this->projects as $project)
                                @php
                                    $projectData = $this->projectsData[$project->key] ?? [];
                                    $assetExpenses = 0;
                                    $operationExpenses = 0;

                                    // Calculate categorized expenses
                                    foreach ($projectData['expense_breakdown'] ?? [] as $type => $amount) {
                                        if (str_contains($type, 'asset')) {
                                            $assetExpenses += $amount;
                                        } else {
                                            $operationExpenses += $amount;
                                        }
                                    }

                                    $totalExpenses = $projectData['total_expenses'] ?? 0;
                                    $totalRevenues = $projectData['total_revenues'] ?? 0;
                                    $netProfit = $totalRevenues - $totalExpenses;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <!-- Fixed Project Column -->
                                    <td class="project-column">
                                        <div class="project-info">
                                            <div class="project-title">{{ $project->title }}</div>
                                            <div class="project-key">{{ $project->key }}</div>
                                        </div>
                                    </td>

                                    <!-- Project Details Section -->
                                    <td class="section-content details-section"
                                        data-state="{{ $sectionStates['details'] }}"
                                        wire:key="content-details-{{ $project->id }}">
                                        <div class="section-expanded-content">
                                            <div class="expanded-content-wrapper">
                                                <div class="content-row">
                                                    <span
                                                        class="content-value">{{ $project->unit_no ?? 'N/A' }}</span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-value">
                                                        @if ($project->area)
                                                            {{ number_format($project->area, 0) }} m²
                                                        @else
                                                            N/A
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-value">
                                                        @if ($project->garden_area)
                                                            {{ number_format($project->garden_area, 0) }} m²
                                                        @else
                                                            N/A
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span
                                                        class="content-value">{{ $project->compound->name ?? ($project->project ?? 'N/A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Contract Details Section -->
                                    <td class="section-content contract-section"
                                        data-state="{{ $sectionStates['contract'] }}"
                                        wire:key="content-contract-{{ $project->id }}">
                                        <div class="section-expanded-content">
                                            <div class="expanded-content-wrapper">
                                                <div class="content-row">
                                                    <span class="content-value">
                                                        @if ($project->reservation_date)
                                                            {{ $project->reservation_date->format('M d, Y') }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-value">
                                                        @if ($project->contract_date)
                                                            {{ $project->contract_date->format('M d, Y') }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-value font-medium">
                                                        @if ($project->total_contract_value)
                                                            ${{ number_format($project->total_contract_value, 0) }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-value">
                                                        @if ($project->years_of_installment)
                                                            {{ $project->years_of_installment }} years
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Expenses Section -->
                                    <td class="section-content expenses-section"
                                        data-state="{{ $sectionStates['expenses'] }}"
                                        wire:key="content-expenses-{{ $project->id }}">
                                        <div class="section-expanded-content">
                                            <div class="expanded-content-wrapper">
                                                <div class="content-row">
                                                    <span
                                                        class="content-value text-red-600 dark:text-red-400 font-medium">
                                                        ${{ number_format($assetExpenses, 0) }}
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span
                                                        class="content-value text-red-600 dark:text-red-400 font-medium">
                                                        ${{ number_format($operationExpenses, 0) }}
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span
                                                        class="content-value text-red-600 dark:text-red-400 font-bold">
                                                        ${{ number_format($totalExpenses, 0) }}
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span
                                                        class="content-value font-bold {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                        ${{ number_format($netProfit, 0) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status Section -->
                                    <td class="section-content status-section"
                                        data-state="{{ $sectionStates['status'] }}"
                                        wire:key="content-status-{{ $project->id }}">
                                        <div class="section-expanded-content">
                                            <div class="expanded-content-wrapper">
                                                <div class="content-row">
                                                    <span
                                                        class="status-badge
                                                        @if ($project->status === 'active') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                        @elseif($project->status === 'exited') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                        @elseif($project->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                                        @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100 @endif">
                                                        {{ $project->status === 'exited' ? 'Sold' : ($project->status === 'active' ? 'On Hold' : ucfirst($project->status)) }}
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span
                                                        class="content-value">{{ ucfirst($project->stage ?? 'N/A') }}</span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-value">
                                                        @if (isset($projectData['entry_date']) && $projectData['entry_date'])
                                                            {{ \Carbon\Carbon::parse($projectData['entry_date'])->format('M d, Y') }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-value">
                                                        @if (isset($projectData['exit_date']) && $projectData['exit_date'])
                                                            {{ \Carbon\Carbon::parse($projectData['exit_date'])->format('M d, Y') }}
                                                        @else
                                                            <span class="text-gray-400">-</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No projects found matching your criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $this->projects->links() }}
            </div>
        @endif
    </div>

    <style>
        /* Project Status Table Styles */
        .project-status-table-container {
            overflow-x: auto;
            overflow-y: visible;
            max-width: 100%;
            width: 100%;
            -webkit-overflow-scrolling: touch;
            /* Smooth scrolling on mobile */
        }

        .project-status-table {
            width: auto;
            /* Let table size itself based on content */
            min-width: 1200px;
            /* Minimum width to trigger horizontal scroll */
            border-collapse: collapse;
            font-size: 0.75rem;
            line-height: 1rem;
            table-layout: auto;
            /* Auto layout for content-based sizing */
        }

        /* Fixed Project Column */
        .project-column-header,
        .project-column {
            position: sticky;
            left: 0;
            z-index: 10;
            background: white;
            border-right: 2px solid #e5e7eb;
            min-width: 200px;
            max-width: 200px;
        }

        .dark .project-column-header,
        .dark .project-column {
            background: #1f2937;
            border-right-color: #374151;
        }

        .project-column-header {
            padding: 12px 16px 0;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            background: #f9fafb;
        }

        .dark .project-column-header {
            color: #d1d5db;
            background: #374151;
        }

        .project-column {
            padding: 16px 16px 0;
            vertical-align: top;
        }

        .project-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .project-title {
            font-weight: 600;
            color: #111827;
            font-size: 0.875rem;
        }

        .dark .project-title {
            color: #f9fafb;
        }

        .project-key {
            font-size: 0.75rem;
            color: #6b7280;
            font-family: monospace;
        }

        .dark .project-key {
            color: #9ca3af;
        }

        .project-developer {
            font-size: 0.75rem;
            color: #2563eb;
        }

        .dark .project-developer {
            color: #60a5fa;
        }

        /* Section Headers */
        .section-header {
            padding: 8px 12px;
            background: #f9fafb;
            border-left: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            cursor: pointer;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .section-header[data-state="collapsed"] {
            width: 60px !important;
            min-width: 60px !important;
            max-width: 60px !important;
            padding: 8px 4px !important;
        }

        .section-header[data-state="expanded"] {
            width: auto !important;
            min-width: 200px !important;
            padding: 8px 12px 0 !important;
        }

        .dark .section-header {
            background: #374151;
            border-left-color: #4b5563;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .section-header:hover {
            background: #e5e7eb;
        }

        .dark .section-header:hover {
            background: #4b5563;
        }

        .toggle-arrow {
            width: 16px;
            height: 16px;
            transition: transform 0.2s ease;
        }

        .toggle-arrow.expanded {
            transform: rotate(180deg);
        }

        /* Section Title States */
        .section-title-full {
            display: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
        }

        .section-title-short {
            display: inline;
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .section-header[data-state="expanded"] .section-title-full {
            display: inline;
        }

        .section-header[data-state="expanded"] .section-title-short {
            display: none;
        }

        .section-header[data-state="collapsed"] .section-title-full {
            display: none;
        }

        .section-header[data-state="collapsed"] .section-title-short {
            display: none;
        }

        .section-header[data-state="collapsed"]::before {
            content: '•••';
            display: block;
            text-align: center;
            color: #9ca3af;
            font-weight: bold;
            font-size: 1rem;
        }

        /* Sub-headers */
        .section-subheader {
            padding: 8px 12px;
            background: #f3f4f6;
            border-left: 1px solid #e5e7eb;
            border-top: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .section-subheader[data-state="collapsed"] {
            width: 60px !important;
            min-width: 60px !important;
            max-width: 60px !important;
            padding: 8px 4px !important;
            overflow: hidden;
        }

        .section-subheader[data-state="expanded"] {
            width: auto !important;
            min-width: 200px !important;
            padding: 8px 12px !important;
        }

        .dark .section-subheader {
            background: #2d3748;
            border-left-color: #4b5563;
            border-top-color: #4b5563;
        }

        .sub-header-grid {
            display: grid;
            gap: 8px;
            font-size: 0.6875rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            width: 100%;
        }

        .section-subheader[data-state="collapsed"] .sub-header-grid {
            display: none;
        }

        .section-subheader[data-state="collapsed"]::before {
            content: '•••';
            display: block;
            text-align: center;
            color: #9ca3af;
            font-weight: bold;
            font-size: 1rem;
        }

        .dark .sub-header-grid {
            color: #9ca3af;
        }

        .details-grid,
        .contract-grid,
        .expenses-grid,
        .status-grid {
            grid-template-columns: 1fr 1fr 1fr 1fr;
            min-width: 400px;
        }

        .section-subheader[data-state="expanded"] .sub-header-grid {
            display: grid;
        }

        /* Section Content */
        .section-content {
            border-left: 1px solid #e5e7eb;
            vertical-align: top;
            transition: all 0.3s ease;
            position: relative;
        }

        .section-content[data-state="collapsed"] {
            width: 60px !important;
            min-width: 60px !important;
            max-width: 60px !important;
            padding: 8px 4px !important;
            text-align: center;
            overflow: hidden;
        }

        .section-content[data-state="collapsed"] .section-expanded-content {
            display: none;
        }

        .section-content[data-state="collapsed"]::before {
            content: '•••';
            display: block;
            text-align: center;
            color: #9ca3af;
            font-weight: bold;
            font-size: 1rem;
            margin-top: 8px;
        }

        .section-content[data-state="expanded"] {
            width: auto !important;
            min-width: 200px !important;
            padding: 16px 12px 0 !important;
        }

        .section-content[data-state="expanded"] .expanded-content-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 8px;
            align-items: start;
            width: 100%;
        }

        .section-content[data-state="expanded"] .content-row {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 8px 4px;
        }

        .section-content[data-state="expanded"] .content-label {
            font-size: 0.6875rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin-bottom: 4px;
            min-width: auto;
            white-space: nowrap;
        }

        .section-content[data-state="expanded"] .content-value {
            font-size: 0.75rem;
            color: #111827;
            text-align: center;
            word-wrap: break-word;
            max-width: 100%;
        }

        .dark .section-content {
            border-left-color: #4b5563;
        }

        /* Expanded Content */
        .section-expanded-content {
            display: none;
        }

        .section-content[data-state="expanded"] .section-expanded-content {
            display: block;
        }

        .expanded-content-wrapper {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .content-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .dark .content-row {
            border-bottom-color: #4b5563;
        }

        .content-row:last-child {
            border-bottom: none;
        }

        .content-label {
            font-size: 0.6875rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            min-width: 60px;
        }

        .dark .content-label {
            color: #9ca3af;
        }

        .content-value {
            font-size: 0.75rem;
            color: #111827;
            text-align: right;
        }

        .dark .content-value {
            color: #f9fafb;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 500;
        }


        /* Responsive adjustments */
        @media (max-width: 768px) {

            .project-column-header,
            .project-column {
                min-width: 150px;
                max-width: 150px;
            }

            .section-header[data-state="expanded"],
            .section-content[data-state="expanded"],
            .section-subheader[data-state="expanded"] {
                min-width: 200px;
            }

            .section-content[data-state="expanded"] .expanded-content-wrapper {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .sub-header-grid {
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }
        }

        /* Custom scrollbar for the table */
        .project-status-table-container::-webkit-scrollbar {
            height: 8px;
        }

        .project-status-table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .project-status-table-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .project-status-table-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Dark mode scrollbar */
        .dark .project-status-table-container::-webkit-scrollbar-track {
            background: #374151;
        }

        .dark .project-status-table-container::-webkit-scrollbar-thumb {
            background: #6b7280;
        }

        .dark .project-status-table-container::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Sortable Header Styles */
        .sortable-header {
            background: none;
            border: none;
            color: inherit;
            font-size: inherit;
            font-weight: inherit;
            text-transform: inherit;
            letter-spacing: inherit;
            cursor: pointer;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 4px;
            border-radius: 4px;
            text-align: inherit;
        }

        .sortable-header:hover {
            background-color: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .dark .sortable-header:hover {
            background-color: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        .sort-indicator {
            font-size: 0.75rem;
            font-weight: bold;
            color: #2563eb;
            margin-left: 2px;
        }

        .dark .sort-indicator {
            color: #60a5fa;
        }

        /* Active sort styling */
        .sortable-header:has(.sort-indicator) {
            color: #2563eb;
            font-weight: 600;
        }

        .dark .sortable-header:has(.sort-indicator) {
            color: #60a5fa;
        }

        /* Excel-style Column Header Styles */
        .excel-column-header {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4px;
        }

        .column-sort-btn {
            background: none;
            border: none;
            color: inherit;
            font-size: inherit;
            font-weight: inherit;
            text-transform: inherit;
            letter-spacing: inherit;
            cursor: pointer;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 4px;
            border-radius: 4px;
            flex: 1;
            justify-content: center
        }

        .column-sort-btn:hover {
            background-color: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .dark .column-sort-btn:hover {
            background-color: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        .filter-btn {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 2px;
            border-radius: 3px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .filter-btn:hover {
            background-color: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .dark .filter-btn {
            color: #9ca3af;
        }

        .dark .filter-btn:hover {
            background-color: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        /* Filter Dropdown Styles */
        .filter-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            min-width: 200px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            margin-top: 4px;
        }

        .dark .filter-dropdown {
            background: #374151;
            border-color: #4b5563;
        }

        .filter-content {
            padding: 8px;
            max-height: 300px;
            overflow-y: auto;
        }

        .filter-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 8px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 0.875rem;
            transition: background-color 0.2s ease;
        }

        .filter-option:hover {
            background-color: #f3f4f6;
        }

        .dark .filter-option:hover {
            background-color: #4b5563;
        }

        .filter-checkbox {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            border: 1px solid #d1d5db;
            cursor: pointer;
        }

        .dark .filter-checkbox {
            border-color: #4b5563;
            background-color: #374151;
        }

        /* Range Filter Styles */
        .range-filter {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .range-input {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .dark .range-input {
            background-color: #374151;
            border-color: #4b5563;
            color: white;
        }

        /* Date Filter Styles */
        .date-filter {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .date-input {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .dark .date-input {
            background-color: #374151;
            border-color: #4b5563;
            color: white;
        }

        /* Active filter indicator */
        .filter-btn.active {
            color: #2563eb;
            background-color: rgba(59, 130, 246, 0.1);
        }

        .dark .filter-btn.active {
            color: #60a5fa;
            background-color: rgba(59, 130, 246, 0.2);
        }

        /* Section Collapse/Expand Styles */
        .section-header {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .section-header:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }

        .dark .section-header:hover {
            background-color: rgba(59, 130, 246, 0.2);
        }

        /* Toggle Arrow Animation */
        .toggle-arrow {
            width: 20px;
            height: 20px;
            transition: transform 0.3s ease;
            transform: rotate(0deg);
            /* Default: collapsed */
        }

        [data-state="expanded"] .toggle-arrow {
            transform: rotate(180deg);
            /* Expanded state */
        }


        .section-header {
            position: relative;
        }

        .section-header[data-state="expanded"] {
            background-color: rgba(59, 130, 246, 0.05);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Close filter dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                // Check if click is outside any filter dropdown
                if (!event.target.closest('.excel-column-header')) {
                    // Trigger a click on a hidden button to close filters
                    const closeButton = document.getElementById('close-filters-btn');
                    if (closeButton) {
                        closeButton.click();
                    }
                }
            });
        });

        // Livewire hook to ensure functionality after DOM updates
        document.addEventListener('livewire:navigated', function() {
            console.log('Livewire navigated - reinitializing');
        });
    </script>
</div>
