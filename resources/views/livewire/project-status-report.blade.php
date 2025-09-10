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
                                
                                <!-- Foldable Section Headers -->
                                <th class="section-header collapsed" data-section="details">
                                    <button class="section-toggle flex items-center justify-center w-full text-left" 
                                            onclick="toggleSection('details')">
                                        <svg class="section-arrow w-4 h-4 transform transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="section-title-full">Project Details</span>
                                        <span class="section-title-short">P.D</span>
                                    </button>
                                </th>
                                
                                <th class="section-header collapsed" data-section="contract">
                                    <button class="section-toggle flex items-center justify-center w-full text-left" 
                                            onclick="toggleSection('contract')">
                                        <svg class="section-arrow w-4 h-4 transform transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="section-title-full">Contract Details</span>
                                        <span class="section-title-short">C.D</span>
                                    </button>
                                </th>
                                
                                <th class="section-header collapsed" data-section="expenses">
                                    <button class="section-toggle flex items-center justify-center w-full text-left" 
                                            onclick="toggleSection('expenses')">
                                        <svg class="section-arrow w-4 h-4 transform transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="section-title-full">Expenses</span>
                                        <span class="section-title-short">E</span>
                                    </button>
                                </th>
                                
                                <th class="section-header collapsed" data-section="status">
                                    <button class="section-toggle flex items-center justify-center w-full text-left" 
                                            onclick="toggleSection('status')">
                                        <svg class="section-arrow w-4 h-4 transform transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="section-title-full">Status & Dates</span>
                                        <span class="section-title-short">S.D</span>
                                    </button>
                                </th>
                            </tr>
                            
                            <!-- Sub-headers for expanded sections -->
                            <tr class="sub-headers">
                                <th class="project-column-header"></th>
                                
                                <!-- Project Details Sub-headers -->
                                <th class="section-subheader details-section" style="display: none;">
                                    <div class="sub-header-grid details-grid">
                                        <span>Unit</span>
                                        <span>Area</span>
                                        <span>Garden</span>
                                        <span>Compound</span>
                                    </div>
                                </th>
                                
                                <!-- Contract Details Sub-headers -->
                                <th class="section-subheader contract-section" style="display: none;">
                                    <div class="sub-header-grid contract-grid">
                                        <span>Reserved</span>
                                        <span>Contract Date</span>
                                        <span>Total Value</span>
                                        <span>Years</span>
                                    </div>
                                </th>
                                
                                <!-- Expenses Sub-headers -->
                                <th class="section-subheader expenses-section" style="display: none;">
                                    <div class="sub-header-grid expenses-grid">
                                        <span>Asset Expenses</span>
                                        <span>Operation Expenses</span>
                                        <span>Total Expenses</span>
                                        <span>Net Profit</span>
                                    </div>
                                </th>
                                
                                <!-- Status Sub-headers -->
                                <th class="section-subheader status-section" style="display: none;">
                                    <div class="sub-header-grid status-grid">
                                        <span>Status</span>
                                        <span>Stage</span>
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
                                            @if ($project->developer)
                                                <div class="project-developer">{{ $project->developer->name }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Project Details Section -->
                                    <td class="section-content details-section collapsed" data-section="details">
                                        <div class="section-collapsed-content">
                                            <div class="collapsed-letters">
                                                <span>{{ substr($project->unit_no ?? 'N', 0, 1) }}</span>
                                                <span>{{ substr(number_format($project->area ?? 0, 0), 0, 1) }}</span>
                                                <span>{{ substr(number_format($project->garden_area ?? 0, 0), 0, 1) }}</span>
                                                <span>{{ substr($project->compound->name ?? $project->project ?? 'N', 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <div class="section-expanded-content" style="display: none;">
                                            <div class="expanded-content-wrapper">
                                                <div class="content-row">
                                                    <span class="content-label">Unit:</span>
                                                    <span class="content-value">{{ $project->unit_no ?? 'N/A' }}</span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">Area:</span>
                                                    <span class="content-value">
                                                        @if($project->area)
                                                            {{ number_format($project->area, 0) }} m²
                                                        @else
                                                            N/A
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">Garden:</span>
                                                    <span class="content-value">
                                                        @if($project->garden_area)
                                                            {{ number_format($project->garden_area, 0) }} m²
                                                        @else
                                                            N/A
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">Compound:</span>
                                                    <span class="content-value">{{ $project->compound->name ?? $project->project ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Contract Details Section -->
                                    <td class="section-content contract-section collapsed" data-section="contract">
                                        <div class="section-collapsed-content">
                                            <div class="collapsed-letters">
                                                <span>{{ $project->reservation_date ? 'R' : 'N' }}</span>
                                                <span>{{ $project->contract_date ? 'C' : 'N' }}</span>
                                                <span>{{ $project->total_contract_value ? '$' : 'N' }}</span>
                                                <span>{{ $project->years_of_installment ? 'Y' : 'N' }}</span>
                                            </div>
                                        </div>
                                        <div class="section-expanded-content" style="display: none;">
                                            <div class="expanded-content-wrapper">
                                                <div class="content-row">
                                                    <span class="content-label">Reserved:</span>
                                                    <span class="content-value">
                                                        @if ($project->reservation_date)
                                                            {{ $project->reservation_date->format('M d, Y') }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">Contract Date:</span>
                                                    <span class="content-value">
                                                        @if ($project->contract_date)
                                                            {{ $project->contract_date->format('M d, Y') }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">Total Value:</span>
                                                    <span class="content-value font-medium">
                                                        @if ($project->total_contract_value)
                                                            ${{ number_format($project->total_contract_value, 0) }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">Years:</span>
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
                                    <td class="section-content expenses-section collapsed" data-section="expenses">
                                        <div class="section-collapsed-content">
                                            <div class="collapsed-letters">
                                                <span class="text-red-600 dark:text-red-400">A</span>
                                                <span class="text-red-600 dark:text-red-400">O</span>
                                                <span class="text-red-600 dark:text-red-400">T</span>
                                                <span class="{{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">N</span>
                                            </div>
                                        </div>
                                        <div class="section-expanded-content" style="display: none;">
                                            <div class="expanded-content-wrapper">
                                                <div class="content-row">
                                                    <span class="content-label">Asset Expenses:</span>
                                                    <span class="content-value text-red-600 dark:text-red-400 font-medium">
                                                        ${{ number_format($assetExpenses, 0) }}
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">Operation Expenses:</span>
                                                    <span class="content-value text-red-600 dark:text-red-400 font-medium">
                                                        ${{ number_format($operationExpenses, 0) }}
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">Total Expenses:</span>
                                                    <span class="content-value text-red-600 dark:text-red-400 font-bold">
                                                        ${{ number_format($totalExpenses, 0) }}
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">Net Profit:</span>
                                                    <span class="content-value font-bold {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                        ${{ number_format($netProfit, 0) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Status Section -->
                                    <td class="section-content status-section collapsed" data-section="status">
                                        <div class="section-collapsed-content">
                                            <div class="collapsed-letters">
                                                <span class="
                                                    @if ($project->status === 'active') text-green-600 dark:text-green-400
                                                    @elseif($project->status === 'exited') text-red-600 dark:text-red-400
                                                    @elseif($project->status === 'pending') text-yellow-600 dark:text-yellow-400
                                                    @else text-gray-600 dark:text-gray-400 @endif">
                                                    {{ $project->status === 'exited' ? 'S' : ($project->status === 'active' ? 'H' : 'P') }}
                                                </span>
                                                <span>{{ substr(ucfirst($project->stage ?? 'N'), 0, 1) }}</span>
                                                <span>{{ $projectData['entry_date'] ? 'S' : 'N' }}</span>
                                                <span>{{ $projectData['exit_date'] ? 'E' : 'N' }}</span>
                                            </div>
                                        </div>
                                        <div class="section-expanded-content" style="display: none;">
                                            <div class="expanded-content-wrapper">
                                                <div class="content-row">
                                                    <span class="content-label">Status:</span>
                                                    <span class="status-badge
                                                        @if ($project->status === 'active') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                        @elseif($project->status === 'exited') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                        @elseif($project->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                                        @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100 @endif">
                                                        {{ $project->status === 'exited' ? 'Sold' : ($project->status === 'active' ? 'On Hold' : ucfirst($project->status)) }}
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">Stage:</span>
                                                    <span class="content-value">{{ ucfirst($project->stage ?? 'N/A') }}</span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">Start Date:</span>
                                                    <span class="content-value">
                                                        @if ($projectData['entry_date'])
                                                            {{ \Carbon\Carbon::parse($projectData['entry_date'])->format('M d, Y') }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="content-row">
                                                    <span class="content-label">End Date:</span>
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
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
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

        /* Section Headers */
        .section-header {
            padding: 8px 12px;
            background: #f9fafb;
            border-left: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .section-header.collapsed {
            min-width: 60px;
            max-width: 60px;
        }

        .section-header.expanded {
            min-width: 200px;
        }

        .dark .section-header {
            background: #374151;
            border-left-color: #4b5563;
        }

        .section-toggle {
            background: none;
            border: none;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.2s;
        }

        .dark .section-toggle {
            color: #d1d5db;
        }

        .section-toggle:hover {
            color: #374151;
        }

        .dark .section-toggle:hover {
            color: #f9fafb;
        }

        .section-arrow {
            transition: transform 0.2s ease;
        }

        .section-arrow.expanded {
            transform: rotate(90deg);
        }

        /* Section Title States */
        .section-title-full {
            display: none;
        }

        .section-title-short {
            display: inline;
            font-size: 0.75rem;
            font-weight: 700;
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
        }

        .dark .sub-header-grid {
            color: #9ca3af;
        }

        .details-grid {
            grid-template-columns: 1fr 1fr 1fr 1fr;
        }

        .contract-grid {
            grid-template-columns: 1fr 1fr 1fr 1fr;
        }

        .expenses-grid {
            grid-template-columns: 1fr 1fr 1fr 1fr;
        }

        .status-grid {
            grid-template-columns: 1fr 1fr 1fr 1fr;
        }

        /* Section Content */
        .section-content {
            border-left: 1px solid #e5e7eb;
            vertical-align: top;
            transition: all 0.3s ease;
            position: relative;
        }

        .section-content.collapsed {
            min-width: 60px;
            max-width: 60px;
            padding: 8px 4px;
        }

        .section-content.expanded {
            min-width: 200px;
            padding: 16px 12px;
        }

        .dark .section-content {
            border-left-color: #4b5563;
        }

        /* Collapsed Content */
        .section-collapsed-content {
            display: block;
        }

        .section-content.expanded .section-collapsed-content {
            display: none;
        }

        .collapsed-letters {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .collapsed-letters span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #f3f4f6;
            color: #374151;
            font-size: 0.6875rem;
            font-weight: 700;
        }

        .dark .collapsed-letters span {
            background: #4b5563;
            color: #d1d5db;
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .project-column-header,
            .project-column {
                min-width: 150px;
                max-width: 150px;
            }

            .section-header,
            .section-content {
                min-width: 140px;
            }

            .sub-header-grid,
            .content-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
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

    <script>
        function toggleSection(sectionName) {
            // Toggle section header state
            const sectionHeader = document.querySelector(`[data-section="${sectionName}"]`);
            const arrow = sectionHeader.querySelector('.section-arrow');
            
            // Toggle section content elements
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
                
                // Hide sub-headers
                const subHeaders = document.querySelectorAll(`.section-subheader.${sectionName}-section`);
                subHeaders.forEach(header => {
                    header.style.display = 'none';
                });
            } else {
                // Expand section
                sectionHeader.classList.remove('collapsed');
                sectionHeader.classList.add('expanded');
                
                sectionElements.forEach(element => {
                    element.classList.remove('collapsed');
                    element.classList.add('expanded');
                });
                
                // Show sub-headers
                const subHeaders = document.querySelectorAll(`.section-subheader.${sectionName}-section`);
                subHeaders.forEach(header => {
                    header.style.display = 'table-cell';
                });
            }
            
            // Toggle arrow rotation
            if (arrow) {
                arrow.classList.toggle('expanded');
            }
        }

        // Initialize all sections as collapsed
        document.addEventListener('DOMContentLoaded', function() {
            const sections = ['details', 'contract', 'expenses', 'status'];
            sections.forEach(section => {
                // Set headers as collapsed
                const header = document.querySelector(`[data-section="${section}"]`);
                if (header) {
                    header.classList.add('collapsed');
                }
                
                // Set content elements as collapsed
                const elements = document.querySelectorAll(`.${section}-section`);
                elements.forEach(element => {
                    element.classList.add('collapsed');
                    if (element.classList.contains('section-subheader')) {
                        element.style.display = 'none';
                    }
                });
            });
        });
    </script>
</div>
