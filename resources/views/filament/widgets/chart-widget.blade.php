<x-filament-widgets::widget class="fi-wi-chart">
    <div style="overflow-x: auto; overflow-y: hidden; padding-bottom: 20px;">
        <div style="min-width: 1200px; height: {{ $this->getMaxHeight() }};">
            <div
                wire:ignore
                x-data="{
                    chart: null,
                    init: function () {
                        let chart = this.createChart();
                        $wire.on('updateChartData', ({ data }) => {
                            chart.data = data;
                            chart.update('resize');
                        });
                    },
                    createChart: function () {
                        return this.chart = new Chart(this.$refs.canvas, {
                            type: '{{ $this->getType() }}',
                            data: {{ \Illuminate\Support\Js::from($this->getData()) }},
                            options: {{ \Illuminate\Support\Js::from($this->getOptions()) }},
                        });
                    },
                }"
                x-init="init()"
            >
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
