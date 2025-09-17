<div class="fi-wi-widget">
    <div class="fi-wi-widget-content">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Ongoing Projects - Weekly Breakdown</h3>
                <p class="text-sm text-gray-600 mt-1">Projects with pending installments organized by week</p>
            </div>

            <!-- Weekly Headers -->
            <div class="overflow-x-auto">
                <div class="min-w-full">
                    <!-- Week Headers -->
                    <div class="grid grid-cols-12 gap-1 p-4 bg-gray-50 border-b border-gray-200">
                        @foreach($weeks as $index => $week)
                            <div class="text-center">
                                <div class="text-xs font-medium text-gray-700 mb-1">
                                    {{ $week['week_label'] }}
                                </div>
                                <div class="text-xs px-2 py-1 rounded-md {{ $week['expected_cash'] >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    ${{ number_format($week['expected_cash'], 0) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Projects Grid -->
                    <div class="divide-y divide-gray-200">
                        @forelse($projects as $project)
                            <div class="grid grid-cols-12 gap-1 p-4 hover:bg-gray-50">
                                <!-- Project Info -->
                                <div class="col-span-12 mb-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $project['title'] }}</h4>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $project['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($project['status']) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Weekly Installments -->
                                @for($weekIndex = 0; $weekIndex < 12; $weekIndex++)
                                    <div class="min-h-[60px] border-l border-gray-200 pl-2">
                                        @if(isset($project['installments'][$weekIndex]))
                                            @foreach($project['installments'][$weekIndex] as $installment)
                                                <div class="mb-1 p-1 rounded text-xs {{ $installment['financial_type'] === 'revenue' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                                    <div class="font-medium {{ $installment['financial_type'] === 'revenue' ? 'text-green-800' : 'text-red-800' }}">
                                                        ${{ number_format($installment['amount'], 0) }}
                                                    </div>
                                                    <div class="text-gray-600 truncate">
                                                        {{ $installment['description'] }}
                                                    </div>
                                                    <div class="text-gray-500">
                                                        {{ \Carbon\Carbon::parse($installment['due_date'])->format('M j') }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-500">
                                <div class="text-lg font-medium mb-2">No ongoing projects</div>
                                <div class="text-sm">There are no projects with pending installments in the next 12 weeks.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .grid-cols-12 > div:first-child {
        grid-column: span 12;
    }
    
    .grid-cols-12 > div:not(:first-child) {
        grid-column: span 1;
    }
    
    @media (max-width: 768px) {
        .grid-cols-12 {
            display: block;
        }
        
        .grid-cols-12 > div {
            display: block !important;
            grid-column: unset !important;
            margin-bottom: 0.5rem;
        }
    }
</style>
