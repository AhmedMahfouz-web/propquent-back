@php
$colors = [
    'zinc' => '#71717a',
    'slate' => '#64748b',
    'stone' => '#78716c',
    'gray' => '#6b7280',
    'neutral' => '#737373',
    'red' => '#ef4444',
    'orange' => '#f97316',
    'amber' => '#f59e0b',
    'yellow' => '#eab308',
    'lime' => '#84cc16',
    'green' => '#22c55e',
    'emerald' => '#10b981',
    'teal' => '#14b8a6',
    'cyan' => '#06b6d4',
    'sky' => '#0ea5e9',
    'blue' => '#3b82f6',
    'indigo' => '#6366f1',
    'violet' => '#8b5cf6',
    'purple' => '#a855f7',
    'fuchsia' => '#d946ef',
    'pink' => '#ec4899',
    'rose' => '#f43f5e',
];
$currentColor = session('theme_color', 'zinc');
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div
            x-data="{
                init() {
                    $wire.on('color-changed', () => {
                        window.location.reload();
                    });
                }
            }"
        >
            <div class="p-2">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Theme Color
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($colors as $name => $hex)
                        <button
                            type="button"
                            wire:click="changeColor('{{ $name }}')"
                            class="w-8 h-8 rounded-full focus:outline-none transition-transform duration-150 hover:scale-110"
                            :class="'{{ $currentColor }}' === '{{ $name }}' ? 'ring-2 ring-offset-2 ring-gray-900 dark:ring-gray-100 ring-offset-white dark:ring-offset-gray-800' : ''"
                            style="background-color: {{ $hex }}"
                            title="{{ ucfirst($name) }}"
                        >
                            <span class="sr-only">{{ ucfirst($name) }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
