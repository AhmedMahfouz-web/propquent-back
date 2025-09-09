<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Project Status Overview</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Comprehensive view of all projects with financial summaries, evaluations, and key dates.
                </p>
            </div>
            
            @livewire('project-status-report')
        </div>
    </div>
</x-filament-panels::page>
