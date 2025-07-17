<div>
    <div
        x-data="{
            isOpen: false,
            showCustomPicker: false,
            currentColor: 'blue',
            customColor: '#3b82f6',
            colors: {{ json_encode($colors) }},

            init() {
                const savedColor = localStorage.getItem('theme_color');
                const savedCustomColor = localStorage.getItem('custom_theme_color');

                if (savedColor === 'custom' && savedCustomColor) {
                    this.currentColor = 'custom';
                    this.customColor = savedCustomColor;
                    this.applyCustomColor(savedCustomColor);
                } else if (savedColor) {
                    this.currentColor = savedColor;
                    this.applyPalette(this.colors[savedColor].palette);
                } else {
                    this.applyPalette(this.colors['blue'].palette);
                }
            },

            changeColor(colorName) {
                this.currentColor = colorName;
                this.isOpen = false;
                const palette = this.colors[colorName].palette;
                this.applyPalette(palette);
                localStorage.setItem('theme_color', colorName);
                localStorage.removeItem('custom_theme_color');
            },

            triggerCustomColor() {
                this.currentColor = 'custom';
                this.isOpen = false;
                this.applyCustomColor(this.customColor);
                localStorage.setItem('theme_color', 'custom');
                localStorage.setItem('custom_theme_color', this.customColor);
            },

            applyPalette(palette) {
                const style = document.documentElement.style;
                for (const shade in palette) {
                    style.setProperty(`--f-primary-${shade}`, palette[shade]);
                }
                if (palette['500']) {
                    style.setProperty(`--f-primary-rgb`, palette['500'].replace(' ', ', '));
                }
            },

            applyCustomColor(hex) {
                const rgb = this.hexToRgb(hex);
                if (rgb) {
                    const style = document.documentElement.style;
                    style.setProperty('--f-primary-500', `${rgb.r} ${rgb.g} ${rgb.b}`);
                    style.setProperty('--f-primary-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`);
                }
            },

            hexToRgb(hex) {
                const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                return result ? { r: parseInt(result[1], 16), g: parseInt(result[2], 16), b: parseInt(result[3], 16) } : null;
            }
        }"
        class="relative"
    >
        <button 
            @click="isOpen = !isOpen" 
            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-gray-300 dark:hover:bg-gray-800"
            title="Change Theme Color"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v6a2 2 0 002 2h4a2 2 0 002-2V5zM21 15a2 2 0 00-2-2h-4a2 2 0 00-2 2v2a2 2 0 002 2h4a2 2 0 002-2v-2z"/>
            </svg>
            <span>Theme</span>
        </button>

        <div
            x-show="isOpen"
            @click.away="isOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute right-0 top-full z-50 mt-2 w-80 overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-gray-900/10 dark:bg-gray-800 dark:ring-gray-50/10"
            style="display: none;"
        >
            <div class="p-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Choose Theme Color</h3>
                
                <!-- Predefined Colors -->
                <div class="grid grid-cols-6 gap-2 mb-4">
                    @foreach ($colors as $colorKey => $colorData)
                        <button
                            type="button"
                            @click="changeColor('{{ $colorKey }}')"
                            class="group relative h-10 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800"
                            :class="currentColor === '{{ $colorKey }}' ? 'ring-2 ring-offset-2 ring-primary-500 dark:ring-offset-gray-800' : 'ring-1 ring-gray-200 dark:ring-gray-600 hover:ring-2 hover:ring-gray-300 dark:hover:ring-gray-500'"
                            style="background-color: {{ $colorData['color'] }}"
                            title="{{ $colorData['name'] }}"
                        >
                            <template x-if="currentColor === '{{ $colorKey }}'">
                                <svg class="absolute inset-0 m-auto h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </template>
                            <span class="sr-only">{{ $colorData['name'] }}</span>
                        </button>
                    @endforeach
                </div>
                
                <!-- Custom Color Section -->
                <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Custom Color</label>
                        <button
                            type="button"
                            @click="showCustomPicker = !showCustomPicker"
                            class="text-xs text-primary-600 hover:text-primary-500 dark:text-primary-400"
                        >
                            <span x-text="showCustomPicker ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                    
                    <div x-show="showCustomPicker" x-transition class="space-y-3">
                        <div class="flex items-center gap-3">
                            <input
                                type="color"
                                x-model="customColor"
                                class="h-10 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                            >
                            <input
                                type="text"
                                x-model="customColor"
                                placeholder="#3b82f6"
                                class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                        </div>
                        <button
                            type="button"
                            @click="triggerCustomColor()"
                            class="w-full rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        >
                            Apply Custom Color
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
