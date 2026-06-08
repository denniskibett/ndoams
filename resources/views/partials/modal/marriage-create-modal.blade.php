{{-- resources/views/partials/modals/marriage-create-modal.blade.php --}}
<div x-show="isCreateModalOpen" class="fixed inset-0 flex items-center justify-center p-4 z-99999" x-cloak>
    <div class="fixed inset-0 bg-gray-500/50 dark:bg-gray-900/80 backdrop-blur-md" @click="isCreateModalOpen = false">
    </div>
    <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Marriage Record</h3>
            <button @click="isCreateModalOpen = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div x-show="modalError" class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/50 dark:text-red-300" x-text="modalError"></div>

        <form @submit.prevent="submitCreateForm()" class="space-y-4">
            <!-- Basic Info -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Certificate Serial <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formData.certificate_serial" required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marriage Date <span class="text-red-500">*</span></label>
                    <input type="date" x-model="formData.marriage_date" required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marriage Type <span class="text-red-500">*</span></label>
                    <select x-model="formData.marriage_type_id" required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Type</option>
                        @foreach($categories->where('type', 'marriage_type') as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Venue <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formData.venue" required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">County <span class="text-red-500">*</span></label>
                    <select x-model="formData.county" required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select County</option>
                        @foreach($counties ?? [] as $county)
                            <option value="{{ $county }}">{{ $county }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Constituency</label>
                    <select x-model="formData.sub_county"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Constituency</option>
                        @foreach($constituencies ?? [] as $constituency)
                            <option value="{{ $constituency }}">{{ $constituency }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Husband -->
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <h4 class="mb-3 font-medium text-gray-900 dark:text-white">Husband Details</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.husband_name" required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Age</label>
                        <input type="number" x-model="formData.husband_age" min="18" max="120"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Wife -->
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <h4 class="mb-3 font-medium text-gray-900 dark:text-white">Wife Details</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.wife_name" required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Age</label>
                        <input type="number" x-model="formData.wife_age" min="18" max="120"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Witnesses -->
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <h4 class="mb-3 font-medium text-gray-900 dark:text-white">Witnesses</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Witness 1 <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.witness1_name" required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Witness 2 <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.witness2_name" required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <button type="button" @click="isCreateModalOpen = false"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" :disabled="isLoading"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!isLoading">Create Marriage</span>
                    <span x-show="isLoading">Creating...</span>
                </button>
            </div>
        </form>
    </div>
</div>