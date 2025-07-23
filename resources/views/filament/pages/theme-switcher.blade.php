<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Current Theme
            </x-slot>

            <div class="text-center">
                @php
                    $currentTheme = auth('admins')->user()?->theme_color ?? 'blue';
                    $themes = [
                        'blue' => ['name' => 'Blue', 'color' => 'bg-blue-500'],
                        'green' => ['name' => 'Green', 'color' => 'bg-green-500'],
                        'purple' => ['name' => 'Purple', 'color' => 'bg-purple-500'],
                        'orange' => ['name' => 'Orange', 'color' => 'bg-orange-500'],
                        'red' => ['name' => 'Red', 'color' => 'bg-red-500'],
                        'indigo' => ['name' => 'Indigo', 'color' => 'bg-indigo-500'],
                        'pink' => ['name' => 'Pink', 'color' => 'bg-pink-500'],
                        'teal' => ['name' => 'Teal', 'color' => 'bg-teal-500'],
                    ];
                @endphp

                <div class="flex items-center justify-center space-x-4 mb-6">
                    <div class="w-16 h-16 rounded-full {{ $themes[$currentTheme]['color'] }} shadow-lg"></div>
                    <div>
                        <h3 class="text-xl font-semibold">{{ $themes[$currentTheme]['name'] }} Theme</h3>
                        <p class="text-gray-600">Currently active theme</p>
                    </div>
                </div>

                <p class="text-gray-600 mb-6">
                    Click the "Switch Theme" button above to change your dashboard color theme.
                </p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Available Themes
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($themes as $themeKey => $theme)
                    <div
                        class="text-center p-4 rounded-lg border {{ $currentTheme === $themeKey ? 'border-gray-400 bg-gray-50' : 'border-gray-200' }}">
                        <div class="w-12 h-12 rounded-full {{ $theme['color'] }} mx-auto mb-2 shadow-md"></div>
                        <span class="text-sm font-medium">{{ $theme['name'] }}</span>
                        @if ($currentTheme === $themeKey)
                            <div class="mt-1">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                    Active
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
