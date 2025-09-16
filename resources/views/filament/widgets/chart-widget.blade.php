<div class="fi-wi-chart relative" style="height: 1800px;">
    <canvas
        ax-load
        ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
        x-data="chartWidget({
            cachedData: @js($this->getCachedData()),
            options: @js($this->getOptions()),
            type: @js($this->getType()),
        })"
        x-ignore
        style="height: 1800px !important; min-height: 1800px !important;"
    ></canvas>
</div>
