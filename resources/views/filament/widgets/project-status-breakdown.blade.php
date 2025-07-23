<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Project Status Breakdown
        </x-slot>

        <x-slot name="description">
            Detailed breakdown of projects by status and developer
        </x-slot>

        <x-slot name="headerActions">
            <div class="flex items-center space-x-2">
                @if ($selectedStatus || $selectedDeveloper)
                    <x-filament::button size="sm" color="gray" wire:click="clearFilters">
                        <x-heroicon-o-x-mark class="w-4 h-4 mr-1" />
                        Clear Filters
                    </x-filament::button>
                @endif

                <x-filament::button size="sm" color="gray" wire:click="loadData">
                    <x-heroicon-o-arrow-path class="w-4 h-4 mr-1" />
                    Refresh
                </x-filament::button>
            </div>
        </x-slot>

        <div class="space-y-6">
            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Developer:</span>
                    <select wire:model.live="selectedDeveloper"
                        class="text-sm border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">All Developers</option>
                        @foreach ($this->getDevelopers() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($selectedStatus)
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</span>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColor($selectedStatus) }}">
                            {{ $selectedStatus }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Status Breakdown -->
            <div class="space-y-4">
                @forelse($this->getStatusBreakdown() as $statusGroup)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <!-- Status Header -->
                        <div
                            class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColor($statusGroup['status']) }}">
                                        {{ $statusGroup['status_label'] }}
                                    </span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $statusGroup['total_count'] }}
                                        {{ Str::plural('project', $statusGroup['total_count']) }}
                                    </span>
                                </div>

                                <x-filament::button size="sm" color="primary"
                                    href="/admin/projects?tableFilters[status][value]={{ $statusGroup['status'] }}"
                                    tag="a">
                                    View All
                                </x-filament::button>
                            </div>
                        </div>

                        <!-- Developer Breakdown -->
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($statusGroup['developers'] as $developer)
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-2">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $developer['name'] }}
                                            </h4>
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                                {{ $developer['count'] }}
                                                {{ Str::plural('project', $developer['count']) }}
                                            </span>
                                        </div>

                                        <x-filament::button size="sm" color="gray"
                                            wire:click="selectDeveloper('{{ $developer['id'] }}')">
                                            Filter
                                        </x-filament::button>
                                    </div>

                                    <!-- Project List -->
                                    @if ($showDetails || $selectedDeveloper == $developer['id'])
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @foreach ($developer['projects'] as $project)
                                                <div
                                                    class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3 hover:shadow-md transition-shadow">
                                                    <div class="flex items-start justify-between mb-2">
                                                        <h5
                                                            class="font-medium text-sm text-gray-900 dark:text-gray-100 truncate">
                                                            {{ $project['name'] }}
                                                        </h5>
                                                        <a href="/admin/projects/{{ $project['id'] }}"
                                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                            <x-heroicon-o-eye class="w-4 h-4" />
                                                        </a>
                                                    </div>

                                                    <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                                                        @if ($project['location'])
                                                            <div class="flex items-center">
                                                                <x-heroicon-o-map-pin class="w-3 h-3 mr-1" />
                                                                {{ $project['location'] }}
                                                            </div>
                                                        @endif

                                                        @if ($project['property_type'])
                                                            <div class="flex items-center">
                                                                <x-heroicon-o-building-office class="w-3 h-3 mr-1" />
                                                                {{ $project['property_type'] }}
                                                            </div>
                                                        @endif

                                                        @if ($project['stage'])
                                                            <div class="flex items-center">
                                                                <x-heroicon-o-cog-6-tooth class="w-3 h-3 mr-1" />
                                                                {{ $project['stage'] }}
                                                            </div>
                                                        @endif

                                                        <div class="flex items-center">
                                                            <x-heroicon-o-calendar class="w-3 h-3 mr-1" />
                                                            {{ $project['created_at'] }}
                                                        </div>

                                                        @if ($project['total_area'])
                                                            <div class="flex items-center">
                                                                <x-heroicon-o-squares-2x2 class="w-3 h-3 mr-1" />
                                                                {{ number_format($project['total_area']) }} sqft
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <x-filament::button size="sm" color="gray"
                                                wire:click="$set('showDetails', true)">
                                                Show Project Details
                                            </x-filament::button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                            <x-heroicon-o-building-office-2 class="w-full h-full" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                            No Projects Found
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            @if ($selectedStatus || $selectedDeveloper)
                                No projects match your current filters.
                            @else
                                There are no projects in the system yet.
                            @endif
                        </p>

                        @if ($selectedStatus || $selectedDeveloper)
                            <x-filament::button wire:click="clearFilters" color="primary">
                                Clear Filters
                            </x-filament::button>
                        @else
                            <x-filament::button href="/admin/projects/create" color="primary">
                                Add First Project
                            </x-filament::button>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Listen for chart clicks from the ProjectStatusWidget
            window.addEventListener('project-status-clicked', function(event) {
                const status = event.detail.status;
                console.log('Status clicked:', status);

                // Dispatch Livewire event to update the breakdown widget
                Livewire.dispatch('project-status-clicked', {
                    status: status,
                    index: event.detail.index
                });
            });

            // Auto-refresh widget when configuration changes
            document.addEventListener('configurationChanged', function() {
                Livewire.dispatch('$refresh');
            });
        });
    </script>
@endpush
