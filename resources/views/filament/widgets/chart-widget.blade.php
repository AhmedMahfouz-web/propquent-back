<x-filament-widgets::widget class="fi-wi-chart">
    <div style="overflow-x: auto; overflow-y: hidden; padding-bottom: 20px;">
        <div style="min-width: 2000px; height: {{ $this->getMaxHeight() }};">
            <canvas
                x-data="{
                    chart: null,
                    
                    init() {
                        // Wait for Chart.js to be available
                        const initChart = () => {
                            if (typeof Chart !== 'undefined') {
                                this.chart = new Chart($el, {
                                    type: '{{ $this->getType() }}',
                                    data: {{ \Illuminate\Support\Js::from($this->getData()) }},
                                    options: {{ \Illuminate\Support\Js::from($this->getOptions()) }},
                                });
                                
                                $wire.on('updateChartData', ({ data }) => {
                                    this.chart.data = data;
                                    this.chart.update();
                                });
                            } else {
                                // Retry after a short delay
                                setTimeout(initChart, 100);
                            }
                        };
                        
                        initChart();
                    }
                }"
                x-init="init()"
                wire:ignore
            ></canvas>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
</x-filament-widgets::widget>
