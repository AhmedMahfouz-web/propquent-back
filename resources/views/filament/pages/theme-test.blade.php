<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Theme Test Component -->
        <x-theme-test :show-all="true" />

        <!-- Additional Test Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Notification Test -->
            <x-filament::section>
                <x-slot name="heading">
                    Notification Test
                </x-slot>

                <x-slot name="description">
                    Test different notification types with current theme
                </x-slot>

                <div class="space-y-3">
                    <x-filament::button
                        wire:click="$dispatch('notify', { type: 'success', title: 'Success!', body: 'This is a success notification' })"
                        color="success" size="sm">
                        Test Success Notification
                    </x-filament::button>

                    <x-filament::button
                        wire:click="$dispatch('notify', { type: 'warning', title: 'Warning!', body: 'This is a warning notification' })"
                        color="warning" size="sm">
                        Test Warning Notification
                    </x-filament::button>

                    <x-filament::button
                        wire:click="$dispatch('notify', { type: 'danger', title: 'Error!', body: 'This is an error notification' })"
                        color="danger" size="sm">
                        Test Error Notification
                    </x-filament::button>

                    <x-filament::button
                        wire:click="$dispatch('notify', { type: 'info', title: 'Info', body: 'This is an info notification with theme colors' })"
                        color="info" size="sm">
                        Test Info Notification
                    </x-filament::button>
                </div>
            </x-filament::section>

            <!-- Modal Test -->
            <x-filament::section>
                <x-slot name="heading">
                    Modal Test
                </x-slot>

                <x-slot name="description">
                    Test modal components with theme colors
                </x-slot>

                <div class="space-y-3">
                    <x-filament::button x-data=""
                        x-on:click="$dispatch('open-modal', { id: 'theme-test-modal' })" color="primary">
                        Open Test Modal
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>

        <!-- Theme Performance Test -->
        <x-filament::section>
            <x-slot name="heading">
                Theme Performance Test
            </x-slot>

            <x-slot name="description">
                Monitor theme switching performance and transitions
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                    <div class="font-medium text-gray-700 dark:text-gray-300">Transition Duration</div>
                    <div id="transition-duration" class="text-lg font-bold text-primary-600">0.3s</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                    <div class="font-medium text-gray-700 dark:text-gray-300">Last Switch Time</div>
                    <div id="last-switch-time" class="text-lg font-bold text-primary-600">--</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                    <div class="font-medium text-gray-700 dark:text-gray-300">Switch Count</div>
                    <div id="switch-count" class="text-lg font-bold text-primary-600">0</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                    <div class="font-medium text-gray-700 dark:text-gray-300">Performance</div>
                    <div id="performance-status" class="text-lg font-bold text-green-600">Good</div>
                </div>
            </div>
        </x-filament::section>
    </div>

    <!-- Test Modal -->
    <x-filament::modal id="theme-test-modal" width="md">
        <x-slot name="heading">
            Theme Test Modal
        </x-slot>

        <div class="space-y-4">
            <p class="text-gray-600 dark:text-gray-400">
                This modal should reflect the current theme colors. Notice how the primary colors
                are applied to buttons and interactive elements.
            </p>

            <div class="flex space-x-2">
                <x-filament::button color="primary">Primary Button</x-filament::button>
                <x-filament::button color="secondary">Secondary Button</x-filament::button>
            </div>

            <div>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" placeholder="Test input in modal" />
                </x-filament::input.wrapper>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button color="primary" x-on:click="close">
                Close Modal
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let switchCount = 0;
            let lastSwitchTime = null;

            // Monitor theme changes
            document.addEventListener('themeChanged', function(e) {
                switchCount++;
                lastSwitchTime = new Date();

                // Update performance metrics
                document.getElementById('switch-count').textContent = switchCount;
                document.getElementById('last-switch-time').textContent = lastSwitchTime
                .toLocaleTimeString();

                // Check performance
                const performanceEl = document.getElementById('performance-status');
                if (switchCount > 10) {
                    performanceEl.textContent = 'Excellent';
                    performanceEl.className = 'text-lg font-bold text-green-600';
                } else if (switchCount > 5) {
                    performanceEl.textContent = 'Good';
                    performanceEl.className = 'text-lg font-bold text-blue-600';
                }

                console.log('Theme performance test - Switch #' + switchCount + ' to ' + e.detail.theme);
            });

            // Test notification dispatcher
            window.addEventListener('notify', function(e) {
                if (window.Filament?.notifications) {
                    window.Filament.notifications.send({
                        title: e.detail.title,
                        body: e.detail.body,
                        type: e.detail.type,
                        duration: 4000
                    });
                }
            });
        });
    </script>
@endpush
