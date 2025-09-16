<x-filament-widgets::widget class="fi-wi-chart">
    <div style="overflow-x: auto; overflow-y: hidden; padding-bottom: 20px;">
        <div style="min-width: 1200px; height: {{ $this->getMaxHeight() }};" id="chart-container-{{ $this->getId() }}">
            <canvas id="chart-{{ $this->getId() }}"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartContainer = document.getElementById('chart-container-{{ $this->getId() }}');
            const canvas = document.getElementById('chart-{{ $this->getId() }}');
            if (!canvas) return;

            const chart = new Chart(canvas, {
                type: '{{ $this->getType() }}',
                data: {{ Illuminate\Support\Js::from($this->getData()) }},
                options: {{ Illuminate\Support\Js::from($this->getOptions()) }},
            });

            @this.on('updateChartData', ({ data }) => {
                chart.data = data;
                chart.update();
            });
        });
    </script>
</x-filament-widgets::widget>
