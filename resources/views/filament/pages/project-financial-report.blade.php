<x-filament-panels::page>
    <div class="p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                <x-filament::input type="text" wire:model.live.debounce.300ms="search" placeholder="Search projects..." />
            </div>
                    <div>
                <x-filament::input.select wire:model.live="startMonth">
                    @foreach ($availableMonths as $month)
                        <option value="{{ $month }}">{{ date('M Y', strtotime($month)) }}</option>
                    @endforeach
                </x-filament::input.select>
            </div>
            <div>
                <x-filament::input.select wire:model.live="endMonth">
                    @foreach ($availableMonths as $month)
                        <option value="{{ $month }}">{{ date('M Y', strtotime($month)) }}</option>
                    @endforeach
                </x-filament::input.select>
            </div>
            <div>
                <x-filament::input.select wire:model.live="perPage">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="{{ $this->projects->total() }}">All</option>
                </x-filament::input.select>
            </div>
            </div>
    </div>

    <div class="mt-4 overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                                        <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-0 bg-gray-50 dark:bg-gray-700 z-10">
                        <button wire:click="sortBy('created_at')" class="flex items-center">
                            Project
                            @if ($sortDirection === 'asc')
                                <x-heroicon-s-chevron-up class="w-4 h-4 ml-1" />
                            @else
                                <x-heroicon-s-chevron-down class="w-4 h-4 ml-1" />
                            @endif
                        </button>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Metric</th>
                    <th
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Total</th>
                    @foreach ($allMonths as $month)
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                            {{ date('M Y', strtotime($month)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($this->projects as $project)
                    @php
                        $projectData = $this->financialSummary[$project->key] ?? null;
                        $metrics = [
                            'evaluation_asset' => 'Evaluation Asset',
                            'revenue' => 'Revenue',
                            'expense' => 'Expense',
                            'profit_operation' => 'Profit Operation',
                            'profit_asset' => 'Profit Asset',
                            'profit' => 'Total Profit',
                        ];
                        $rowspan = count($metrics);
                    @endphp
                    @if ($projectData)
                        @foreach ($metrics as $key => $label)
                            <tr
                                class="{{ $loop->first ? 'border-t-2 border-gray-300 dark:border-gray-600' : '' }} bg-white dark:bg-gray-800">
                                @if ($loop->first)
                                    <td rowspan="{{ $rowspan }}"
                                        class="px-6 py-4 align-top whitespace-nowrap border-r dark:border-gray-600 sticky left-0 bg-white dark:bg-gray-800 z-10">
                                        <div class="font-bold text-lg">{{ $projectData['title'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $projectData['status'] }}</div>
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">{{ $label }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-bold">
                                    ${{ number_format($projectData['totals'][$key], 2) }}</td>
                                @foreach ($allMonths as $month)
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        ${{ number_format($projectData['months'][$month][$key] ?? 0, 2) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ 3 + count($allMonths) }}" class="px-6 py-12 whitespace-nowrap">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" aria-hidden="true">
                                    <path vector-effect="non-scaling-stroke" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2"
                                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No projects found
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">There are no projects to
                                    display.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4">
        {{ $this->projects->links() }}
    </div>
</x-filament-panels::page>
