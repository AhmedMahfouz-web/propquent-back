<x-filament-widgets::widget class="fi-wi-chart">
    <div style="overflow-x: auto; overflow-y: hidden; padding-bottom: 20px;">
        <div style="min-width: 1200px; height: {{ $this->getMaxHeight() }};">
            <canvas
                x-data="{
                    chart: null,
                    
                    init() {
                        this.chart = new Chart($el, {
                            type: '{{ $this->getType() }}',
                            data: {{ \Illuminate\Support\Js::from($this->getData()) }},
                            options: {{ \Illuminate\Support\Js::from($this->getOptions()) }},
                        });
                        
                        $wire.on('updateChartData', ({ data }) => {
                            this.chart.data = data;
                            this.chart.update();
                        });
                    }
                }"
                x-init="init()"
                wire:ignore
            ></canvas>
        </div>
    </div>
</x-filament-widgets::widget>
