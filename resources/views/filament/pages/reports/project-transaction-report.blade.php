<x-filament::page>
    @include('filament.pages.reports.help.project-transaction-report-help')

    <x-filament::card>
        {{ $this->form }}
    </x-filament::card>

    @if ($reportData)
        <div class="mt-6 space-y-6">
            <!-- Summary Stats -->
            <x-filament::card>
                <h2 class="text-xl font-bold mb-4">Summary</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-success-50 p-4 rounded-lg border border-success-200">
                        <div class="text-sm text-gray-600">Total Revenue</div>
                        <div class="text-xl font-bold text-success-700">
                            {{ number_format($reportData['summary']['total_revenue'], 2) }}</div>
                    </div>
                    <div class="bg-danger-50 p-4 rounded-lg border border-danger-200">
                        <div class="text-sm text-gray-600">Total Expenses</div>
                        <div class="text-xl font-bold text-danger-700">
                            {{ number_format($reportData['summary']['total_expenses'], 2) }}</div>
                    </div>
                    <div class="bg-primary-50 p-4 rounded-lg border border-primary-200">
                        <div class="text-sm text-gray-600">Net Cash Flow</div>
                        <div
                            class="text-xl font-bold {{ $reportData['summary']['net_cash_flow'] >= 0 ? 'text-success-700' : 'text-danger-700' }}">
                            {{ number_format($reportData['summary']['net_cash_flow'], 2) }}
                        </div>
                    </div>
                </div>
            </x-filament::card>

            <!-- Monthly Data Table -->
            <x-filament::card>
                <h2 class="text-xl font-bold mb-4">Monthly Transaction Data</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Month</th>
                                <th scope="col" class="px-6 py-3 text-right">Total Revenue</th>
                                <th scope="col" class="px-6 py-3 text-right">Total Expenses</th>
                                <th scope="col" class="px-6 py-3 text-right">Net Cash Flow</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData['monthly_data'] as $data)
                                <tr class="bg-white border-b">
                                    <td class="px-6 py-4">{{ $data['month_name'] }}</td>
                                    <td class="px-6 py-4 text-right text-success-600">
                                        {{ number_format($data['total_revenue'], 2) }}</td>
                                    <td class="px-6 py-4 text-right text-danger-600">
                                        {{ number_format($data['total_expenses'], 2) }}</td>
                                    <td
                                        class="px-6 py-4 text-right {{ $data['net_cash_flow'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                        {{ number_format($data['net_cash_flow'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::card>

            <!-- Category Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Revenue Categories -->
                <x-filament::card>
                    <h2 class="text-xl font-bold mb-4">Revenue by Category</h2>
                    <div class="mb-4">
                        <canvas id="revenueChart" height="250"></canvas>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Category</th>
                                    <th scope="col" class="px-6 py-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reportData['category_totals']['revenue_categories'] as $category => $amount)
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4">{{ ucfirst($category) }}</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::card>

                <!-- Expense Categories -->
                <x-filament::card>
                    <h2 class="text-xl font-bold mb-4">Expenses by Category</h2>
                    <div class="mb-4">
                        <canvas id="expenseChart" height="250"></canvas>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Category</th>
                                    <th scope="col" class="px-6 py-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reportData['category_totals']['expense_categories'] as $category => $amount)
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4">{{ ucfirst($category) }}</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::card>
            </div>

            <!-- Monthly Trend Chart -->
            <x-filament::card>
                <h2 class="text-xl font-bold mb-4">Monthly Transaction Trends</h2>
                <div class="w-full h-80">
                    <canvas id="transactionChart"></canvas>
                </div>
            </x-filament::card>
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('livewire:load', function() {
                    Livewire.hook('message.processed', (message, component) => {
                        if (document.getElementById('transactionChart')) {
                            renderCharts();
                        }
                    });

                    function renderCharts() {
                        // Monthly trend chart
                        const trendCtx = document.getElementById('transactionChart').getContext('2d');
                        const monthlyData = @json($reportData['monthly_data']);
                        const labels = monthlyData.map(item => item.month_name);
                        const revenue = monthlyData.map(item => item.total_revenue);
                        const expenses = monthlyData.map(item => item.total_expenses);
                        const netCashFlow = monthlyData.map(item => item.net_cash_flow);

                        new Chart(trendCtx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                        label: 'Revenue',
                                        data: revenue,
                                        backgroundColor: 'rgba(16, 185, 129, 0.5)',
                                        borderColor: 'rgb(16, 185, 129)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Expenses',
                                        data: expenses,
                                        backgroundColor: 'rgba(239, 68, 68, 0.5)',
                                        borderColor: 'rgb(239, 68, 68)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Net Cash Flow',
                                        data: netCashFlow,
                                        type: 'line',
                                        fill: false,
                                        borderColor: 'rgb(59, 130, 246)',
                                        tension: 0.1
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });

                        // Revenue categories pie chart
                        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                        const revenueCategories = @json($reportData['category_totals']['revenue_categories']);

                        new Chart(revenueCtx, {
                            type: 'pie',
                            data: {
                                labels: Object.keys(revenueCategories).map(key => key.charAt(0).toUpperCase() + key
                                    .slice(1)),
                                datasets: [{
                                    data: Object.values(revenueCategories),
                                    backgroundColor: [
                                        '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                                        '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                    }
                                }
                            }
                        });

                        // Expense categories pie chart
                        const expenseCtx = document.getElementById('expenseChart').getContext('2d');
                        const expenseCategories = @json($reportData['category_totals']['expense_categories']);

                        new Chart(expenseCtx, {
                            type: 'pie',
                            data: {
                                labels: Object.keys(expenseCategories).map(key => key.charAt(0).toUpperCase() + key
                                    .slice(1)),
                                datasets: [{
                                    data: Object.values(expenseCategories),
                                    backgroundColor: [
                                        '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                                        '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                    }
                                }
                            }
                        });
                    }

                    if (document.getElementById('transactionChart')) {
                        renderCharts();
                    }
                });
            </script>
        @endpush
    @endif
</x-filament::page>
