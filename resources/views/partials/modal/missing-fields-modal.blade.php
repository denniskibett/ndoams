{{-- resources/views/partials/modals/missing-fields-modal.blade.php --}}

<div x-show="showMissingModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition.opacity
     style="display: none;">
    
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Missing Required Fields
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-3">
                                The following required fields are missing. You can still submit, but the record will be marked as having errors.
                            </p>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 max-h-40 overflow-y-auto">
                                <ul class="list-disc list-inside space-y-1">
                                    <template x-for="field in missingFieldsList" :key="field">
                                        <li class="text-sm text-yellow-800" x-text="field"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Reason for incomplete submission (Optional)
                            </label>
                            <textarea x-model="missingReason" 
                                      rows="3"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                      placeholder="e.g., Text is illegible, information not provided in document, etc."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        @click="submitWithMissing"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Submit Anyway
                </button>
                <button type="button" 
                        @click="cancelSubmit"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Complete Form
                </button>
            </div>
        </div>
    </div>
</div>