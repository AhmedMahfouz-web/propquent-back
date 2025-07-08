<div>
    @php
        $colorNames = array_keys(\Filament\Support\Colors\Color::all());
        $currentColor = session('theme_color', 'violet');
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
        class="relative"
    >
        <button @click="isOpen = !isOpen" class="block shrink-0 rounded-full p-2 text-gray-600 hover:bg-gray-500/5 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-900/5">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
        </button>

        <div
            x-show="isOpen"
            @click.away="isOpen = false"
            x-transition
            class="absolute -right-2 top-full z-10 mt-2 w-56 overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-gray-900/10 dark:bg-gray-800 dark:ring-gray-50/10"
            style="display: none;"
        >
            <div class="grid grid-cols-4 gap-2 p-4">
                @foreach ($colorNames as $name)
                    <button
                        type="button"
                        wire:click="changeColor('{{ $name }}')"
                        @click="currentColor = '{{ $name }}'; isOpen = false"
                        class="h-8 w-full rounded-lg focus:outline-none"
                        :class="currentColor === '{{ $name }}' ? 'ring-2 ring-offset-2 ring-primary-500 dark:ring-offset-gray-800' : 'ring-1 ring-gray-900/10 dark:ring-white/20'"
                        :style="`background-color: rgb(${getColors()['{{ $name }}']['500']})`"
                        title="{{ ucfirst($name) }}"
                    >
                        <span class="sr-only">{{ ucfirst($name) }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>


</div>
