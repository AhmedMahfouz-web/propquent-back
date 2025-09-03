<x-filament-panels::page>
    <style>
        .correction-grid {
            max-height: 70vh;
            overflow-y: auto;
        }

        .sticky-column {
            position: sticky;
            background: inherit;
            z-index: 10;
        }

        .correction-input {
            font-size: 0.875rem;
            padding: 0.375rem 0.5rem;
        }

        .correction-input:focus {
            background-color: #fef3c7;
            border-color: #f59e0b;
        }

        .dark .correction-input:focus {
            background-color: #451a03;
            border-color: #d97706;
        }
    </style>
    <div class="space-y-6">

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-2">
            <x-filament::button wire:click="save" color="primary">
                Save All Changes
            </x-filament::button>
        </div>

        <!-- Value Correction Grid -->
        <div class="correction-grid overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-0 bg-gray-50 dark:bg-gray-700 z-10 min-w-[200px]">
                            Project
                        </th>
                        <th
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 sticky left-[200px] bg-gray-50 dark:bg-gray-700 z-10 min-w-[120px]">
                            Actions
                        </th>
                        @foreach ($availableMonths as $month)
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 w-36"
                                style="min-width: 120px !important">
                                {{ date('M Y', strtotime($month)) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach ($projects as $project)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td
                                class="px-4 py-4 whitespace-nowrap sticky left-0 bg-white dark:bg-gray-800 z-10 border-r border-gray-200 dark:border-gray-600">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $project->title }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $project->key }}
                                </div>
                            </td>
                            <td
                                class="px-4 py-4 whitespace-nowrap sticky left-[200px] bg-white dark:bg-gray-800 z-10 border-r border-gray-200 dark:border-gray-600">
                                <div class="flex space-x-1">
                                    <x-filament::button wire:click="copyFromPreviousMonth('{{ $project->key }}')"
                                        size="xs" color="gray" title="Copy values from previous months forward">
                                        Copy Forward
                                    </x-filament::button>
                                    <x-filament::button wire:click="clearProject('{{ $project->key }}')" size="xs"
                                        color="danger" title="Clear all values for this project">
                                        Clear
                                    </x-filament::button>
                                </div>
                            </td>
                            @foreach ($availableMonths as $month)
                                @php
                                    $key = $project->key . '_' . $month;
                                @endphp
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <x-filament::input type="number" step="0.01" min="0"
                                        wire:model.blur="corrections.{{ $key }}" placeholder="0.00"
                                        class="w-32 text-right correction-input mx-auto" />
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Save Button (Bottom) -->
        <div class="flex justify-end">
            <x-filament::button wire:click="save" color="primary" size="lg">
                Save All Changes
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
