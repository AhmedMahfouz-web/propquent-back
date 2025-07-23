@props([
    'showAll' => false,
])

<div class="theme-test-container space-y-6 p-6">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
            Theme Component Test
        </h2>
        <p class="text-gray-600 dark:text-gray-400">
            Test all Filament components with current theme
        </p>
    </div>

    <!-- Theme Switcher -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
        <h3 class="text-lg font-semibold mb-4">Theme Switcher</h3>
        <x-theme-switcher />
    </div>

    <!-- Buttons -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
        <h3 class="text-lg font-semibold mb-4">Buttons</h3>
        <div class="flex flex-wrap gap-3">
            <button class="fi-btn-primary px-4 py-2 rounded-md text-white font-medium">
                Primary Button
            </button>
            <button class="fi-btn-secondary px-4 py-2 rounded-md border border-gray-300 text-gray-700 font-medium">
                Secondary Button
            </button>
            <button class="fi-action-button-primary px-3 py-2 rounded text-white text-sm">
                Action Button
            </button>
        </div>
    </div>

    <!-- Form Elements -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
        <h3 class="text-lg font-semibold mb-4">Form Elements</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Text Input
                </label>
                <input type="text"
                    class="fi-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none"
                    placeholder="Enter text here" value="Sample text">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Select
                </label>
                <select class="fi-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none">
                    <option>Option 1</option>
                    <option selected>Option 2 (Selected)</option>
                    <option>Option 3</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Textarea
                </label>
                <textarea class="fi-textarea w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none" rows="3"
                    placeholder="Enter description">Sample textarea content</textarea>
            </div>
            <div class="space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" class="fi-checkbox mr-2" checked>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Checked checkbox</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" class="fi-checkbox mr-2">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Unchecked checkbox</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="radio-test" class="fi-radio mr-2" checked>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Selected radio</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="radio-test" class="fi-radio mr-2">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Unselected radio</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Badges and Status -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
        <h3 class="text-lg font-semibold mb-4">Badges & Status</h3>
        <div class="flex flex-wrap gap-2">
            <span class="fi-badge-primary px-2 py-1 rounded text-xs font-medium">Primary Badge</span>
            <span class="fi-badge-outline-primary px-2 py-1 rounded border text-xs font-medium">Outline Badge</span>
            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">Success</span>
            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">Warning</span>
            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">Error</span>
        </div>
    </div>

    <!-- Links -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
        <h3 class="text-lg font-semibold mb-4">Links</h3>
        <div class="space-y-2">
            <div>
                <a href="#" class="fi-link text-sm font-medium hover:underline">Primary Link</a>
            </div>
            <div>
                <a href="#" class="text-gray-600 hover:text-gray-800 text-sm">Secondary Link</a>
            </div>
        </div>
    </div>

    @if ($showAll)
        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h3 class="text-lg font-semibold mb-4">Table</h3>
            <div class="overflow-x-auto">
                <table class="fi-table w-full">
                    <thead class="fi-table-header">
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Name</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Status</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="fi-table-row border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">John Doe</td>
                            <td class="py-3 px-4">
                                <span class="fi-badge-primary px-2 py-1 rounded text-xs">Active</span>
                            </td>
                            <td class="py-3 px-4">
                                <button class="fi-action-button-primary px-2 py-1 rounded text-xs">Edit</button>
                            </td>
                        </tr>
                        <tr class="fi-table-row fi-table-row-selected border-b border-gray-100">
                            <td class="py-3 px-4">Jane Smith</td>
                            <td class="py-3 px-4">
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span>
                            </td>
                            <td class="py-3 px-4">
                                <button class="fi-action-button-primary px-2 py-1 rounded text-xs">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h3 class="text-lg font-semibold mb-4">Progress Bar</h3>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Progress</span>
                        <span>75%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="fi-progress-bar h-2 rounded-full" style="width: 75%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h3 class="text-lg font-semibold mb-4">Pagination</h3>
            <div class="flex items-center space-x-1">
                <button class="fi-pagination-item px-3 py-2 border border-gray-300 rounded text-sm">Previous</button>
                <button class="fi-pagination-item px-3 py-2 border border-gray-300 rounded text-sm">1</button>
                <button class="fi-pagination-item fi-pagination-item-active px-3 py-2 border rounded text-sm">2</button>
                <button class="fi-pagination-item px-3 py-2 border border-gray-300 rounded text-sm">3</button>
                <button class="fi-pagination-item px-3 py-2 border border-gray-300 rounded text-sm">Next</button>
            </div>
        </div>
    @endif

    <!-- Theme Status -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
        <h3 class="text-lg font-semibold mb-4">Theme Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <strong>Current Theme:</strong>
                <span id="current-theme-display">{{ auth('admins')->user()?->theme_color ?? 'blue' }}</span>
            </div>
            <div>
                <strong>Primary RGB:</strong>
                <span id="primary-rgb-display">--</span>
            </div>
            <div>
                <strong>Theme Status:</strong>
                <span id="theme-status-display" class="text-green-600">Ready</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update theme status display
            function updateThemeStatus() {
                const currentThemeEl = document.getElementById('current-theme-display');
                const primaryRgbEl = document.getElementById('primary-rgb-display');
                const themeStatusEl = document.getElementById('theme-status-display');

                if (window.themeManager) {
                    const currentTheme = window.themeManager.currentTheme;
                    const primaryRgb = getComputedStyle(document.documentElement).getPropertyValue('--primary-rgb');

                    if (currentThemeEl) currentThemeEl.textContent = currentTheme;
                    if (primaryRgbEl) primaryRgbEl.textContent = primaryRgb || 'Not set';
                    if (themeStatusEl) {
                        themeStatusEl.textContent = 'Active';
                        themeStatusEl.className = 'text-green-600';
                    }
                } else {
                    if (themeStatusEl) {
                        themeStatusEl.textContent = 'Not loaded';
                        themeStatusEl.className = 'text-red-600';
                    }
                }
            }

            // Listen for theme changes
            document.addEventListener('themeChanged', function(e) {
                updateThemeStatus();
                console.log('Theme changed to:', e.detail.theme);
            });

            // Initial update
            setTimeout(updateThemeStatus, 100);
        });
    </script>
@endpush
