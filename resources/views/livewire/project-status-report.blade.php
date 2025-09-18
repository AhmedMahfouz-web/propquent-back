<div>
    <div class="space-y-6">
        <!-- Filters Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Project title or key..."
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select wire:model.live="status"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Statuses</option>
                        @foreach (\App\Models\Project::getAvailableStatuses() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Stage</label>
                    <select wire:model.live="stage"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Stages</option>
                        @foreach (\App\Models\Project::getAvailableStages() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select wire:model.live="type"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Types</option>
                        @foreach (\App\Models\Project::getAvailablePropertyTypes() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Investment
                        Type</label>
                    <select wire:model.live="investment_type"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Investment Types</option>
                        @foreach (\App\Models\Project::getAvailableInvestmentTypes() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Compound</label>
                    <select wire:model.live="compound"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Compounds</option>
                        @foreach ($this->availableCompounds as $compoundName)
                            <option value="{{ $compoundName }}">{{ $compoundName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Advanced Filters Row -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                <!-- Area Range -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Min Area (m²)</label>
                    <input type="number" wire:model.live.debounce.500ms="minArea" placeholder="0"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Max Area (m²)</label>
                    <input type="number" wire:model.live.debounce.500ms="maxArea" placeholder="∞"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Contract Value Range -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Min Contract Value</label>
                    <input type="number" wire:model.live.debounce.500ms="minContractValue" placeholder="0"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Max Contract Value</label>
                    <input type="number" wire:model.live.debounce.500ms="maxContractValue" placeholder="∞"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Financial Range -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Min Expenses</label>
                    <input type="number" wire:model.live.debounce.500ms="minExpenses" placeholder="0"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Max Expenses</label>
                    <input type="number" wire:model.live.debounce.500ms="maxExpenses" placeholder="∞"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <!-- Date Filters Row -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                <!-- Contract Date Range -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Contract Date From</label>
                    <input type="date" wire:model.live="contractDateFrom"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Contract Date To</label>
                    <input type="date" wire:model.live="contractDateTo"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Reservation Date Range -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Reservation Date From</label>
                    <input type="date" wire:model.live="reservationDateFrom"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Reservation Date To</label>
                    <input type="date" wire:model.live="reservationDateTo"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <!-- Control Row -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-600">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Per Page</label>
                    <select wire:model.live="perPage"
                        class="text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <button wire:click="clearFilters" 
                        class="px-3 py-1 text-xs bg-gray-500 hover:bg-gray-600 text-white rounded-md transition-colors">
                        Clear Filters
                    </button>
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
                                        <div class="flex flex-col space-y-1">
                                            <button wire:click="sortBy('title')" class="sortable-header text-left">
                                                Title
                                                @if($sortBy === 'title')
                                                    <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                            <button wire:click="sortBy('key')" class="sortable-header text-left">
                                                Key
                                                @if($sortBy === 'key')
                                                    <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </button>
                                        </div>
                                    </div>
                                </th>

                                <!-- Project Details Section Header -->
                                <th class="section-header details-header" data-section="details"
                                    onclick="toggleSection('details')">
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
                                <th class="section-header contract-header" data-section="contract"
                                    onclick="toggleSection('contract')">
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
                                <th class="section-header expenses-header" data-section="expenses"
                                    onclick="toggleSection('expenses')">
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
                                <th class="section-header status-header" data-section="status"
                                    onclick="toggleSection('status')">
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

                            <!-- Sub-headers row -->
                            <tr class="sub-headers-row">
                                <th></th> <!-- Empty for project column -->

                                <!-- Project Details Sub-headers -->
                                <th class="section-subheader details-section">
                                    <div class="sub-header-grid details-grid">
                                        <button wire:click="sortBy('unit_no')" class="sortable-header">
                                            Unit
                                            @if($sortBy === 'unit_no')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                        <button wire:click="sortBy('area')" class="sortable-header">
                                            Area
                                            @if($sortBy === 'area')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                        <button wire:click="sortBy('garden_area')" class="sortable-header">
                                            Garden
                                            @if($sortBy === 'garden_area')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                        <button wire:click="sortBy('compound')" class="sortable-header">
                                            Compound
                                            @if($sortBy === 'compound')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                    </div>
                                </th>

                                <!-- Contract Details Sub-headers -->
                                <th class="section-subheader contract-section">
                                    <div class="sub-header-grid contract-grid">
                                        <button wire:click="sortBy('reservation_date')" class="sortable-header">
                                            Reserved
                                            @if($sortBy === 'reservation_date')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                        <button wire:click="sortBy('contract_date')" class="sortable-header">
                                            Contract Date
                                            @if($sortBy === 'contract_date')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                        <button wire:click="sortBy('total_contract_value')" class="sortable-header">
                                            Total Value
                                            @if($sortBy === 'total_contract_value')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                        <button wire:click="sortBy('years_of_installment')" class="sortable-header">
                                            Years
                                            @if($sortBy === 'years_of_installment')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                    </div>
                                </th>

                                <!-- Expenses Sub-headers -->
                                <th class="section-subheader expenses-section">
                                    <div class="sub-header-grid expenses-grid">
                                        <span>Asset</span>
                                        <span>Operation</span>
                                        <button wire:click="sortBy('total_expenses')" class="sortable-header">
                                            Total
                                            @if($sortBy === 'total_expenses')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                        <button wire:click="sortBy('net_profit')" class="sortable-header">
                                            Net Profit
                                            @if($sortBy === 'net_profit')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                    </div>
                                </th>

                                <!-- Status Sub-headers -->
                                <th class="section-subheader status-section">
                                    <div class="sub-header-grid status-grid">
                                        <button wire:click="sortBy('status')" class="sortable-header">
                                            Status
                                            @if($sortBy === 'status')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                        <button wire:click="sortBy('stage')" class="sortable-header">
                                            Stage
                                            @if($sortBy === 'stage')
                                                <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </button>
                                        <span>Start Date</span>
                                        <span>End Date</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
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
                                    <td class="section-content details-section expanded" data-section="details">
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
                                    <td class="section-content contract-section expanded" data-section="contract">
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
                                    <td class="section-content expenses-section expanded" data-section="expenses">
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
                                    <td class="section-content status-section expanded" data-section="status">
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
                                                        @if ($projectData['entry_date'])
                                                            {{ \Carbon\Carbon::parse($projectData['entry_date'])->format('M d, Y') }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-value">
                                                        @if ($projectData['exit_date'])
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
            max-width: 100%;
        }

        .project-status-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            line-height: 1rem;
            table-layout: auto;
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
            padding: 12px 16px;
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
            padding: 16px;
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
        }

        .section-header.collapsed {
            width: 50px;
            min-width: 50px;
            max-width: 50px;
            padding: 8px 4px;
        }

        .section-header.expanded {
            width: auto;
            min-width: 280px;
            padding: 8px 12px;
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

        .section-header.expanded .section-title-full {
            display: inline;
        }

        .section-header.expanded .section-title-short {
            display: none;
        }

        /* Sub-headers */
        .section-subheader {
            padding: 8px 12px;
            background: #f3f4f6;
            border-left: 1px solid #e5e7eb;
            border-top: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .section-subheader.collapsed {
            width: 50px;
            min-width: 50px;
            max-width: 50px;
            padding: 8px 4px;
        }

        .section-subheader.expanded {
            width: auto;
            min-width: 280px;
            padding: 8px 12px;
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

        .section-subheader.collapsed .sub-header-grid {
            display: none;
        }

        .dark .sub-header-grid {
            color: #9ca3af;
        }

        .details-grid,
        .contract-grid,
        .expenses-grid,
        .status-grid {
            grid-template-columns: 1fr 1fr 1fr 1fr;
        }

        .section-subheader.expanded .sub-header-grid {
            display: grid;
        }

        /* Section Content */
        .section-content {
            border-left: 1px solid #e5e7eb;
            vertical-align: top;
            transition: all 0.3s ease;
            position: relative;
        }

        .section-content.collapsed {
            width: 50px;
            min-width: 50px;
            max-width: 50px;
            padding: 8px 4px;
            text-align: center;
        }

        .section-content.collapsed .section-expanded-content {
            display: none;
        }

        .section-content.expanded {
            width: auto;
            min-width: 280px;
            padding: 16px 12px;
        }

        .section-content.expanded .expanded-content-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 12px;
            align-items: start;
            width: 100%;
        }

        .section-content.expanded .content-row {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border-bottom: none;
            padding: 8px 4px;
        }

        .section-content.expanded .content-label {
            font-size: 0.6875rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin-bottom: 4px;
            min-width: auto;
            white-space: nowrap;
        }

        .section-content.expanded .content-value {
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

        .section-content.expanded .section-expanded-content {
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

        /* Collapsed state indicators */
        .section-content.collapsed::after {
            content: '•••';
            display: block;
            text-align: center;
            color: #9ca3af;
            font-weight: bold;
            margin-top: 8px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {

            .project-column-header,
            .project-column {
                min-width: 150px;
                max-width: 150px;
            }

            .section-header.expanded,
            .section-content.expanded,
            .section-subheader.expanded {
                min-width: 200px;
            }

            .section-content.expanded .expanded-content-wrapper {
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
    </style>

    <script>
        function toggleSection(sectionName) {
            // Toggle section header state
            const sectionHeader = document.querySelector(`[data-section="${sectionName}"]`);
            const arrow = sectionHeader.querySelector('.toggle-arrow');

            // Toggle section content elements and sub-headers
            const sectionElements = document.querySelectorAll(`.${sectionName}-section`);

            const isExpanded = sectionHeader.classList.contains('expanded');

            if (isExpanded) {
                // Collapse section
                sectionHeader.classList.remove('expanded');
                sectionHeader.classList.add('collapsed');

                sectionElements.forEach(element => {
                    element.classList.remove('expanded');
                    element.classList.add('collapsed');
                });

                // Rotate arrow back
                if (arrow) {
                    arrow.classList.remove('expanded');
                }
            } else {
                // Expand section
                sectionHeader.classList.remove('collapsed');
                sectionHeader.classList.add('expanded');

                sectionElements.forEach(element => {
                    element.classList.remove('collapsed');
                    element.classList.add('expanded');
                });

                // Rotate arrow
                if (arrow) {
                    arrow.classList.add('expanded');
                }
            }
        }

        // Initialize all sections as expanded by default
        document.addEventListener('DOMContentLoaded', function() {
            const sections = ['details', 'contract', 'expenses', 'status'];

            sections.forEach(section => {
                // Set headers as expanded
                const header = document.querySelector(`[data-section="${section}"]`);
                if (header) {
                    header.classList.add('expanded');
                    // Rotate arrow to expanded state
                    const arrow = header.querySelector('.toggle-arrow');
                    if (arrow) {
                        arrow.classList.add('expanded');
                    }
                }

                // Set all section elements (content and sub-headers) as expanded
                const elements = document.querySelectorAll(`.${section}-section`);
                elements.forEach(element => {
                    element.classList.add('expanded');
                });
            });
        });

        // Handle Livewire updates to maintain section states
        document.addEventListener('livewire:navigated', function() {
            // Re-initialize expanded states after Livewire updates
            const sections = ['details', 'contract', 'expenses', 'status'];

            sections.forEach(section => {
                const header = document.querySelector(`[data-section="${section}"]`);
                if (header && !header.classList.contains('collapsed')) {
                    header.classList.add('expanded');
                    // Rotate arrow to expanded state
                    const arrow = header.querySelector('.toggle-arrow');
                    if (arrow) {
                        arrow.classList.add('expanded');
                    }
                }

                const elements = document.querySelectorAll(`.${section}-section`);
                elements.forEach(element => {
                    if (!element.classList.contains('collapsed')) {
                        element.classList.add('expanded');
                    }
                });
            });
        });
    </script>
</div>
