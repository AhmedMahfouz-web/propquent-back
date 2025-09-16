<x-filament-widgets::widget class="fi-wi-chart">
    <div style="height: 800px; width: 100%;">
        <canvas
            x-data="{
                chart: null,
                init() {
                    $nextTick(() => {
                        this.chart = new Chart($el, {
                            type: '{{ $this->getType() }}',
                            data: @js($this->getData()),
                            options: @js($this->getOptions())
                        });
                        
                        $wire.on('updateChartData', ({ data }) => {
                            this.chart.data = data;
                            this.chart.update();
                        });
                    });
                }
            }"
            x-init="init()"
            wire:ignore
        ></canvas>
    </div>
</x-filament-widgets::widget>
