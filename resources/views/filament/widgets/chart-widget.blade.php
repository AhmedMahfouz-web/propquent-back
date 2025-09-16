<div class="fi-wi-chart relative">
    <canvas
        ax-load
        ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
        x-data="chartWidget({
            cachedData: @js($this->getCachedData()),
            options: @js($this->getOptions()),
            type: @js($this->getType()),
        })"
        x-ignore
    ></canvas>
</div>
