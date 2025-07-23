@props([
    'filters' => [],
    'hasFilters' => false,
])

<div class="text-center py-16">
    <div class="mx-auto h-24 w-24 text-gray-400 mb-6">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-full h-full">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
    </div>

    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
        No Project Data Found
    </h3>

    @if ($hasFilters)
        <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
            No projects match your current filter criteria. Try adjusting your filters or expanding the date range to
            see more data.
        </p>

        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-6 max-w-lg mx-auto">
            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Current Filters:</h4>
            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                @if (!empty($filters['start_date']) && !empty($filters['end_date']))
                    <div>📅 Date Range: {{ $filters['start_date'] }} to {{ $filters['end_date'] }}</div>
                @endif
                @if (!empty($filters['developer_id']))
                    <div>🏢 Developer: Selected</div>
                @endif
                @if (!empty($filters['status']))
                    <div>📊 Status: {{ $filters['status'] }}</div>
                @endif
                @if (!empty($filters['stage']))
                    <div>🏗️ Stage: {{ $filters['stage'] }}</div>
                @endif
                @if (!empty($filters['property_type']))
                    <div>🏠 Property Type: {{ $filters['property_type'] }}</div>
                @endif
                @if (!empty($filters['location']))
                    <div>📍 Location: {{ $filters['location'] }}</div>
                @endif
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <x-filament::button wire:click="resetFilters" color="primary">
                <x-heroicon-o-x-mark class="w-4 h-4 mr-2" />
                Clear All Filters
            </x-filament::button>

            <x-filament::button wire:click="applyQuickFilter('last_12_months')" color="gray">
                <x-heroicon-o-calendar class="w-4 h-4 mr-2" />
                Show Last 12 Months
            </x-filament::button>
        </div>
    @else
        <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
            It looks like there are no projects in your system yet, or they don't have the required data for reporting.
        </p>

        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6 max-w-lg mx-auto">
            <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Getting Started:</h4>
            <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1 text-left">
                <div>1. Add some projects to your system</div>
                <div>2. Record project transactions</div>
                <div>3. Set project statuses and stages</div>
                <div>4. Come back to view your reports</div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <x-filament::button href="/admin/projects/create" color="primary">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                Add Your First Project
            </x-filament::button>

            <x-filament::button wire:click="loadReportData" color="gray">
                <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" />
                Refresh Data
            </x-filament::button>
        </div>
    @endif

    <!-- Help Section -->
    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Need Help?</h4>
        <div class="flex flex-wrap justify-center gap-4 text-sm">
            <a href="#"
                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center">
                <x-heroicon-o-question-mark-circle class="w-4 h-4 mr-1" />
                Report Guide
            </a>
            <a href="#"
                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center">
                <x-heroicon-o-document-text class="w-4 h-4 mr-1" />
                Documentation
            </a>
            <a href="#"
                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center">
                <x-heroicon-o-chat-bubble-left class="w-4 h-4 mr-1" />
                Contact Support
            </a>
        </div>
    </div>
</div>
