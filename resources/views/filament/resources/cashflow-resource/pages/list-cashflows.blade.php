<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Widgets -->
        <div class="grid gap-6">
            @foreach($this->getHeaderWidgets() as $widget)
                @livewire($widget)
            @endforeach
        </div>

        <!-- Custom Cashflow Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Project Cashflow Projection</h3>
                <p class="text-sm text-gray-600 mt-1">Ongoing projects with weekly installments</p>
            </div>

            <!-- Month Headers -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <!-- Month Header Row -->
                    <thead class="bg-gray-50">
                        <tr>
                            <th rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                                Project
                            </th>
                            <th rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                                Status
                            </th>
                            @php
                                $startDate = now()->startOfWeek();
                                $months = [];
                                for ($i = 0; $i < 12; $i++) {
                                    $weekStart = $startDate->copy()->addWeeks($i);
                                    $monthKey = $weekStart->format('M-Y');
                                    if (!isset($months[$monthKey])) {
                                        $months[$monthKey] = 0;
                                    }
                                    $months[$monthKey]++;
                                }
                            @endphp
                            @foreach($months as $monthName => $weekCount)
                                <th colspan="{{ $weekCount }}" class="px-3 py-3 text-center text-sm font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300 bg-blue-50">
                                    {{ $monthName }}
                                </th>
                            @endforeach
                        </tr>
                        <!-- Week Header Row -->
                        <tr class="bg-gray-100">
                            @for($i = 0; $i < 12; $i++)
                                @php
                                    $weekStart = $startDate->copy()->addWeeks($i);
                                    $weekEnd = $weekStart->copy()->endOfWeek();
                                    $weekNumber = 'W' . (($i % 4) + 1);
                                    $expectedCash = \App\Filament\Resources\CashflowResource::calculateExpectedCashForWeek($weekStart, $weekEnd);
                                @endphp
                                <th class="px-2 py-2 text-center border-r border-gray-300">
                                    <div class="text-xs font-medium text-gray-700 mb-1">{{ $weekNumber }}</div>
                                    <div class="text-xs px-2 py-1 rounded {{ $expectedCash >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ number_format($expectedCash, 0) }}
                                    </div>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    
                    <!-- Table Body -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($this->getTableRecords() as $project)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-200">
                                    <div class="text-sm font-medium text-gray-900">{{ $project->title }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-200">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $project->status === 'active' ? 'bg-green-100 text-green-800' : 
                                           ($project->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($project->status) }}
                                    </span>
                                </td>
                                @for($i = 0; $i < 12; $i++)
                                    @php
                                        $weekStart = $startDate->copy()->addWeeks($i);
                                        $weekEnd = $weekStart->copy()->endOfWeek();
                                        $transactions = $project->transactions()
                                            ->where('status', 'pending')
                                            ->whereBetween('due_date', [$weekStart, $weekEnd])
                                            ->get();
                                    @endphp
                                    <td class="px-2 py-4 text-center border-r border-gray-200 min-h-[80px]">
                                        @if($transactions->isEmpty())
                                            <div class="text-gray-400 text-xs">-</div>
                                        @else
                                            @foreach($transactions as $transaction)
                                                <div class="mb-1 p-1 rounded text-xs cursor-help 
                                                    {{ $transaction->financial_type === 'revenue' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' }}"
                                                    title="{{ ucfirst($transaction->financial_type) }} - Due: {{ \Carbon\Carbon::parse($transaction->due_date)->format('M j') }}">
                                                    {{ number_format($transaction->amount, 0) }}
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
