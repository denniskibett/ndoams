{{-- resources/views/partials/modals/marriage-edit-modal.blade.php --}}
<div x-show="isEditModalOpen" class="fixed inset-0 flex items-center justify-center p-4 z-99999" x-cloak>
    <div class="fixed inset-0 bg-gray-500/50 dark:bg-gray-900/80 backdrop-blur-md" @click="isEditModalOpen = false"></div>
    <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Marriage Record</h3>
            <button @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div x-show="modalError" class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/50 dark:text-red-300" x-text="modalError"></div>

        <form @submit.prevent="submitEditForm()" class="space-y-4">
            <input type="hidden" x-model="formData.id">

            <!-- Basic Information Section -->
            <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Basic Information</h4>
                </div>
                
                <div class="p-4 space-y-4">
                    <!-- Certificate Serial -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Certificate Serial Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               x-model="formData.certificate_serial"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                               placeholder="Enter certificate serial number"
                               required>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Marriage Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Marriage Type <span class="text-red-500">*</span>
                            </label>
                            <select x-model="formData.marriage_type_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                                    required>
                                <option value="">Select Marriage Type</option>
                                @foreach($categories->where('type', 'marriage_type') as $category)
                                    <option value="{{ $category->id }}" 
                                            data-name="{{ strtolower($category->name) }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Marriage Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Marriage Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   x-model="formData.marriage_date"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                                   required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Registration Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Registration Date
                            </label>
                            <input type="date" 
                                   x-model="formData.reg_date"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800">
                        </div>

                        <!-- Venue -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Venue <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   x-model="formData.venue"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                   placeholder="Enter marriage venue"
                                   required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- County -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                County <span class="text-red-500">*</span>
                            </label>
                            <select x-model="formData.county"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                                    required>
                                <option value="">Select County</option>
                                @foreach($counties ?? [] as $county)
                                    <option value="{{ $county }}">{{ $county }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sub County -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Sub County
                            </label>
                            <select x-model="formData.sub_county"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 constituency-select">
                                <option value="">Select Sub County</option>
                                @foreach($constituencies ?? [] as $constituency)
                                    <option value="{{ $constituency }}">{{ $constituency }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Year
                            </label>
                            <input type="number" 
                                   x-model="formData.year"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800"
                                   min="1900"
                                   max="2100">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spouses Information Section -->
            <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Spouses Information</h4>
                </div>
                
                <div class="p-4 space-y-6">
                    <!-- Husband Section -->
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Husband Details</h5>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                      x-model="formData.husband_name"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                      placeholder="Husband's full name"
                                      required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Age
                                </label>
                                <input type="number" 
                                      x-model="formData.husband_age"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                                      placeholder="Age"
                                      min="18"
                                      max="120">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Occupation
                                </label>
                                <input type="text" 
                                      x-model="formData.husband_occupation"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                      placeholder="Occupation">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Residence/Ward
                                </label>
                                <select x-model="formData.husband_residence"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select">
                                    <option value="">Select or type ward name</option>
                                    @foreach($wards ?? [] as $ward)
                                        <option value="{{ $ward }}">{{ $ward }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Husband's Father Information -->
                        <div class="mt-4 pt-4 border-t">
                            <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Father's Information</h6>
                            
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Father's Name
                                    </label>
                                    <input type="text" 
                                          x-model="formData.husband_father_name"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                          placeholder="Father's full name">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Father's Occupation
                                    </label>
                                    <input type="text" 
                                          x-model="formData.husband_father_occupation"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                          placeholder="Father's occupation">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Father's Residence
                                    </label>
                                    <select x-model="formData.husband_father_residence"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select">
                                        <option value="">Select or type ward name</option>
                                        @foreach($wards ?? [] as $ward)
                                            <option value="{{ $ward }}">{{ $ward }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Husband's Mother Information -->
                        <div class="mt-4">
                            <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Mother's Information</h6>
                            
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Mother's Name
                                    </label>
                                    <input type="text" 
                                          x-model="formData.husband_mother_name"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                          placeholder="Mother's full name">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Mother's Occupation
                                    </label>
                                    <input type="text" 
                                          x-model="formData.husband_mother_occupation"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                          placeholder="Mother's occupation">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Mother's Residence
                                    </label>
                                    <select x-model="formData.husband_mother_residence"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select">
                                        <option value="">Select or type ward name</option>
                                        @foreach($wards ?? [] as $ward)
                                            <option value="{{ $ward }}">{{ $ward }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wife Section -->
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Wife Details</h5>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                      x-model="formData.wife_name"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                      placeholder="Wife's full name"
                                      required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Age
                                </label>
                                <input type="number" 
                                      x-model="formData.wife_age"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                                      placeholder="Age"
                                      min="18"
                                      max="120">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Occupation
                                </label>
                                <input type="text" 
                                      x-model="formData.wife_occupation"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                      placeholder="Occupation">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Residence/Ward
                                </label>
                                <select x-model="formData.wife_residence"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select">
                                    <option value="">Select or type ward name</option>
                                    @foreach($wards ?? [] as $ward)
                                        <option value="{{ $ward }}">{{ $ward }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Wife's Father Information -->
                        <div class="mt-4 pt-4 border-t">
                            <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Father's Information</h6>
                            
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Father's Name
                                    </label>
                                    <input type="text" 
                                          x-model="formData.wife_father_name"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                          placeholder="Father's full name">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Father's Occupation
                                    </label>
                                    <input type="text" 
                                          x-model="formData.wife_father_occupation"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                          placeholder="Father's occupation">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Father's Residence
                                    </label>
                                    <select x-model="formData.wife_father_residence"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select">
                                        <option value="">Select or type ward name</option>
                                        @foreach($wards ?? [] as $ward)
                                            <option value="{{ $ward }}">{{ $ward }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Wife's Mother Information -->
                        <div class="mt-4">
                            <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Mother's Information</h6>
                            
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Mother's Name
                                    </label>
                                    <input type="text" 
                                          x-model="formData.wife_mother_name"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                          placeholder="Mother's full name">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Mother's Occupation
                                    </label>
                                    <input type="text" 
                                          x-model="formData.wife_mother_occupation"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                          placeholder="Mother's occupation">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Mother's Residence
                                    </label>
                                    <select x-model="formData.wife_mother_residence"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select">
                                        <option value="">Select or type ward name</option>
                                        @foreach($wards ?? [] as $ward)
                                            <option value="{{ $ward }}">{{ $ward }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Witnesses Information Section -->
            <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Witnesses Information</h4>
                </div>
                
                <div class="p-4 space-y-4">
                    <!-- Witness 1 -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Witness 1 Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                  x-model="formData.witness1_name"
                                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                  placeholder="Witness 1 full name"
                                  required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Witness 1 Side
                            </label>
                            <select x-model="formData.witness1_side"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800">
                                <option value="">Select Side</option>
                                <option value="husband">Husband's Side</option>
                                <option value="wife">Wife's Side</option>
                                <option value="both">Both Sides</option>
                            </select>
                        </div>
                    </div>

                    <!-- Witness 2 -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Witness 2 Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                  x-model="formData.witness2_name"
                                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                  placeholder="Witness 2 full name"
                                  required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Witness 2 Side
                            </label>
                            <select x-model="formData.witness2_side"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800">
                                <option value="">Select Side</option>
                                <option value="husband">Husband's Side</option>
                                <option value="wife">Wife's Side</option>
                                <option value="both">Both Sides</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Marriage Type Extensions Section (Dynamic) -->
            <div id="marriageExtensionsSection" class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50" 
                 x-show="formData.marriage_type_id" x-cloak>
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Additional Information</h4>
                </div>
                
                <div class="p-4 space-y-4">
                    <!-- Christian Marriage Fields -->
                    <div x-show="formData.marriage_type_id && marriageTypes.find(t => t.id == formData.marriage_type_id)?.name?.toLowerCase() === 'christian'">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Christian marriage requires church and pastor information</p>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Church/Organization
                                </label>
                                <input type="text" 
                                       x-model="formData.church_org"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                       placeholder="Enter church or religious organization name">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Pastor/Officiant Name
                                </label>
                                <input type="text" 
                                       x-model="formData.pastor_name"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                       placeholder="Enter pastor or marriage officiant name">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Church Entry Number
                            </label>
                            <input type="text" 
                                   x-model="formData.entry_no"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                   placeholder="Church registry entry number (optional)">
                        </div>
                    </div>

                    <!-- Muslim Marriage Fields -->
                    <div x-show="formData.marriage_type_id && marriageTypes.find(t => t.id == formData.marriage_type_id)?.name?.toLowerCase() === 'muslim'">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Islamic marriage requires Mahr information and Muslim officer details</p>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Mahr Agreed Amount
                                </label>
                                <input type="text" 
                                       x-model="formData.mahr_agreed"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                                       placeholder="Enter agreed Mahr amount">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Mahr Paid Amount
                                </label>
                                <input type="text" 
                                       x-model="formData.mahr_paid"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                                       placeholder="Enter paid Mahr amount">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Mahr Deferred Amount
                                </label>
                                <input type="text" 
                                       x-model="formData.mahr_deferred"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                                       placeholder="Enter deferred Mahr amount">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Muslim Marriage Officer
                                </label>
                                <input type="text" 
                                       x-model="formData.muslim_officer"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                       placeholder="Enter Muslim marriage officer name">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Additional Gifts
                                </label>
                                <input type="text" 
                                       x-model="formData.gifts"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                       placeholder="Enter any additional gifts">
                            </div>
                        </div>
                    </div>

                    <!-- Hindu Marriage Fields -->
                    <div x-show="formData.marriage_type_id && marriageTypes.find(t => t.id == formData.marriage_type_id)?.name?.toLowerCase() === 'hindu'">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Hindu marriage requires temple and dowry information</p>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Temple Name
                                </label>
                                <input type="text" 
                                       x-model="formData.temple"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                       placeholder="Enter temple name">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Dowry Details
                                </label>
                                <input type="text" 
                                       x-model="formData.dowry"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                       placeholder="Enter dowry details">
                            </div>
                        </div>
                    </div>

                    <!-- Civil Marriage Fields -->
                    <div x-show="formData.marriage_type_id && marriageTypes.find(t => t.id == formData.marriage_type_id)?.name?.toLowerCase() === 'civil'">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Civil marriage requires basic registration information</p>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Marriage Officer
                                </label>
                                <input type="text" 
                                       x-model="formData.marriage_officer"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                       placeholder="Enter marriage officer name">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Registration Entry Number
                                </label>
                                <input type="text" 
                                       x-model="formData.entry_no"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                       placeholder="Registration entry number">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end w-full gap-3 mt-6">
                <button
                    @click="isEditModalOpen = false"
                    type="button"
                    class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
                    :disabled="isLoading"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-blue-600 shadow-theme-xs hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
                    :disabled="isLoading"
                >
                    <template x-if="isLoading">
                        <svg class="animate-spin h-5 w-5 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="isLoading ? 'Updating...' : 'Update Marriage'"></span>
                </button>
            </div>
        </form>
    </div>
</div>