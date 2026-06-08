{{-- resources/views/partials/form/full-entry-form.blade.php --}}

<div x-data="fullEntryForm()" x-init="init()" x-cloak>
    <div class="space-y-6">
        <!-- Form Completion Bar -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Form Completion</span>
                <span x-text="completionPercentage + '%'" class="text-sm font-semibold text-primary-600"></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="h-2 rounded-full transition-all duration-300" 
                    :style="'width: ' + completionPercentage + '%; background-color: ' + getCompletionColor()"></div>
            </div>
        </div>

        <!-- Validation Bypass Toggle -->
        <div class="mb-5 p-4 rounded-lg border-2 transition-all"
            :class="skipValidation ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50'">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="radio" 
                            id="full_has_errors_true" 
                            x-model="skipValidation" 
                            :value="1"
                            class="w-5 h-5 text-red-600 focus:ring-red-500">
                        <label for="full_has_errors_true" class="ml-2 text-sm font-medium text-red-700">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Has Errors / Bypass Validation
                        </label>
                    </div>
                    <div class="relative ml-6">
                        <input type="radio" 
                            id="full_has_errors_false" 
                            x-model="skipValidation" 
                            :value="0"
                            class="w-5 h-5 text-green-600 focus:ring-green-500">
                        <label for="full_has_errors_false" class="ml-2 text-sm font-medium text-green-700">
                            <i class="fas fa-check-circle mr-1"></i> Complete / Validate All Fields
                        </label>
                    </div>
                </div>
                <div x-show="skipValidation" class="text-xs text-red-600">
                    <i class="fas fa-info-circle mr-1"></i> Validation bypassed - record will be marked "Under Review"
                </div>
                <div x-show="!skipValidation" class="text-xs text-green-600">
                    <i class="fas fa-info-circle mr-1"></i> All required fields will be validated
                </div>
            </div>
        </div>

        <!-- Debug Panel (hidden by default) -->
        <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200" x-show="showDebugPanel" x-cloak>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-700">Debug Information</h3>
                <button @click="showDebugPanel = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="text-xs font-mono bg-white p-3 rounded border border-gray-200 max-h-96 overflow-auto">
                <pre x-text="debugOutput"></pre>
            </div>
        </div>

        <form action="{{ route('pdf.full-create-from-page') }}" method="POST" id="fullEntryForm" @submit.prevent="submitForm">
            @csrf
            
            <input type="hidden" name="skip_validation" x-model="skipValidation">
            
            <!-- Hidden Fields -->
            <input type="hidden" name="pdf_page_id" value="{{ $pdfPage->id }}">
            <input type="hidden" name="pdf_id" value="{{ $pdfUpload->id }}">
            <input type="hidden" name="year" value="{{ $constants['year'] }}">
            <input type="hidden" name="month" value="{{ $constants['month'] }}">
            <input type="hidden" name="county" value="{{ $constants['county_name'] }}">
            <input type="hidden" name="marriage_type_id" value="{{ $pdfUpload->marriage_type_id }}">
            <input type="hidden" name="created_by" value="{{ Auth::id() }}">
            <input type="hidden" name="verified_by" value="{{ Auth::id() }}">

            <!-- Basic Information Section -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-4">
                <div 
                    @click="toggleSection('basic')" 
                    class="flex cursor-pointer items-center justify-between px-5 py-4 sm:px-6 sm:py-5"
                >
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Basic Information
                        </h3>
                        <span class="completion-badge px-2 py-1 rounded-full text-xs font-medium" :class="getSectionBadgeClass('basic')" x-text="sectionCompletion.basic + '%'"></span>
                    </div>
                    <button :class="openSection === 'basic' ? 'text-primary' : 'text-gray-400'">
                        <svg x-show="openSection !== 'basic'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg x-show="openSection === 'basic'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 12H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <div 
                    x-show="openSection === 'basic'" 
                    class="border-t border-gray-200 px-5 py-4 sm:px-6 sm:py-5 dark:border-gray-800"
                >
                    <!-- Marriage Type (Locked) -->
                    <div class="bg-gray-50 p-3 rounded-lg mb-4">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                            Marriage Type (Locked)
                        </label>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $pdfUpload->marriageType->name ?? 'No Type' }}
                        </p>
                    </div>

                    <!-- Certificate Serial & License No Grid -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Cert No <span class="text-red-500" x-show="!skipValidation">*</span>
                            </label>
                            <input type="text" 
                                name="certificate_serial" 
                                x-model="formData.certificateSerial"
                                @input="updateCompletion"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 uppercase-field"
                                placeholder="e.g., 0000001"
                                :required="!skipValidation">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Marriage Entry No
                            </label>
                            <input type="text" 
                                name="entry_no" 
                                x-model="extensionData.entry_no"
                                @input="updateCompletion"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800"
                                placeholder="e.g., 2024/001">
                        </div>
                    </div>

                    <!-- Venue & License No Grid -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Venue <span class="text-red-500" x-show="!skipValidation">*</span>
                            </label>
                            <input type="text" 
                                name="venue" 
                                x-model="formData.venue"
                                @input="updateCompletion"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 uppercase-field"
                                placeholder="Enter marriage venue"
                                :required="!skipValidation">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                License No
                            </label>
                            <input type="text" 
                                name="license_no" 
                                x-model="formData.licenseNo"
                                @input="updateCompletion"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 uppercase-field"
                                placeholder="e.g., 0001 or BMT/001 etc">
                        </div>
                    </div>

                    <!-- Date Fields with Flatpickr -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mb-4" 
                        x-data="{ 
                            initDatepickers() {
                                flatpickr(this.$refs.marriageDate, {
                                    dateFormat: 'Y-m-d',
                                    defaultDate: this.formData.marriageDate,
                                    maxDate: 'today',
                                    onChange: (selectedDates, dateStr) => {
                                        this.formData.marriageDate = dateStr;
                                        if (!this.formData.regDate) {
                                            this.formData.regDate = dateStr;
                                            if (this.$refs.regDate._flatpickr) {
                                                this.$refs.regDate._flatpickr.setDate(dateStr);
                                            }
                                        }
                                        this.updateCompletion();
                                    },
                                    position: 'auto'
                                });
                                flatpickr(this.$refs.regDate, {
                                    dateFormat: 'Y-m-d',
                                    defaultDate: this.formData.regDate,
                                    maxDate: 'today',
                                    onChange: (selectedDates, dateStr) => {
                                        this.formData.regDate = dateStr;
                                        this.updateCompletion();
                                    },
                                    position: 'auto'
                                });
                            }
                        }"
                        x-init="initDatepickers()">
                        
                        <!-- Marriage Date -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Marriage Date <span class="text-red-500" x-show="!skipValidation">*</span>
                            </label>
                            <input 
                                type="text"
                                x-ref="marriageDate"
                                x-model="formData.marriageDate"
                                name="marriage_date"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800"
                                placeholder="Select marriage date"
                                :required="!skipValidation"
                                readonly
                            >
                        </div>

                        <!-- Registration Date -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Registration Date
                            </label>
                            <input 
                                type="text"
                                x-ref="regDate"
                                x-model="formData.regDate"
                                name="reg_date"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800"
                                placeholder="Select registration date"
                                readonly
                            >
                        </div>
                    </div>
                    
                    <!-- Marriage Type Extensions -->
                    <div x-show="showExtensions" x-cloak class="mt-4 pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-800 mb-3" x-text="extensionTitle"></h4>
                        <p class="text-xs text-gray-500 mb-4" x-text="extensionDescription"></p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="(field, index) in extensionFields" :key="index">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700" :for="field.name">
                                        <span x-text="field.label"></span>
                                        <span x-show="field.required && !skipValidation" class="text-red-500">*</span>
                                    </label>
                                    <input :type="field.type" 
                                        :name="field.name" 
                                        :id="field.name"
                                        x-model="extensionData[field.name]"
                                        @input="updateCompletion"
                                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 uppercase-field"
                                        :placeholder="'Enter ' + field.label.toLowerCase()"
                                        :required="field.required && !skipValidation">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Information Section -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-4">
                <div 
                    @click="toggleSection('location')" 
                    class="flex cursor-pointer items-center justify-between px-5 py-4 sm:px-6 sm:py-5"
                >
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Location Information
                        </h3>
                        <span class="completion-badge px-2 py-1 rounded-full text-xs font-medium" :class="getSectionBadgeClass('location')" x-text="sectionCompletion.location + '%'"></span>
                    </div>
                    <button :class="openSection === 'location' ? 'text-primary' : 'text-gray-400'">
                        <svg x-show="openSection !== 'location'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg x-show="openSection === 'location'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 12H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <div 
                    x-show="openSection === 'location'" 
                    class="border-t border-gray-200 px-5 py-4 sm:px-6 sm:py-5 dark:border-gray-800"
                >
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- County (Locked) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                County <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-700 cursor-not-allowed" 
                                value="{{ $constants['county_name'] }}" 
                                readonly>
                            <input type="hidden" name="county" value="{{ $constants['county_name'] }}">
                        </div>

                        <!-- Constituency Dropdown -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Constituency <span class="text-red-500" x-show="!skipValidation">*</span>
                            </label>
                            <select name="sub_county" 
                                    id="full_sub_county"
                                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800"
                                    :required="!skipValidation"
                                    x-model="formData.selectedConstituency"
                                    @change="loadWards(); updateCompletion()">
                                <option value="">Select Constituency</option>
                                @foreach($subCounties as $subCounty)
                                    @if(!empty($subCounty->constituency))
                                        <option value="{{ $subCounty->constituency }}">
                                            {{ $subCounty->constituency }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Ward Selection -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Ward <span class="text-red-500" x-show="!skipValidation">*</span>
                        </label>
                        <select name="ward_id" 
                                id="full_ward"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800"
                                x-model="formData.ward_id"
                                @change="updateResidencesFromWard; updateCompletion()"
                                :required="!skipValidation">
                            <option value="">Select Ward</option>
                            <template x-for="ward in filteredWards" :key="ward.id">
                                <option :value="ward.id" x-text="ward.wards"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Spouses Information Section -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-4">
                <div 
                    @click="toggleSection('spouses')" 
                    class="flex cursor-pointer items-center justify-between px-5 py-4 sm:px-6 sm:py-5"
                >
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Spouses Information
                        </h3>
                        <span class="completion-badge px-2 py-1 rounded-full text-xs font-medium" :class="getSectionBadgeClass('spouses')" x-text="sectionCompletion.spouses + '%'"></span>
                    </div>
                    <button :class="openSection === 'spouses' ? 'text-primary' : 'text-gray-400'">
                        <svg x-show="openSection !== 'spouses'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg x-show="openSection === 'spouses'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 12H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <div 
                    x-show="openSection === 'spouses'" 
                    class="border-t border-gray-200 px-5 py-4 sm:px-6 sm:py-5 dark:border-gray-800"
                >
                    <!-- Husband Section -->
                    <div class="mb-6">
                        <div 
                            @click="toggleSubsection('husband')" 
                            class="flex cursor-pointer items-center justify-between px-4 py-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center gap-3">
                                <h4 class="text-sm font-medium text-gray-700">Husband Details</h4>
                                <span class="person-completion-badge px-2 py-0.5 rounded-full text-xs font-medium" :class="getPersonBadgeClass('husband')" x-text="personCompletion.husband + '%'"></span>
                            </div>
                            <button :class="openSubsection === 'husband' ? 'text-primary' : 'text-gray-400'">
                                <svg x-show="openSubsection !== 'husband'" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 5V15M15 10H5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <svg x-show="openSubsection === 'husband'" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 10H5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>

                        <div x-show="openSubsection === 'husband'" class="mt-4 space-y-4">
                            <!-- Husband Basic Info -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Full Name <span class="text-red-500" x-show="!skipValidation">*</span>
                                    </label>
                                    <input type="text" 
                                        name="husband_name" 
                                        x-model="spouses.husband.name"
                                        @input="formatNameCase('husband', 'name'); updateCompletion()"
                                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 name-field"
                                        placeholder="Husband's full name"
                                        :required="!skipValidation">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Age <span class="text-red-500" x-show="!skipValidation">*</span>
                                    </label>
                                    <input type="number" 
                                        name="husband_age" 
                                        x-model="spouses.husband.age"
                                        @input="updateCompletion"
                                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800"
                                        placeholder="Age"
                                        min="18"
                                        max="120"
                                        :required="!skipValidation">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Occupation
                                    </label>
                                    <input type="text" 
                                        name="husband_occupation" 
                                        x-model="spouses.husband.occupation"
                                        @input="updateCompletion"
                                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 uppercase-field"
                                        placeholder="Occupation"
                                        list="occupation-list">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Marital Status
                                    </label>
                                    <select name="husband_marital_status" 
                                            x-model="spouses.husband.maritalStatus"
                                            @change="updateCompletion"
                                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800">
                                        <option value="">Select Status</option>
                                        <option value="Bachelor">Bachelor</option>            
                                        <option value="Married">Married</option>
                                        <option value="Widowed">Widowed</option>
                                        <option value="Divorced">Divorced</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Residence
                                </label>
                                <input type="text" 
                                    name="husband_residence" 
                                    x-model="spouses.husband.residence"
                                    @input="updateCompletion"
                                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 uppercase-field"
                                    placeholder="Residence"
                                    :readonly="!residenceEditable.husband">
                            </div>

                            <!-- Husband's Father -->
                            <div class="border-t pt-4 mt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="text-sm font-medium text-gray-700">Father's Information</h5>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" 
                                            name="husband_father_deceased"
                                            x-model="spouses.husband.father.deceased"
                                            @change="toggleDeceased('husband', 'father')"
                                            class="rounded border-gray-300">
                                        <span class="text-sm text-gray-600">Deceased</span>
                                    </label>
                                </div>
                                
                                <div class="space-y-3">
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                        <div>
                                            <input type="text" 
                                                name="husband_father_name" 
                                                x-model="spouses.husband.father.name"
                                                @input="formatNameCase('husband', 'father', 'name'); updateCompletion()"
                                                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 name-field"
                                                placeholder="Father's full name"
                                                :readonly="spouses.husband.father.deceased"
                                                :class="spouses.husband.father.deceased ? 'bg-gray-100 cursor-not-allowed' : ''">
                                        </div>
                                        <div>
                                            <input type="text" 
                                                name="husband_father_occupation" 
                                                x-model="spouses.husband.father.occupation"
                                                @input="updateCompletion"
                                                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 uppercase-field"
                                                placeholder="Father's occupation"
                                                :readonly="spouses.husband.father.deceased"
                                                :class="spouses.husband.father.deceased ? 'bg-gray-100 cursor-not-allowed' : ''"
                                                list="occupation-list">
                                        </div>
                                    </div>
                                    <div>
                                        <input type="text" 
                                            name="husband_father_residence" 
                                            x-model="spouses.husband.father.residence"
                                            @input="updateCompletion"
                                            class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 uppercase-field"
                                            placeholder="Residence"
                                            :readonly="spouses.husband.father.deceased"
                                            :class="spouses.husband.father.deceased ? 'bg-gray-100 cursor-not-allowed' : ''">
                                    </div>
                                </div>
                            </div>

                            <!-- Husband's Mother -->
                            <div class="border-t pt-4 mt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="text-sm font-medium text-gray-700">Mother's Information</h5>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" 
                                            name="husband_mother_deceased"
                                            x-model="spouses.husband.mother.deceased"
                                            @change="toggleDeceased('husband', 'mother')"
                                            class="rounded border-gray-300">
                                        <span class="text-sm text-gray-600">Deceased</span>
                                    </label>
                                </div>
                                
                                <div class="space-y-3">
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                        <div>
                                            <input type="text" 
                                                name="husband_mother_name" 
                                                x-model="spouses.husband.mother.name"
                                                @input="formatNameCase('husband', 'mother', 'name'); updateCompletion()"
                                                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 name-field"
                                                placeholder="Mother's full name"
                                                :readonly="spouses.husband.mother.deceased"
                                                :class="spouses.husband.mother.deceased ? 'bg-gray-100 cursor-not-allowed' : ''">
                                        </div>
                                        <div>
                                            <input type="text" 
                                                name="husband_mother_occupation" 
                                                x-model="spouses.husband.mother.occupation"
                                                @input="updateCompletion"
                                                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 uppercase-field"
                                                placeholder="Mother's occupation"
                                                :readonly="spouses.husband.mother.deceased"
                                                :class="spouses.husband.mother.deceased ? 'bg-gray-100 cursor-not-allowed' : ''"
                                                list="occupation-list">
                                        </div>
                                    </div>
                                    <div>
                                        <input type="text" 
                                            name="husband_mother_residence" 
                                            x-model="spouses.husband.mother.residence"
                                            @input="updateCompletion"
                                            class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 uppercase-field"
                                            placeholder="Residence"
                                            :readonly="spouses.husband.mother.deceased"
                                            :class="spouses.husband.mother.deceased ? 'bg-gray-100 cursor-not-allowed' : ''">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wife Section -->
                    <div>
                        <div 
                            @click="toggleSubsection('wife')" 
                            class="flex cursor-pointer items-center justify-between px-4 py-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center gap-3">
                                <h4 class="text-sm font-medium text-gray-700">Wife Details</h4>
                                <span class="person-completion-badge px-2 py-0.5 rounded-full text-xs font-medium" :class="getPersonBadgeClass('wife')" x-text="personCompletion.wife + '%'"></span>
                            </div>
                            <button :class="openSubsection === 'wife' ? 'text-primary' : 'text-gray-400'">
                                <svg x-show="openSubsection !== 'wife'" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 5V15M15 10H5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <svg x-show="openSubsection === 'wife'" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 10H5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>

                        <div x-show="openSubsection === 'wife'" class="mt-4 space-y-4">
                            <!-- Wife Basic Info -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Full Name <span class="text-red-500" x-show="!skipValidation">*</span>
                                    </label>
                                    <input type="text" 
                                        name="wife_name" 
                                        x-model="spouses.wife.name"
                                        @input="formatNameCase('wife', 'name'); updateCompletion()"
                                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 name-field"
                                        placeholder="Wife's full name"
                                        :required="!skipValidation">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Age <span class="text-red-500" x-show="!skipValidation">*</span>
                                    </label>
                                    <input type="number" 
                                        name="wife_age" 
                                        x-model="spouses.wife.age"
                                        @input="updateCompletion"
                                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800"
                                        placeholder="Age"
                                        min="18"
                                        max="120"
                                        :required="!skipValidation">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Occupation
                                    </label>
                                    <input type="text" 
                                        name="wife_occupation" 
                                        x-model="spouses.wife.occupation"
                                        @input="updateCompletion"
                                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 uppercase-field"
                                        placeholder="Occupation"
                                        list="occupation-list">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Marital Status
                                    </label>
                                    <select name="wife_marital_status" 
                                            x-model="spouses.wife.maritalStatus"
                                            @change="updateCompletion"
                                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800">
                                        <option value="">Select Status</option>
                                        <option value="Spinster">Spinster</option>
                                        <option value="Married">Married</option>
                                        <option value="Widowed">Widowed</option>
                                        <option value="Divorced">Divorced</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Residence
                                </label>
                                <input type="text" 
                                    name="wife_residence" 
                                    x-model="spouses.wife.residence"
                                    @input="updateCompletion"
                                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 uppercase-field"
                                    placeholder="Residence"
                                    :readonly="!residenceEditable.wife">
                            </div>

                            <!-- Wife's Father -->
                            <div class="border-t pt-4 mt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="text-sm font-medium text-gray-700">Father's Information</h5>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" 
                                            name="wife_father_deceased"
                                            x-model="spouses.wife.father.deceased"
                                            @change="toggleDeceased('wife', 'father')"
                                            class="rounded border-gray-300">
                                        <span class="text-sm text-gray-600">Deceased</span>
                                    </label>
                                </div>
                                
                                <div class="space-y-3">
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                        <div>
                                            <input type="text" 
                                                name="wife_father_name" 
                                                x-model="spouses.wife.father.name"
                                                @input="formatNameCase('wife', 'father', 'name'); updateCompletion()"
                                                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 name-field"
                                                placeholder="Father's full name"
                                                :readonly="spouses.wife.father.deceased"
                                                :class="spouses.wife.father.deceased ? 'bg-gray-100 cursor-not-allowed' : ''">
                                        </div>
                                        <div>
                                            <input type="text" 
                                                name="wife_father_occupation" 
                                                x-model="spouses.wife.father.occupation"
                                                @input="updateCompletion"
                                                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 uppercase-field"
                                                placeholder="Father's occupation"
                                                :readonly="spouses.wife.father.deceased"
                                                :class="spouses.wife.father.deceased ? 'bg-gray-100 cursor-not-allowed' : ''"
                                                list="occupation-list">
                                        </div>
                                    </div>
                                    <div>
                                        <input type="text" 
                                            name="wife_father_residence" 
                                            x-model="spouses.wife.father.residence"
                                            @input="updateCompletion"
                                            class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 uppercase-field"
                                            placeholder="Residence"
                                            :readonly="spouses.wife.father.deceased"
                                            :class="spouses.wife.father.deceased ? 'bg-gray-100 cursor-not-allowed' : ''">
                                    </div>
                                </div>
                            </div>

                            <!-- Wife's Mother -->
                            <div class="border-t pt-4 mt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="text-sm font-medium text-gray-700">Mother's Information</h5>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" 
                                            name="wife_mother_deceased"
                                            x-model="spouses.wife.mother.deceased"
                                            @change="toggleDeceased('wife', 'mother')"
                                            class="rounded border-gray-300">
                                        <span class="text-sm text-gray-600">Deceased</span>
                                    </label>
                                </div>
                                
                                <div class="space-y-3">
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                        <div>
                                            <input type="text" 
                                                name="wife_mother_name" 
                                                x-model="spouses.wife.mother.name"
                                                @input="formatNameCase('wife', 'mother', 'name'); updateCompletion()"
                                                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 name-field"
                                                placeholder="Mother's full name"
                                                :readonly="spouses.wife.mother.deceased"
                                                :class="spouses.wife.mother.deceased ? 'bg-gray-100 cursor-not-allowed' : ''">
                                        </div>
                                        <div>
                                            <input type="text" 
                                                name="wife_mother_occupation" 
                                                x-model="spouses.wife.mother.occupation"
                                                @input="updateCompletion"
                                                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 uppercase-field"
                                                placeholder="Mother's occupation"
                                                :readonly="spouses.wife.mother.deceased"
                                                :class="spouses.wife.mother.deceased ? 'bg-gray-100 cursor-not-allowed' : ''"
                                                list="occupation-list">
                                        </div>
                                    </div>
                                    <div>
                                        <input type="text" 
                                            name="wife_mother_residence" 
                                            x-model="spouses.wife.mother.residence"
                                            @input="updateCompletion"
                                            class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 uppercase-field"
                                            placeholder="Residence"
                                            :readonly="spouses.wife.mother.deceased"
                                            :class="spouses.wife.mother.deceased ? 'bg-gray-100 cursor-not-allowed' : ''">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Witnesses Information Section -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-4">
                <div 
                    @click="toggleSection('witnesses')" 
                    class="flex cursor-pointer items-center justify-between px-5 py-4 sm:px-6 sm:py-5"
                >
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Witnesses Information
                        </h3>
                        <span class="completion-badge px-2 py-1 rounded-full text-xs font-medium" :class="getSectionBadgeClass('witnesses')" x-text="sectionCompletion.witnesses + '%'"></span>
                    </div>
                    <button :class="openSection === 'witnesses' ? 'text-primary' : 'text-gray-400'">
                        <svg x-show="openSection !== 'witnesses'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg x-show="openSection === 'witnesses'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 12H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <div 
                    x-show="openSection === 'witnesses'" 
                    class="border-t border-gray-200 px-5 py-4 sm:px-6 sm:py-5 dark:border-gray-800"
                >
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Witness 1 Full Name <span class="text-red-500" x-show="!skipValidation">*</span>
                                </label>
                                <input type="text" 
                                    name="witness1_name" 
                                    x-model="witnesses.witness1.name"
                                    @input="formatNameCase('witness1', '', 'name'); updateCompletion()"
                                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 name-field"
                                    placeholder="Witness 1 full name"
                                    :required="!skipValidation">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Witness 1 Side
                                </label>
                                <select name="witness1_side" 
                                        x-model="witnesses.witness1.side"
                                        @change="updateCompletion"
                                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800">
                                    <option value="">Select Side</option>
                                    <option value="husband">Husband's Side</option>
                                    <option value="wife">Wife's Side</option>
                                    <option value="both">Both Sides</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Witness 2 Full Name <span class="text-red-500" x-show="!skipValidation">*</span>
                                </label>
                                <input type="text" 
                                    name="witness2_name" 
                                    x-model="witnesses.witness2.name"
                                    @input="formatNameCase('witness2', '', 'name'); updateCompletion()"
                                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 name-field"
                                    placeholder="Witness 2 full name"
                                    :required="!skipValidation">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Witness 2 Side
                                </label>
                                <select name="witness2_side" 
                                        x-model="witnesses.witness2.side"
                                        @change="updateCompletion"
                                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800">
                                    <option value="">Select Side</option>
                                    <option value="husband">Husband's Side</option>
                                    <option value="wife">Wife's Side</option>
                                    <option value="both">Both Sides</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 sm:p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Notes <span class="text-gray-500">(Optional - will be saved to PDF page)</span>
                </label>
                <textarea
                    name="notes" 
                    x-model="formData.notes"
                    @input="updateCompletion"
                    rows="3"
                    placeholder="Additional notes about this marriage record"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800"
                ></textarea>
            </div>

            <!-- Common Occupations Datalist -->
            <datalist id="occupation-list">
                @foreach($commonOccupations ?? [] as $occ)
                    <option value="{{ $occ }}">
                @endforeach
            </datalist>

            <!-- Form Actions -->
            <div class="flex flex-col gap-4 pt-4">
                @if(auth()->user()->role->name !== 'data_clerk')

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <button type="button" 
                            @click="debugFormData"
                            class="inline-flex justify-center items-center gap-2 rounded-lg border border-gray-300 bg-purple-50 px-4 py-3 text-sm font-medium text-purple-700 hover:bg-purple-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"></path>
                        </svg>
                        Debug Data
                    </button>
                    <button type="button" 
                            @click="saveProgress"
                            class="inline-flex justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Save Progress
                    </button>
                    <button type="button" 
                            @click="loadProgress"
                            class="inline-flex justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Load Progress
                    </button>
                    <button type="button" 
                            @click="viewSavedProgress"
                            class="inline-flex justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Saved
                    </button>
                </div>
                @endif
                
                <button type="submit" 
                        class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-green-600 px-4 py-3 text-sm font-medium text-white hover:bg-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Create Full Marriage Record
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Missing Fields Modal -->
@include('partials.modal.missing-fields-modal')

<script>
window.fullEntryForm = function() {
    return {
        // Section state
        openSection: 'basic',
        openSubsection: null,
        showDebugPanel: false,
        debugOutput: '',
        
        // Missing fields modal
        showMissingModal: false,
        missingFieldsList: [],
        missingReason: '',
        pendingSubmit: null,
        
        // Error tracking
        hasErrors: false,
        
        // Validation bypass toggle
        skipValidation: 0,
        
        // Form Data - matches database structure
        formData: {
            certificateSerial: '',
            licenseNo: '',
            venue: '',
            marriageDate: '',
            regDate: '',
            selectedConstituency: '',
            ward_id: '',
            notes: ''
        },
        
        // Spouses data - matches Spouse model
        spouses: {
            husband: {
                name: '',
                age: '',
                occupation: '',
                maritalStatus: '',
                residence: '',
                spouse_type: 'husband',
                father: { 
                    name: '', 
                    occupation: '', 
                    residence: '', 
                    deceased: false 
                },
                mother: { 
                    name: '', 
                    occupation: '', 
                    residence: '', 
                    deceased: false 
                }
            },
            wife: {
                name: '',
                age: '',
                occupation: '',
                maritalStatus: '',
                residence: '',
                spouse_type: 'wife',
                father: { 
                    name: '', 
                    occupation: '', 
                    residence: '', 
                    deceased: false 
                },
                mother: { 
                    name: '', 
                    occupation: '', 
                    residence: '', 
                    deceased: false 
                }
            }
        },
        
        // Witnesses data - matches Witness model
        witnesses: {
            witness1: { 
                name: '', 
                side: '',
                spouse_side: ''
            },
            witness2: { 
                name: '', 
                side: '',
                spouse_side: ''
            }
        },
        
        // Marriage type extensions
        showExtensions: false,
        extensionTitle: '',
        extensionDescription: '',
        extensionFields: [],
        extensionData: {
            mahr_agreed: '',
            mahr_paid: '',
            mahr_deferred: '',
            gifts: '',
            muslim_officer: '',
            church_org: '',
            pastor_name: '',
            entry_no: '',
            temple: '',
            dowry: '',
            registrar_officer: ''
        },
        
        // Completion tracking
        completionPercentage: 0,
        sectionCompletion: {
            basic: 0,
            location: 0,
            spouses: 0,
            witnesses: 0
        },
        personCompletion: {
            husband: 0,
            wife: 0,
            witness1: 0,
            witness2: 0
        },
        
        // Residence editability tracking
        residenceEditable: {
            husband: true,
            wife: true,
            husbandFather: true,
            husbandMother: true,
            wifeFather: true,
            wifeMother: true
        },
        
        // Common occupations
        commonOccupations: [
            'Teacher', 'Doctor', 'Engineer', 'Lawyer', 'Accountant',
            'Businessman', 'Businesswoman', 'Civil Servant', 'Farmer',
            'Mechanic', 'Driver', 'Nurse', 'Police Officer', 'Soldier',
            'Lecturer', 'Journalist', 'Architect', 'Pharmacist', 'Dentist',
            'DECEASED'
        ],
        
        // Wards data
        allWards: @json($wards ?? []),
        
        // Computed property - filtered wards by constituency
        get filteredWards() {
            if (!this.formData.selectedConstituency) return [];
            return this.allWards.filter(w => 
                w.constituency === this.formData.selectedConstituency
            );
        },
        
        init() {
            console.log('✅ Full Entry Form initialized');
            
            // Clear any saved data from localStorage
            localStorage.removeItem('fullEntryFormProgress');
            
            // Set default dates from PDF upload
            @if($pdfUpload)
                let year = {{ $pdfUpload->year }};
                let month = {{ $pdfUpload->month }};
                let defaultDate = new Date(year, month - 1, 1);
                
                let yyyy = defaultDate.getFullYear();
                let mm = String(defaultDate.getMonth() + 1).padStart(2, '0');
                let dd = String(defaultDate.getDate()).padStart(2, '0');
                this.formData.marriageDate = `${yyyy}-${mm}-${dd}`;
                this.formData.regDate = this.formData.marriageDate;
                
                console.log('📅 Default dates set from PDF:', {
                    marriageDate: this.formData.marriageDate,
                    regDate: this.formData.regDate
                });
            @endif
            
            this.initMarriageTypeExtensions();
            this.updateCompletion();
            
            // Watch for skipValidation changes
            this.$watch('skipValidation', (value) => {
                this.updateCompletion();
                console.log('Validation bypassed:', value);
            });
        },
        
        initMarriageTypeExtensions() {
            const marriageTypeId = {{ $pdfUpload->marriage_type_id }};
            const marriageTypeName = "{{ $pdfUpload->marriageType->name ?? '' }}";
            
            console.log('Initializing extensions for marriage type:', marriageTypeName, 'ID:', marriageTypeId);
            
            const extensions = {
                'Christian': {
                    title: 'Christian Marriage Details',
                    description: 'Christian marriage requires church and pastor information',
                    fields: [
                        { name: 'church_org', label: 'Church/Organization', type: 'text', required: true },
                        { name: 'pastor_name', label: 'Pastor/Officiant Name', type: 'text', required: true }
                    ]
                },
                'Muslim': {
                    title: 'Islamic Marriage Details',
                    description: 'Islamic marriage requires Mahr information',
                    fields: [
                        { name: 'mahr_agreed', label: 'Mahr Agreed', type: 'text', required: true },
                        { name: 'mahr_paid', label: 'Mahr Paid', type: 'text', required: false },
                        { name: 'mahr_deferred', label: 'Mahr Deferred', type: 'text', required: false },
                        { name: 'gifts', label: 'Gifts', type: 'text', required: false },
                        { name: 'muslim_officer', label: 'Muslim Officer', type: 'text', required: true }
                    ]
                },
                'Hindu': {
                    title: 'Hindu Marriage Details',
                    description: 'Hindu marriage requires temple and registrar information',
                    fields: [
                        { name: 'registrar_officer', label: 'Registrar Officer', type: 'text', required: true },
                        { name: 'temple', label: 'Temple Name', type: 'text', required: true },
                        { name: 'dowry', label: 'Dowry Details', type: 'text', required: false }
                    ]
                },
                'Civil': {
                    title: 'Civil Marriage Details',
                    description: 'Civil marriage requires registrar information',
                    fields: [
                        { name: 'registrar_officer', label: 'Registrar Officer', type: 'text', required: true }
                    ]
                },
            };
            
            const ext = extensions[marriageTypeName];
            if (ext) {
                this.showExtensions = true;
                this.extensionTitle = ext.title;
                this.extensionDescription = ext.description;
                this.extensionFields = ext.fields;
                
                // Initialize extension data fields
                ext.fields.forEach(field => {
                    this.extensionData[field.name] = '';
                });
                
                console.log('📋 Extension fields initialized:', this.extensionFields);
            } else {
                console.log('No extensions for marriage type:', marriageTypeName);
            }
        },
        
        focusOnErrors() {
            const requiredFields = ['certificateSerial', 'venue', 'marriageDate'];
            for (let field of requiredFields) {
                if (!this.formData[field]) {
                    const element = document.querySelector(`[name="${field}"]`);
                    if (element) {
                        element.focus();
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        break;
                    }
                }
            }
        },
        
        loadWards() {
            this.formData.ward_id = '';
        },
        
        toggleSection(section) {
            this.openSection = this.openSection === section ? null : section;
        },
        
        toggleSubsection(section) {
            this.openSubsection = this.openSubsection === section ? null : section;
        },
        
        formatNameCase(spouse, parent, field) {
            const capitalizeWords = (str) => {
                if (!str) return str;
                return str.replace(/\b\w/g, l => l.toUpperCase())
                         .replace(/[^a-zA-Z\s\-']/g, '');
            };
            
            let value;
            if (parent) {
                value = this.spouses[spouse][parent][field];
                this.spouses[spouse][parent][field] = capitalizeWords(value);
            } else if (spouse.startsWith('witness')) {
                value = this.witnesses[spouse][field];
                this.witnesses[spouse][field] = capitalizeWords(value);
            } else {
                value = this.spouses[spouse][field];
                this.spouses[spouse][field] = capitalizeWords(value);
            }
        },
        
        toggleDeceased(spouse, parent) {
            const isDeceased = this.spouses[spouse][parent].deceased;
            
            if (isDeceased) {
                this.spouses[spouse][parent].occupation = 'DECEASED';
                this.spouses[spouse][parent].residence = 'XXX';
            } else {
                this.spouses[spouse][parent].occupation = '';
                this.spouses[spouse][parent].residence = '';
            }
            
            this.updateCompletion();
        },
        
        updateResidencesFromWard() {
            if (this.formData.ward_id && this.formData.selectedConstituency) {
                const selectedWard = this.allWards.find(w => w.id == this.formData.ward_id);
                if (selectedWard) {
                    const locationString = `${selectedWard.wards}, ${this.formData.selectedConstituency}`;
                    
                    if (!this.spouses.husband.residence || this.spouses.husband.residence === '') {
                        this.spouses.husband.residence = locationString;
                    }
                    if (!this.spouses.wife.residence || this.spouses.wife.residence === '') {
                        this.spouses.wife.residence = locationString;
                    }
                    if (!this.spouses.husband.father.residence || this.spouses.husband.father.residence === '') {
                        if (!this.spouses.husband.father.deceased) {
                            this.spouses.husband.father.residence = locationString;
                        }
                    }
                    if (!this.spouses.husband.mother.residence || this.spouses.husband.mother.residence === '') {
                        if (!this.spouses.husband.mother.deceased) {
                            this.spouses.husband.mother.residence = locationString;
                        }
                    }
                    if (!this.spouses.wife.father.residence || this.spouses.wife.father.residence === '') {
                        if (!this.spouses.wife.father.deceased) {
                            this.spouses.wife.father.residence = locationString;
                        }
                    }
                    if (!this.spouses.wife.mother.residence || this.spouses.wife.mother.residence === '') {
                        if (!this.spouses.wife.mother.deceased) {
                            this.spouses.wife.mother.residence = locationString;
                        }
                    }
                }
            }
            this.updateCompletion();
        },
        
        updateCompletion() {
            let basicFields = [
                this.formData.certificateSerial, 
                this.formData.venue, 
                this.formData.marriageDate
            ];
            
            if (this.showExtensions && this.extensionFields) {
                this.extensionFields.forEach(field => {
                    if (field.required && !this.skipValidation) {
                        basicFields.push(this.extensionData[field.name]);
                    }
                });
            }
            
            let basicCompleted = basicFields.filter(f => f && f.toString().trim() !== '').length;
            this.sectionCompletion.basic = basicFields.length > 0 ? Math.round((basicCompleted / basicFields.length) * 100) : 100;
            
            let locationFields = [this.formData.selectedConstituency, this.formData.ward_id];
            let locationCompleted = locationFields.filter(f => f && f.toString().trim() !== '').length;
            this.sectionCompletion.location = locationFields.length > 0 ? Math.round((locationCompleted / locationFields.length) * 100) : 100;
            
            let spouseFields = [
                this.spouses.husband.name, 
                this.spouses.husband.age,
                this.spouses.wife.name,
                this.spouses.wife.age
            ];
            let spouseCompleted = spouseFields.filter(f => f && f.toString().trim() !== '').length;
            this.sectionCompletion.spouses = spouseFields.length > 0 ? Math.round((spouseCompleted / spouseFields.length) * 100) : 100;
            
            let witnessFields = [
                this.witnesses.witness1.name, 
                this.witnesses.witness2.name
            ];
            let witnessCompleted = witnessFields.filter(f => f && f.toString().trim() !== '').length;
            this.sectionCompletion.witnesses = witnessFields.length > 0 ? Math.round((witnessCompleted / witnessFields.length) * 100) : 100;
            
            let husbandFields = [this.spouses.husband.name, this.spouses.husband.age];
            let husbandCompleted = husbandFields.filter(f => f && f.toString().trim() !== '').length;
            this.personCompletion.husband = husbandFields.length > 0 ? Math.round((husbandCompleted / husbandFields.length) * 100) : 100;
            
            let wifeFields = [this.spouses.wife.name, this.spouses.wife.age];
            let wifeCompleted = wifeFields.filter(f => f && f.toString().trim() !== '').length;
            this.personCompletion.wife = wifeFields.length > 0 ? Math.round((wifeCompleted / wifeFields.length) * 100) : 100;
            
            this.personCompletion.witness1 = this.witnesses.witness1.name ? 100 : (this.skipValidation ? 100 : 0);
            this.personCompletion.witness2 = this.witnesses.witness2.name ? 100 : (this.skipValidation ? 100 : 0);
            
            let allFields = [...basicFields, ...locationFields, ...spouseFields, ...witnessFields];
            let allCompleted = allFields.filter(f => f && f.toString().trim() !== '').length;
            this.completionPercentage = allFields.length > 0 ? Math.round((allCompleted / allFields.length) * 100) : 100;
            
            this.hasErrors = this.checkMissingFields().length > 0;
        },
        
        getCompletionColor() {
            if (this.completionPercentage >= 80) return '#10b981';
            if (this.completionPercentage >= 50) return '#f59e0b';
            return '#ef4444';
        },
        
        getSectionBadgeClass(section) {
            let percentage = this.sectionCompletion[section] || 0;
            if (percentage >= 80) return 'bg-green-100 text-green-800';
            if (percentage >= 50) return 'bg-yellow-100 text-yellow-800';
            return 'bg-red-100 text-red-800';
        },
        
        getPersonBadgeClass(person) {
            let percentage = this.personCompletion[person] || 0;
            if (percentage >= 80) return 'bg-green-100 text-green-800';
            if (percentage >= 50) return 'bg-yellow-100 text-yellow-800';
            return 'bg-red-100 text-red-800';
        },
        
        hasDeceasedParents() {
            return (
                this.spouses.husband.father.deceased ||
                this.spouses.husband.mother.deceased ||
                this.spouses.wife.father.deceased ||
                this.spouses.wife.mother.deceased
            );
        },
        
        checkMissingFields() {
            // If skipping validation, never show missing fields
            if (this.skipValidation) {
                return [];
            }
            
            const missing = [];
            
            if (!this.formData.certificateSerial) missing.push('Certificate Serial Number');
            if (!this.formData.venue) missing.push('Venue');
            if (!this.formData.marriageDate) missing.push('Marriage Date');
            if (!this.formData.selectedConstituency) missing.push('Constituency');
            if (!this.formData.ward_id) missing.push('Ward');
            if (!this.spouses.husband.name) missing.push('Husband Name');
            if (!this.spouses.wife.name) missing.push('Wife Name');
            if (!this.spouses.husband.age) missing.push('Husband Age');
            if (!this.spouses.wife.age) missing.push('Wife Age');
            if (!this.witnesses.witness1.name) missing.push('Witness 1 Name');
            if (!this.witnesses.witness2.name) missing.push('Witness 2 Name');
            
            return missing;
        },
        
        debugFormData() {
            let output = '';
            
            const checkField = (label, value) => {
                const status = value && value.toString().trim() !== '' ? '✓' : '✗';
                const displayValue = value && value.toString().trim() !== '' ? value : 'MISSING';
                return `${status} ${label}: ${displayValue}\n`;
            };
            
            const showDeceasedStatus = (deceased) => {
                return deceased ? '[DECEASED]' : '[LIVING]';
            };
            
            output += '='.repeat(50) + '\n';
            output += ' FORM DATA DEBUG\n';
            output += '='.repeat(50) + '\n\n';
            
            output += 'MARRIAGE DATA:\n';
            output += '-'.repeat(30) + '\n';
            output += checkField('Certificate Serial', this.formData.certificateSerial);
            output += checkField('License No', this.formData.licenseNo);
            output += checkField('Venue', this.formData.venue);
            output += checkField('Marriage Date', this.formData.marriageDate);
            output += checkField('Registration Date', this.formData.regDate);
            output += checkField('Constituency', this.formData.selectedConstituency);
            output += checkField('Ward ID', this.formData.ward_id);
            output += '\n';
            
            output += 'HUSBAND DATA:\n';
            output += '-'.repeat(30) + '\n';
            output += checkField('Name', this.spouses.husband.name);
            output += checkField('Age', this.spouses.husband.age);
            output += checkField('Marital Status', this.spouses.husband.maritalStatus);
            output += checkField('Occupation', this.spouses.husband.occupation);
            output += checkField('Residence', this.spouses.husband.residence);
            output += '\n';
            
            output += 'HUSBAND\'S FATHER:\n';
            output += '-'.repeat(30) + '\n';
            output += checkField('Name', this.spouses.husband.father.name);
            output += checkField('Occupation', this.spouses.husband.father.occupation);
            output += checkField('Residence', this.spouses.husband.father.residence);
            output += `Status: ${showDeceasedStatus(this.spouses.husband.father.deceased)}\n`;
            output += '\n';
            
            output += 'HUSBAND\'S MOTHER:\n';
            output += '-'.repeat(30) + '\n';
            output += checkField('Name', this.spouses.husband.mother.name);
            output += checkField('Occupation', this.spouses.husband.mother.occupation);
            output += checkField('Residence', this.spouses.husband.mother.residence);
            output += `Status: ${showDeceasedStatus(this.spouses.husband.mother.deceased)}\n`;
            output += '\n';
            
            output += 'WIFE DATA:\n';
            output += '-'.repeat(30) + '\n';
            output += checkField('Name', this.spouses.wife.name);
            output += checkField('Age', this.spouses.wife.age);
            output += checkField('Marital Status', this.spouses.wife.maritalStatus);
            output += checkField('Occupation', this.spouses.wife.occupation);
            output += checkField('Residence', this.spouses.wife.residence);
            output += '\n';
            
            output += 'WIFE\'S FATHER:\n';
            output += '-'.repeat(30) + '\n';
            output += checkField('Name', this.spouses.wife.father.name);
            output += checkField('Occupation', this.spouses.wife.father.occupation);
            output += checkField('Residence', this.spouses.wife.father.residence);
            output += `Status: ${showDeceasedStatus(this.spouses.wife.father.deceased)}\n`;
            output += '\n';
            
            output += 'WIFE\'S MOTHER:\n';
            output += '-'.repeat(30) + '\n';
            output += checkField('Name', this.spouses.wife.mother.name);
            output += checkField('Occupation', this.spouses.wife.mother.occupation);
            output += checkField('Residence', this.spouses.wife.mother.residence);
            output += `Status: ${showDeceasedStatus(this.spouses.wife.mother.deceased)}\n`;
            output += '\n';
            
            output += 'WITNESSES:\n';
            output += '-'.repeat(30) + '\n';
            output += 'WITNESS 1:\n';
            output += checkField('  Name', this.witnesses.witness1.name);
            output += checkField('  Side', this.witnesses.witness1.side);
            output += '\n';
            output += 'WITNESS 2:\n';
            output += checkField('  Name', this.witnesses.witness2.name);
            output += checkField('  Side', this.witnesses.witness2.side);
            output += '\n';
            
            if (this.showExtensions) {
                output += 'EXTENSION DATA:\n';
                output += '-'.repeat(30) + '\n';
                
                this.extensionFields.forEach(field => {
                    const value = this.extensionData[field.name];
                    const displayName = field.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    output += checkField(displayName, value);
                });
            } else {
                output += 'No extension fields for this marriage type\n';
            }
            
            output += `\nHAS DECEASED PARENTS: ${this.hasDeceasedParents() ? 'YES' : 'NO'}\n`;
            output += 'COMPLETION: ' + this.completionPercentage + '%\n';
            output += '='.repeat(50) + '\n';
            
            this.debugOutput = output;
            this.showDebugPanel = true;
            
            console.log(output);
        },
        
        saveProgress() {
            const data = {
                formData: this.formData,
                spouses: this.spouses,
                witnesses: this.witnesses,
                extensionData: this.extensionData,
                savedAt: new Date().toISOString()
            };
            
            localStorage.setItem('fullEntryFormProgress', JSON.stringify(data));
            
            if (window.pdfPageViewer) {
                window.pdfPageViewer().showAlert('success', 'Success', 'Progress saved successfully!');
            } else {
                alert('✅ Progress saved successfully!');
            }
        },
        
        loadSavedData() {
            const saved = localStorage.getItem('fullEntryFormProgress');
            if (!saved) return;
            
            try {
                const data = JSON.parse(saved);
                if (data.formData) Object.assign(this.formData, data.formData);
                if (data.spouses) Object.assign(this.spouses, data.spouses);
                if (data.witnesses) Object.assign(this.witnesses, data.witnesses);
                if (data.extensionData) Object.assign(this.extensionData, data.extensionData);
                
                this.updateCompletion();
                
                console.log('📦 Loaded saved data:', data);
            } catch (e) {
                console.error('Error loading saved data:', e);
            }
        },
        
        loadProgress() {
            this.loadSavedData();
            
            if (window.pdfPageViewer) {
                window.pdfPageViewer().showAlert('success', 'Success', 'Progress loaded successfully!');
            } else {
                alert('✅ Progress loaded successfully!');
            }
        },
        
        viewSavedProgress() {
            const saved = localStorage.getItem('fullEntryFormProgress');
            if (!saved) {
                if (window.pdfPageViewer) {
                    window.pdfPageViewer().showAlert('error', 'No Saved Data', 'No saved progress found.');
                } else {
                    alert('❌ No saved progress found.');
                }
                return;
            }
            
            try {
                const data = JSON.parse(saved);
                let summary = 'SAVED PROGRESS\n══════════════\n\n';
                summary += `Saved: ${new Date(data.savedAt).toLocaleString()}\n\n`;
                summary += `Certificate: ${data.formData?.certificateSerial || 'Not set'}\n`;
                summary += `License: ${data.formData?.licenseNo || 'Not set'}\n`;
                summary += `Venue: ${data.formData?.venue || 'Not set'}\n`;
                summary += `Date: ${data.formData?.marriageDate || 'Not set'}\n`;
                summary += `Constituency: ${data.formData?.selectedConstituency || 'Not set'}\n`;
                summary += `Ward: ${data.formData?.ward_id || 'Not set'}\n\n`;
                summary += `Husband: ${data.spouses?.husband?.name || 'Not set'} (Age: ${data.spouses?.husband?.age || 'Not set'})\n`;
                summary += `Wife: ${data.spouses?.wife?.name || 'Not set'} (Age: ${data.spouses?.wife?.age || 'Not set'})\n`;
                summary += `Witness 1: ${data.witnesses?.witness1?.name || 'Not set'}\n`;
                summary += `Witness 2: ${data.witnesses?.witness2?.name || 'Not set'}\n`;
                
                if (data.extensionData && Object.keys(data.extensionData).length > 0) {
                    summary += '\nExtension Data:\n';
                    for (let [key, value] of Object.entries(data.extensionData)) {
                        if (value) summary += `   • ${key}: ${value}\n`;
                    }
                }
                
                summary += `\nCompletion: ${this.completionPercentage}%`;
                
                if (window.pdfPageViewer) {
                    window.pdfPageViewer().showAlert('info', 'Saved Progress', summary);
                } else {
                    alert(summary);
                }
            } catch (e) {
                console.error('Error parsing saved data:', e);
            }
        },
        
        getMissingFieldsCount() {
            if (this.skipValidation) return 0;
            
            let count = 0;
            
            if (!this.formData.certificateSerial) count++;
            if (!this.formData.venue) count++;
            if (!this.formData.marriageDate) count++;
            if (!this.formData.selectedConstituency) count++;
            if (!this.formData.ward_id) count++;
            if (!this.spouses.husband.name) count++;
            if (!this.spouses.husband.age) count++;
            if (!this.spouses.wife.name) count++;
            if (!this.spouses.wife.age) count++;
            if (!this.witnesses.witness1.name) count++;
            if (!this.witnesses.witness2.name) count++;
            
            return count;
        },
        
        submitForm(e) {
            console.log('🚀 SUBMIT FORM TRIGGERED');
            e.preventDefault();
            
            // If skipValidation is true, bypass missing fields check
            if (!this.skipValidation) {
                const missing = this.checkMissingFields();
                if (missing.length > 0) {
                    this.missingFieldsList = missing;
                    this.missingReason = '';
                    this.showMissingModal = true;
                    this.pendingSubmit = e;
                    return;
                }
            }
            
            this.doSubmit(e);
        },
        
        submitWithMissing() {
            if (this.missingReason) {
                this.formData.notes = (this.formData.notes ? this.formData.notes + '\n' : '') + 
                    '[INCOMPLETE SUBMISSION] Reason: ' + this.missingReason;
            }
            
            this.showMissingModal = false;
            if (this.pendingSubmit) {
                this.doSubmit(this.pendingSubmit);
            }
        },
        
        cancelSubmit() {
            this.showMissingModal = false;
            this.pendingSubmit = null;
        },
        
        doSubmit(e) {
            console.log('🚀 DO SUBMIT EXECUTED');
            
            // Skip all validation if skipValidation is true
            if (!this.skipValidation) {
                if (!this.formData.marriageDate) {
                    alert('Marriage date is required');
                    return;
                }
                
                if (!this.formData.certificateSerial) {
                    alert('Certificate serial is required');
                    return;
                }
                
                if (!this.formData.venue) {
                    alert('Venue is required');
                    return;
                }
                
                if (!this.formData.selectedConstituency) {
                    alert('Constituency is required');
                    return;
                }
                
                if (!this.formData.ward_id) {
                    alert('Ward is required');
                    return;
                }
                
                if (!this.spouses.husband.name) {
                    alert('Husband name is required');
                    return;
                }
                
                if (!this.spouses.husband.age) {
                    alert('Husband age is required');
                    return;
                }
                
                if (!this.spouses.wife.name) {
                    alert('Wife name is required');
                    return;
                }
                
                if (!this.spouses.wife.age) {
                    alert('Wife age is required');
                    return;
                }
                
                if (!this.witnesses.witness1.name) {
                    alert('Witness 1 name is required');
                    return;
                }
                
                if (!this.witnesses.witness2.name) {
                    alert('Witness 2 name is required');
                    return;
                }
            }
            
            this.debugFormData();
            
            const missingFields = this.getMissingFieldsCount();
            if (missingFields > 0 && !this.skipValidation) {
                if (!confirm(`⚠️ There are ${missingFields} missing required fields. Do you still want to submit?`)) {
                    return;
                }
            }
            
            const form = e.target;
            
            // Set spouse_type
            this.spouses.husband.spouse_type = 'husband';
            this.spouses.wife.spouse_type = 'wife';
            
            // Set witness spouse_side
            this.witnesses.witness1.spouse_side = this.witnesses.witness1.side;
            this.witnesses.witness2.spouse_side = this.witnesses.witness2.side;
            
            // Update form fields
            const fieldMappings = [
                { name: 'certificate_serial', value: this.formData.certificateSerial },
                { name: 'license_no', value: this.formData.licenseNo },
                { name: 'venue', value: this.formData.venue },
                { name: 'marriage_date', value: this.formData.marriageDate },
                { name: 'reg_date', value: this.formData.regDate },
                { name: 'sub_county', value: this.formData.selectedConstituency },
                { name: 'ward_id', value: this.formData.ward_id },
                { name: 'notes', value: this.formData.notes },
                
                { name: 'husband_name', value: this.spouses.husband.name },
                { name: 'husband_age', value: this.spouses.husband.age },
                { name: 'husband_marital_status', value: this.spouses.husband.maritalStatus },
                { name: 'husband_occupation', value: this.spouses.husband.occupation },
                { name: 'husband_residence', value: this.spouses.husband.residence },
                
                { name: 'husband_father_name', value: this.spouses.husband.father.name },
                { name: 'husband_father_occupation', value: this.spouses.husband.father.occupation },
                { name: 'husband_father_residence', value: this.spouses.husband.father.residence },
                
                { name: 'husband_mother_name', value: this.spouses.husband.mother.name },
                { name: 'husband_mother_occupation', value: this.spouses.husband.mother.occupation },
                { name: 'husband_mother_residence', value: this.spouses.husband.mother.residence },
                
                { name: 'wife_name', value: this.spouses.wife.name },
                { name: 'wife_age', value: this.spouses.wife.age },
                { name: 'wife_marital_status', value: this.spouses.wife.maritalStatus },
                { name: 'wife_occupation', value: this.spouses.wife.occupation },
                { name: 'wife_residence', value: this.spouses.wife.residence },
                
                { name: 'wife_father_name', value: this.spouses.wife.father.name },
                { name: 'wife_father_occupation', value: this.spouses.wife.father.occupation },
                { name: 'wife_father_residence', value: this.spouses.wife.father.residence },
                
                { name: 'wife_mother_name', value: this.spouses.wife.mother.name },
                { name: 'wife_mother_occupation', value: this.spouses.wife.mother.occupation },
                { name: 'wife_mother_residence', value: this.spouses.wife.mother.residence },
                
                { name: 'witness1_name', value: this.witnesses.witness1.name },
                { name: 'witness1_side', value: this.witnesses.witness1.side },
                { name: 'witness2_name', value: this.witnesses.witness2.name },
                { name: 'witness2_side', value: this.witnesses.witness2.side }
            ];
            
            fieldMappings.forEach(mapping => {
                const input = document.querySelector(`[name="${mapping.name}"]`);
                if (input) {
                    input.value = mapping.value || '';
                } else if (mapping.name === 'marriage_date' || mapping.name === 'reg_date') {
                    let input = document.querySelector(`input[name="${mapping.name}"]`);
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = mapping.name;
                        form.appendChild(input);
                    }
                    input.value = mapping.value || '';
                }
            });
            
            // Handle deceased checkboxes
            const deceasedFields = [
                { name: 'husband_father_deceased', value: this.spouses.husband.father.deceased },
                { name: 'husband_mother_deceased', value: this.spouses.husband.mother.deceased },
                { name: 'wife_father_deceased', value: this.spouses.wife.father.deceased },
                { name: 'wife_mother_deceased', value: this.spouses.wife.mother.deceased }
            ];
            
            deceasedFields.forEach(field => {
                let input = document.querySelector(`[name="${field.name}"]`);
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = field.name;
                    form.appendChild(input);
                }
                input.value = field.value === true ? '1' : '0';
            });
            
            // Add extension fields
            if (this.showExtensions && this.extensionFields) {
                this.extensionFields.forEach(field => {
                    const value = this.extensionData[field.name];
                    if (value) {
                        let input = document.querySelector(`[name="${field.name}"]`);
                        if (!input) {
                            input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = field.name;
                            form.appendChild(input);
                        }
                        input.value = value;
                    }
                });
            }
            
            console.log('🚀 Submitting form with data:');
            const formData = new FormData(form);
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            form.submit();
        }
    };
}

// Initialize after DOM is loaded
document.addEventListener('alpine:init', () => {
    console.log('Alpine initialized');
});
</script>

<style>
    [x-cloak] { display: none !important; }
    .uppercase-field { text-transform: uppercase; }
    .name-field { text-transform: capitalize; }
    
    input[type="date"] {
        z-index: 99999 !important;
        position: relative;
    }
    
    input[type="date"]::-webkit-calendar-picker-indicator {
        z-index: 99999;
        position: relative;
    }
    
    input:disabled {
        background-color: #f3f4f6;
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .completion-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
    }
    
    .person-completion-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
    }
</style>