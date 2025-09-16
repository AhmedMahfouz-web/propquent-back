@php
    $chartId = 'chart-' . $this->getId();
@endphp

<x-filament-widgets::widget class="fi-wi-chart">
    <div style="overflow-x: auto; overflow-y: hidden; padding-bottom: 20px;">
        <div style="min-width: 2000px; height: {{ $this->getMaxHeight() }};">
            <canvas id="{{ $chartId }}" wire:ignore></canvas>
        </div>
    </div>
</x-filament-widgets::widget>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('{{ $chartId }}');
    if (!canvas) return;

    // Wait for Chart.js to be available
    function initChart() {
        if (typeof Chart !== 'undefined') {
            const chart = new Chart(canvas, {
                type: '{{ $this->getType() }}',
                data: {!! \Illuminate\Support\Js::from($this->getData()) !!},
                options: {!! \Illuminate\Support\Js::from($this->getOptions()) !!}
            });

            // Listen for Livewire updates
            Livewire.on('updateChartData', (data) => {
                chart.data = data;
                chart.update();
            });
        } else {
            setTimeout(initChart, 100);
        }
    }

    initChart();
});
</script>
