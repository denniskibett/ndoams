<div x-data="quickEntryForm()" x-init="init()" class="space-y-6">
    <!-- Quick Entry Header -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <div class="text-yellow-600">
                <i class="fas fa-bolt text-xl"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-yellow-800">Quick Entry Mode</h4>
                <p class="text-sm text-yellow-700 mt-1">
                    This will update the major marriage certificate details only. 
                    <strong>No personnel information (spouses, parents, witnesses) will be saved.</strong>
                    You can add complete details later by editing the record.
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('pdf.quick-create-from-page') }}" method="POST" id="quickCreateForm">
        @csrf
        
        <!-- Hidden Fields -->
        <input type="hidden" name="pdf_page_id" value="{{ $pdfPage->id }}">
        <input type="hidden" name="pdf_id" value="{{ $pdfUpload->id }}">
        <input type="hidden" name="year" value="{{ $constants['year'] }}">
        <input type="hidden" name="month" value="{{ $constants['month'] }}">
        <input type="hidden" name="county" value="{{ $constants['county_name'] }}">
        <input type="hidden" name="marriage_type_id" value="{{ $pdfUpload->marriage_type_id }}">
        
        <div class="grid grid-cols-1 gap-4 mb-4">
            <!-- Marriage Type (Locked) -->
            <div class="bg-gray-50 p-3 rounded-lg">
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                    Marriage Type (Locked)
                </label>
                <p class="text-sm font-medium text-gray-900">
                    {{ $pdfUpload->marriageType->name ?? 'No Type' }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Inherited from PDF upload
                </p>
            </div>

            <!-- Certificate Serial -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Certificate Serial <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                    name="certificate_serial" 
                    x-model="formData.certificate_serial"
                    placeholder="Enter certificate serial number"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 uppercase-field"
                    required>
            </div>

            <!-- Sub County Selection -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Sub County <span class="text-red-500">*</span>
                </label>
                <select name="sub_county" 
                        x-model="formData.sub_county"
                        @change="loadWards($event.target.value)"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                        required>
                    <option value="">Select Sub County</option>
                    @foreach($subCounties as $subCounty)
                        <option value="{{ $subCounty->constituency }}">{{ $subCounty->constituency }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Ward Selection -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Ward <span class="text-gray-500">(Optional)</span>
                </label>
                <select name="ward_id" 
                        x-model="formData.ward_id"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                    <option value="">Select Ward</option>
                    <template x-for="ward in wards" :key="ward.id">
                        <option :value="ward.id" x-text="ward.wards"></option>
                    </template>
                </select>
            </div>

            <!-- Venue -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Venue <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                    name="venue" 
                    x-model="formData.venue"
                    placeholder="Enter marriage venue"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 uppercase-field"
                    required>
            </div>

            <!-- Religious Institution -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Religious Institution
                </label>
                <input type="text" 
                    name="religious_institution" 
                    x-model="formData.religious_institution"
                    placeholder="Enter religious institution name"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
            </div>

            <!-- Marriage Date -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Marriage Date <span class="text-red-500">*</span>
                </label>
                <input type="date"
                    name="marriage_date"
                    x-model="formData.marriage_date"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                    required>
            </div>

            <!-- Registration Date -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Registration Date <span class="text-red-500">*</span>
                </label>
                <input type="date"
                    name="reg_date"
                    x-model="formData.reg_date"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                    required>
            </div>

            <!-- Notes -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Notes
                </label>
                <textarea
                    name="notes"
                    x-model="formData.notes"
                    rows="3"
                    placeholder="Any additional notes..."
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                ></textarea>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" 
                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                <i class="fas fa-bolt mr-2"></i>
                Quick Create Marriage Record
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function quickEntryForm() {
    return {
        formData: {
            certificate_serial: '',
            sub_county: '',
            ward_id: '',
            venue: '',
            religious_institution: '',
            marriage_date: '{{ date('Y-m-d') }}',
            reg_date: '{{ date('Y-m-d') }}',
            notes: ''
        },
        wards: [],
        
        init() {
            this.$watch('formData.sub_county', (value) => {
                if (value) this.loadWards(value);
            });
        },
        
        async loadWards(subCounty) {
            if (!subCounty) return;
            
            try {
                const response = await fetch(`{{ route('pdf-uploads.wards') }}?constituency=${encodeURIComponent(subCounty)}&county_code={{ $constants['county_code'] }}`);
                this.wards = await response.json();
            } catch (error) {
                console.error('Error loading wards:', error);
            }
        }
    }
}
</script>
@endpush