@props([
    'size' => 'md',
    'showLabels' => true,
    'inline' => false,
    'currentTheme' => null,
])

@php
    $currentTheme = $currentTheme ?? (auth('admins')->user()?->theme_color ?? 'blue');

    $themes = [
        'blue' => ['name' => 'Blue', 'color' => '#3b82f6'],
        'green' => ['name' => 'Green', 'color' => '#10b981'],
        'purple' => ['name' => 'Purple', 'color' => '#8b5cf6'],
        'orange' => ['name' => 'Orange', 'color' => '#f59e0b'],
        'red' => ['name' => 'Red', 'color' => '#ef4444'],
        'indigo' => ['name' => 'Indigo', 'color' => '#6366f1'],
        'pink' => ['name' => 'Pink', 'color' => '#ec4899'],
        'teal' => ['name' => 'Teal', 'color' => '#14b8a6'],
    ];

    $sizeClasses = [
        'sm' => 'w-6 h-6',
        'md' => 'w-8 h-8',
        'lg' => 'w-10 h-10',
    ];

    $containerClasses = $inline ? 'flex flex-wrap gap-2' : 'grid grid-cols-4 gap-3';
@endphp

<div class="theme-switcher">
    @if ($showLabels && !$inline)
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Choose Theme Color
        </label>
    @endif

    <div class="{{ $containerClasses }}">
        @foreach ($themes as $themeKey => $theme)
            <button type="button" data-theme-button data-theme="{{ $themeKey }}"
                class="theme-button {{ $sizeClasses[$size] }} rounded-lg border-2 transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $currentTheme === $themeKey ? 'border-gray-800 dark:border-gray-200 ring-2 ring-gray-800 dark:ring-gray-200' : 'border-gray-300 dark:border-gray-600' }}"
                style="background-color: {{ $theme['color'] }}" title="{{ $theme['name'] }} Theme"
                aria-label="Switch to {{ $theme['name'] }} theme"
                @if ($currentTheme === $themeKey) aria-pressed="true" @endif>
                @if ($currentTheme === $themeKey)
                    <svg class="w-4 h-4 text-white mx-auto" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                @endif
            </button>

            @if ($showLabels && !$inline)
                <span class="text-xs text-center text-gray-600 dark:text-gray-400 mt-1">
                    {{ $theme['name'] }}
                </span>
            @endif
        @endforeach
    </div>

    @if ($showLabels && $inline)
        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Current: <span class="font-medium">{{ $themes[$currentTheme]['name'] ?? 'Blue' }}</span>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize theme buttons
            const themeButtons = document.querySelectorAll('[data-theme-button]');

            themeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const theme = this.getAttribute('data-theme');

                    // Update button states
                    themeButtons.forEach(btn => {
                        btn.classList.remove('border-gray-800', 'dark:border-gray-200',
                            'ring-2', 'ring-gray-800', 'dark:ring-gray-200');
                        btn.classList.add('border-gray-300', 'dark:border-gray-600');
                        btn.setAttribute('aria-pressed', 'false');
                        btn.innerHTML = '';
                    });

                    // Activate clicked button
                    this.classList.remove('border-gray-300', 'dark:border-gray-600');
                    this.classList.add('border-gray-800', 'dark:border-gray-200', 'ring-2',
                        'ring-gray-800', 'dark:ring-gray-200');
                    this.setAttribute('aria-pressed', 'true');
                    this.innerHTML =
                        '<svg class="w-4 h-4 text-white mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>';

                    // Use theme manager if available
                    if (window.themeManager) {
                        window.themeManager.switchTheme(theme);
                    }

                    // Update current theme display
                    const currentThemeSpan = document.querySelector(
                        '.theme-switcher span.font-medium');
                    if (currentThemeSpan) {
                        const themes = @json($themes);
                        currentThemeSpan.textContent = themes[theme]?.name || 'Blue';
                    }
                });
            });
        });
    </script>
@endpush
