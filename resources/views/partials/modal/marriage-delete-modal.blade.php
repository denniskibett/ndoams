{{-- resources/views/partials/modals/marriage-delete-modal.blade.php --}}
<div x-show="isDeleteModalOpen" class="fixed inset-0 flex items-center justify-center p-4 z-99999" x-cloak>
    <div class="fixed inset-0 bg-gray-500/50 dark:bg-gray-900/80 backdrop-blur-md" @click="isDeleteModalOpen = false"></div>
    <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Marriage Record</h3>
            <button @click="isDeleteModalOpen = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div x-show="modalError" class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/50 dark:text-red-300" x-text="modalError"></div>

        <div class="text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h4 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Are you sure?</h4>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                You are about to delete marriage record 
                <span class="font-semibold" x-text="selectedMarriage?.certificate_serial"></span>. 
                This action cannot be undone and will also delete all associated spouses, witnesses, and extension data.
            </p>
        </div>

        <div class="flex justify-end gap-3">
            <button @click="isDeleteModalOpen = false"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                Cancel
            </button>
            <button @click="submitDelete()" :disabled="isLoading"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">
                <span x-show="!isLoading">Delete</span>
                <span x-show="isLoading">Deleting...</span>
            </button>
        </div>
    </div>
</div>