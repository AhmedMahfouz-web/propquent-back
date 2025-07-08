@php
$colorNames = [
    'zinc', 'slate', 'stone', 'gray', 'neutral', 'red', 'orange', 'amber', 'yellow', 'lime',
    'green', 'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia',
    'pink', 'rose',
];
$currentColor = session('theme_color', 'zinc');
@endphp

<div
    x-data="{
        isOpen: false,
        currentColor: '{{ $currentColor }}',
        init() {
            window.addEventListener('update-theme-color', (event) => {
                const palette = event.detail.palette;
                const style = document.documentElement.style;

                for (const shade in palette) {
                    style.setProperty(`--f-primary-${shade}`, palette[shade]);
                }
                if (palette['500']) {
                    style.setProperty(`--f-primary-rgb`, palette['500'].replace(' ', ', '));
                }
            });
        },
        getColors() {
            return @json(\Filament\Support\Colors\Color::all());
        }
    }"
    class="fixed bottom-4 right-4 z-50"
>
    <!-- Settings Button -->
    <button
        @click="isOpen = !isOpen"
        class="flex items-center justify-center w-12 h-12 bg-primary-600 text-white rounded-full shadow-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-transform duration-200 hover:scale-110"
        aria-label="Open theme settings"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
    </button>

    <!-- Slide-out Panel -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="transform translate-x-full"
        x-transition:enter-end="transform translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="transform translate-x-0"
        x-transition:leave-end="transform translate-x-full"
        @click.away="isOpen = false"
        class="fixed top-0 right-0 h-full w-72 bg-white dark:bg-gray-800 shadow-xl p-6 overflow-y-auto"
        style="display: none;"
    >
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Theme Color</h3>
        <div class="flex flex-wrap items-center gap-3">
            @foreach ($colorNames as $name)
                <button
                    type="button"
                    wire:click="changeColor('{{ $name }}')"
                    @click="currentColor = '{{ $name }}'"
                    class="w-10 h-10 rounded-full focus:outline-none transition-transform duration-150 hover:scale-110"
                    :class="currentColor === '{{ $name }}' ? 'ring-2 ring-offset-2 ring-primary-500 dark:ring-primary-400 ring-offset-white dark:ring-offset-gray-800' : ''"
                    :style="`background-color: rgb(${getColors()['{{ $name }}']['500']})`"
                    title="{{ ucfirst($name) }}"
                >
                    <span class="sr-only">{{ ucfirst($name) }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>
