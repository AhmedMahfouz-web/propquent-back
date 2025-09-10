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
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Per Page</label>
                    <select wire:model.live="perPage"
                        class="w-full text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
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
                                    <div class="flex items-center space-x-2">
                                        <span>Project</span>
                                    </div>
                                </th>
                                
                                <!-- Project Details Columns -->
                                <th class="table-header">Unit</th>
                                <th class="table-header">Area</th>
                                <th class="table-header">Garden</th>
                                <th class="table-header">Compound</th>
                                
                                <!-- Contract Details Columns -->
                                <th class="table-header">Reserved</th>
                                <th class="table-header">Contract Date</th>
                                <th class="table-header">Total Value</th>
                                <th class="table-header">Years</th>
                                
                                <!-- Expenses Columns -->
                                <th class="table-header">Asset Expenses</th>
                                <th class="table-header">Operation Expenses</th>
                                <th class="table-header">Total Expenses</th>
                                <th class="table-header">Net Profit</th>
                                
                                <!-- Status Columns -->
                                <th class="table-header">Status</th>
                                <th class="table-header">Stage</th>
                                <th class="table-header">Start Date</th>
                                <th class="table-header">End Date</th>
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
                                            @if ($project->developer)
                                                <div class="project-developer">{{ $project->developer->name }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Project Details Columns -->
                                    <td class="table-cell">{{ $project->unit_no ?? 'N/A' }}</td>
                                    <td class="table-cell">
                                        @if($project->area)
                                            {{ number_format($project->area, 0) }} m²
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        @if($project->garden_area)
                                            {{ number_format($project->garden_area, 0) }} m²
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="table-cell">{{ $project->compound->name ?? $project->project ?? 'N/A' }}</td>
                                    
                                    <!-- Contract Details Columns -->
                                    <td class="table-cell">
                                        @if ($project->reservation_date)
                                            {{ $project->reservation_date->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        @if ($project->contract_date)
                                            {{ $project->contract_date->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="table-cell font-medium">
                                        @if ($project->total_contract_value)
                                            ${{ number_format($project->total_contract_value, 0) }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        @if ($project->years_of_installment)
                                            {{ $project->years_of_installment }} years
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Expenses Columns -->
                                    <td class="table-cell text-red-600 dark:text-red-400 font-medium">
                                        ${{ number_format($assetExpenses, 0) }}
                                    </td>
                                    <td class="table-cell text-red-600 dark:text-red-400 font-medium">
                                        ${{ number_format($operationExpenses, 0) }}
                                    </td>
                                    <td class="table-cell text-red-600 dark:text-red-400 font-bold">
                                        ${{ number_format($totalExpenses, 0) }}
                                    </td>
                                    <td class="table-cell font-bold {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        ${{ number_format($netProfit, 0) }}
                                    </td>
                                    
                                    <!-- Status Columns -->
                                    <td class="table-cell">
                                        <span class="status-badge
                                            @if ($project->status === 'active') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                            @elseif($project->status === 'exited') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                            @elseif($project->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100 @endif">
                                            {{ $project->status === 'exited' ? 'Sold' : ($project->status === 'active' ? 'On Hold' : ucfirst($project->status)) }}
                                        </span>
                                    </td>
                                    <td class="table-cell">{{ ucfirst($project->stage ?? 'N/A') }}</td>
                                    <td class="table-cell">
                                        @if ($projectData['entry_date'])
                                            {{ \Carbon\Carbon::parse($projectData['entry_date'])->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        @if ($projectData['exit_date'])
                                            {{ \Carbon\Carbon::parse($projectData['exit_date'])->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
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

        /* Table Headers */
        .table-header {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            background: #f9fafb;
            border-left: 1px solid #e5e7eb;
            min-width: 120px;
        }

        .dark .table-header {
            color: #d1d5db;
            background: #374151;
            border-left-color: #4b5563;
        }

        /* Table Cells */
        .table-cell {
            padding: 16px;
            vertical-align: top;
            border-left: 1px solid #e5e7eb;
            font-size: 0.75rem;
            color: #111827;
            min-width: 120px;
        }

        .dark .table-cell {
            border-left-color: #4b5563;
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

            .table-header,
            .table-cell {
                min-width: 100px;
                padding: 12px 8px;
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
    </style>

</div>
