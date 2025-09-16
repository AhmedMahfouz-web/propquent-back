<x-filament-widgets::widget class="fi-wi-chart">
    <div style="height: 800px; width: 100%;">
        <canvas
            x-data="{
                chart: null,
                init() {
                    const initChart = () => {
                        if (typeof Chart !== 'undefined') {
                            this.chart = new Chart($el, {
                                type: '{{ $this->getType() }}',
                                data: @js($this->getData()),
                                options: @js($this->getOptions())
                            });
                            
                            $wire.on('updateChartData', ({ data }) => {
                                this.chart.data = data;
                                this.chart.update();
                            });
                        } else {
                            setTimeout(initChart, 100);
                        }
                    };
                    
                    $nextTick(initChart);
                }
            }"
            x-init="init()"
            wire:ignore
        ></canvas>
    </div>
    
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
</x-filament-widgets::widget>
