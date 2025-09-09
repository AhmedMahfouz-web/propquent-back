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
            <!-- Project Status Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="sticky left-0 bg-gray-50 dark:bg-gray-700 px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600 min-w-[120px]">
                                    Project
                                </th>
                                <th
                                    class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[80px]">
                                    Status
                                </th>
                                <th
                                    class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[80px]">
                                    Stage
                                </th>
                                <th
                                    class="px-2 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[90px]">
                                    Total Expenses
                                </th>
                                <th
                                    class="px-2 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[90px]">
                                    Total Revenues
                                </th>
                                <th
                                    class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[150px]">
                                    Expense Breakdown
                                </th>
                                <th
                                    class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[150px]">
                                    Revenue Breakdown
                                </th>
                                <th
                                    class="px-2 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[90px]">
                                    Asset Evaluation
                                </th>
                                <th
                                    class="px-2 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[90px]">
                                    Asset Correction
                                </th>
                                <th
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[80px]">
                                    Entry Date
                                </th>
                                <th
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[80px]">
                                    Exit Date
                                </th>
                                <th
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[80px]">
                                    Reservation Date
                                </th>
                                <th
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[80px]">
                                    Contract Date
                                </th>
                                <th
                                    class="px-2 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[90px]">
                                    Contract Value
                                </th>
                                <th
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[70px]">
                                    Contract Years
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($this->projects as $project)
                                @php
                                    $projectData = $this->projectsData[$project->key] ?? [];
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <!-- Project Info (Sticky) -->
                                    <td
                                        class="sticky left-0 bg-white dark:bg-gray-800 px-2 py-2 border-r border-gray-200 dark:border-gray-600">
                                        <div class="space-y-1">
                                            <div class="font-medium text-gray-900 dark:text-white text-xs">
                                                {{ $project->title }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $project->key }}
                                            </div>
                                            @if ($project->developer)
                                                <div class="text-xs text-blue-600 dark:text-blue-400">
                                                    {{ $project->developer->name }}</div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-2 py-2">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if ($project->status === 'active') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                        @elseif($project->status === 'exited') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                        @elseif($project->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100 @endif">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </td>

                                    <!-- Stage -->
                                    <td class="px-2 py-2 text-xs text-gray-900 dark:text-white">
                                        {{ ucfirst($project->stage ?? 'N/A') }}
                                    </td>

                                    <!-- Total Expenses -->
                                    <td class="px-2 py-2 text-right text-xs font-medium text-red-600 dark:text-red-400">
                                        ${{ number_format($projectData['total_expenses'] ?? 0, 2) }}
                                    </td>

                                    <!-- Total Revenues -->
                                    <td
                                        class="px-2 py-2 text-right text-xs font-medium text-green-600 dark:text-green-400">
                                        ${{ number_format($projectData['total_revenues'] ?? 0, 2) }}
                                    </td>

                                    <!-- Expense Breakdown -->
                                    <td class="px-2 py-2">
                                        <div class="space-y-1">
                                            @foreach ($projectData['expense_breakdown'] ?? [] as $type => $amount)
                                                <div class="text-xs text-red-600 dark:text-red-400">
                                                    <span
                                                        class="font-medium">{{ ucfirst(str_replace('_', ' ', $type)) }}:</span>
                                                    ${{ number_format($amount, 2) }}
                                                </div>
                                            @endforeach
                                            @if (empty($projectData['expense_breakdown']))
                                                <span class="text-xs text-gray-400">No expenses</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Revenue Breakdown -->
                                    <td class="px-2 py-2">
                                        <div class="space-y-1">
                                            @foreach ($projectData['revenue_breakdown'] ?? [] as $type => $amount)
                                                <div class="text-xs text-green-600 dark:text-green-400">
                                                    <span
                                                        class="font-medium">{{ ucfirst(str_replace('_', ' ', $type)) }}:</span>
                                                    ${{ number_format($amount, 2) }}
                                                </div>
                                            @endforeach
                                            @if (empty($projectData['revenue_breakdown']))
                                                <span class="text-xs text-gray-400">No revenues</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Asset Evaluation -->
                                    <td
                                        class="px-2 py-2 text-right text-xs font-medium text-blue-600 dark:text-blue-400">
                                        ${{ number_format($projectData['asset_evaluation'] ?? 0, 2) }}
                                    </td>

                                    <!-- Asset Correction -->
                                    <td
                                        class="px-2 py-2 text-right text-xs font-medium text-purple-600 dark:text-purple-400">
                                        ${{ number_format($projectData['asset_correction'] ?? 0, 2) }}
                                    </td>

                                    <!-- Entry Date -->
                                    <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-white">
                                        @if ($projectData['entry_date'])
                                            {{ \Carbon\Carbon::parse($projectData['entry_date'])->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>

                                    <!-- Exit Date -->
                                    <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-white">
                                        @if ($projectData['exit_date'])
                                            {{ \Carbon\Carbon::parse($projectData['exit_date'])->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Reservation Date -->
                                    <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-white">
                                        @if ($project->reservation_date)
                                            {{ $project->reservation_date->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>

                                    <!-- Contract Date -->
                                    <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-white">
                                        @if ($project->contract_date)
                                            {{ $project->contract_date->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>

                                    <!-- Contract Value -->
                                    <td class="px-2 py-2 text-right text-xs font-medium text-gray-900 dark:text-white">
                                        @if ($project->total_contract_value)
                                            ${{ number_format($project->total_contract_value, 2) }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>

                                    <!-- Contract Years -->
                                    <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-white">
                                        @if ($project->years_of_installment)
                                            {{ $project->years_of_installment }} years
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16"
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
        /* Custom scrollbar for the table */
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Dark mode scrollbar */
        .dark .overflow-x-auto::-webkit-scrollbar-track {
            background: #374151;
        }

        .dark .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #6b7280;
        }

        .dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
</div>
