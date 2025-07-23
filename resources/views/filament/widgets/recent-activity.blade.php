<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Recent Activity
        </x-slot>

        <x-slot name="description">
            Latest project updates, transactions, and system changes
        </x-slot>

        <x-slot name="headerActions">
            <div class="flex items-center space-x-2">
                <x-filament::button size="sm" color="gray" wire:click="$refresh">
                    <x-heroicon-o-arrow-path class="w-4 h-4 mr-1" />
                    Refresh
                </x-filament::button>

                <x-filament::button size="sm" color="primary" href="/admin/projects" tag="a">
                    View All Projects
                </x-filament::button>
            </div>
        </x-slot>

        <div class="space-y-6">
            <!-- Activity Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php $stats = $this->getActivityStats(); @endphp

                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                    <div class="text-xs font-medium text-blue-700 dark:text-blue-300">Last 24 Hours</div>
                    <div class="text-lg font-bold text-blue-900 dark:text-blue-100">
                        {{ $stats['projects_updated_24h'] }}
                    </div>
                    <div class="text-xs text-blue-600 dark:text-blue-400">Projects Updated</div>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg">
                    <div class="text-xs font-medium text-green-700 dark:text-green-300">Last 24 Hours</div>
                    <div class="text-lg font-bold text-green-900 dark:text-green-100">
                        {{ $stats['transactions_24h'] }}
                    </div>
                    <div class="text-xs text-green-600 dark:text-green-400">New Transactions</div>
                </div>

                <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded-lg">
                    <div class="text-xs font-medium text-purple-700 dark:text-purple-300">Last 7 Days</div>
                    <div class="text-lg font-bold text-purple-900 dark:text-purple-100">
                        ${{ number_format($stats['total_transactions_7d'], 0) }}
                    </div>
                    <div class="text-xs text-purple-600 dark:text-purple-400">Transaction Volume</div>
                </div>

                <div class="bg-orange-50 dark:bg-orange-900/20 p-3 rounded-lg">
                    <div class="text-xs font-medium text-orange-700 dark:text-orange-300">Last 7 Days</div>
                    <div class="text-lg font-bold text-orange-900 dark:text-orange-100">
                        {{ $stats['config_changes_7d'] }}
                    </div>
                    <div class="text-xs text-orange-600 dark:text-orange-400">Config Changes</div>
                </div>
            </div>

            <!-- Activity Feed -->
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($this->getRecentActivities() as $activity)
                    <div
                        class="flex items-start space-x-3 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition-shadow">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-0.5">
                            <div
                                class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                @php
                                    $iconComponent = 'heroicon-o-' . str_replace('heroicon-o-', '', $activity['icon']);
                                @endphp
                                <x-dynamic-component :component="$iconComponent" class="w-4 h-4 {{ $activity['color'] }}" />
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $activity['title'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $this->formatTimeAgo($activity['timestamp']) }}
                                </p>
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ $activity['description'] }}
                            </p>

                            <!-- Activity Details -->
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                @if (isset($activity['project']))
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                        <x-heroicon-o-building-office class="w-3 h-3 mr-1" />
                                        {{ $activity['project'] }}
                                    </span>
                                @endif

                                @if (isset($activity['developer']))
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400">
                                        <x-heroicon-o-user class="w-3 h-3 mr-1" />
                                        {{ $activity['developer'] }}
                                    </span>
                                @endif

                                @if (isset($activity['amount']))
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                        <x-heroicon-o-banknotes class="w-3 h-3 mr-1" />
                                        ${{ number_format($activity['amount'], 2) }}
                                    </span>
                                @endif

                                @if (isset($activity['status']))
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                        <x-heroicon-o-flag class="w-3 h-3 mr-1" />
                                        {{ ucwords(str_replace('_', ' ', $activity['status'])) }}
                                    </span>
                                @endif

                                @if (isset($activity['category']))
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400">
                                        <x-heroicon-o-cog-6-tooth class="w-3 h-3 mr-1" />
                                        {{ ucwords(str_replace('_', ' ', $activity['category'])) }}
                                    </span>
                                @endif
                            </div>

                            <!-- Action Link -->
                            @if (isset($activity['url']))
                                <div class="mt-2">
                                    <a href="{{ $activity['url'] }}"
                                        class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                        View Details →
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                            <x-heroicon-o-bell-slash class="w-full h-full" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                            No Recent Activity
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            No recent project updates, transactions, or system changes found.
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- View More Link -->
            @if (count($this->getRecentActivities()) >= 20)
                <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-filament::button size="sm" color="gray" href="/admin/projects">
                        View All Activity
                    </x-filament::button>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-refresh activity feed every 2 minutes
            setInterval(function() {
                Livewire.dispatch('$refresh');
            }, 120000);

            // Listen for configuration changes to refresh activity
            document.addEventListener('configurationChanged', function() {
                setTimeout(() => {
                    Livewire.dispatch('$refresh');
                }, 1000);
            });
        });
    </script>
@endpush
