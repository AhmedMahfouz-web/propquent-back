@php
    $filter = $this->filter ?? '6';
    $isLongPeriod = (int)$filter >= 12;
    $chartWidth = $isLongPeriod ? ((int)$filter * 100) . 'px' : '100%';
@endphp

<div class="fi-wi-chart relative" style="height: 900px; {{ $isLongPeriod ? 'overflow-x: auto; overflow-y: hidden;' : '' }}">
    <canvas ax-load
        ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
        x-data="chartWidget({
            cachedData: @js($this->getCachedData()),
            options: @js($this->getOptions()),
            type: @js($this->getType()),
        })" 
        x-ignore 
        style="height: 900px !important; min-height: 900px !important; width: {{ $chartWidth }} !important; {{ $isLongPeriod ? 'min-width: ' . $chartWidth . ' !important;' : '' }}">
    </canvas>
</div>
