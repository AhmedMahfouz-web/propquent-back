<x-filament-widgets::widget class="fi-wi-chart">
    <div style="overflow-x: auto; overflow-y: hidden; padding-bottom: 20px;">
        <div style="min-width: 1200px; height: {{ $this->getMaxHeight() }};">
            <canvas
                x-data="{
                    chart: null,
                    init: function () {
                        let chart = this.createChart();
                        $wire.on('updateChartData', ({ data }) => {
                            chart.data = data;
                            chart.update('resize');
                        });
                        $wire.on('filterChartData', ({ data }) => {
                            chart.data = data;
                            chart.update('resize');
                        });
                    },
                    createChart: function () {
                        return this.chart = new Chart($el, {
                            type: '{{ $this->getType() }}',
                            data: {{ \Illuminate\Support\Js::from($this->getData()) }},
                            options: {{ \Illuminate\Support\Js::from($this->getOptions()) }},
                        });
                    },
                }"
                wire:ignore
                x-init="init()"
            ></canvas>
        </div>
    </div>
</x-filament-widgets::widget>
