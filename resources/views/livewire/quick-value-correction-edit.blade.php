<div class="bg-green-200 border-2 border-green-500 p-2 rounded">
    <div class="text-xs text-green-800 mb-1">LIVEWIRE COMPONENT LOADED!</div>
    <div class="text-xs text-green-800 mb-1">Project: {{ $projectKey ?? 'N/A' }}</div>
    <div class="text-xs text-green-800 mb-1">Month: {{ $month ?? 'N/A' }}</div>
    <div class="text-xs text-green-800 mb-1">Amount: ${{ number_format($correction_amount ?? 0, 2) }}</div>
    <button wire:click="openModal" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">
        EDIT
    </button>
</div>

<!-- Modal -->
@if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Edit Value Correction
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $projectTitle }} - {{ date('M Y', strtotime($month)) }}
                                </p>
                            </div>
                            <div class="mt-4">
                                <form wire:submit="save">
                                    {{ $this->form }}
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse dark:bg-gray-700">
                    <button wire:click="save" type="button"
                        class="w-full  inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed bg-gray-500 bg-opacity-75"
                        wire:loading.attr="disabled" wire:target="save" style="background-color: #60a5fa;">
                        <span wire:loading.remove wire:target="save">Save</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                    <button wire:click="closeModal" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
