{{-- resources/views/partials/modal/pdf-uploads-review.blade.php --}}

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    console.log('Registering pdfUploadsReviewModal component');
    
    Alpine.data('pdfUploadsReviewModal', () => ({
        showModal: false,
        marriage: null,
        pageStatus: null,
        marriageStatus: null,
        wards: [],
        mode: 'review',
        isLoading: false,
        showPublishButton: false,
        showApproveButton: false,
        showRejectButton: false,
        formData: {
            certificate_serial: '',
            marriage_date: '',
            reg_date: '',
            venue: '',
            county: '',
            sub_county: '',
            ward_id: '',
            notes: '',
            husband: { 
                name: '', 
                age: '', 
                occupation: '', 
                residence: '', 
                marital_status: '',
                father_name: '',
                father_occupation: '',
                father_residence: '',
                mother_name: '',
                mother_occupation: '',
                mother_residence: ''
            },
            wife: { 
                name: '', 
                age: '', 
                occupation: '', 
                residence: '', 
                marital_status: '',
                father_name: '',
                father_occupation: '',
                father_residence: '',
                mother_name: '',
                mother_occupation: '',
                mother_residence: ''
            },
            witnesses: {
                witness1: { name: '', side: '' },
                witness2: { name: '', side: '' }
            },
            extension: {}
        },
        
        openModal(marriage, pageStatus, marriageStatus, wards) {
            this.marriage = marriage;
            this.pageStatus = pageStatus;
            this.marriageStatus = marriageStatus;
            this.wards = wards;
            
            // Determine which buttons to show based on role and status
            const userRole = document.querySelector('meta[name="user-role"]')?.content || '';
            
            this.showPublishButton = (userRole === 'marriage_registrar' || userRole === 'admin') && 
                                      pageStatus === 'completed' && 
                                      marriageStatus === 'Completed';
            
            this.showApproveButton = (userRole === 'marriage_teller' || userRole === 'admin') && 
                                      pageStatus === 'review_needed';
            
            this.showRejectButton = (userRole === 'marriage_teller' || userRole === 'admin') && 
                                     pageStatus === 'review_needed';
            
            this.populateFormData(marriage);
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        populateFormData(marriage) {
            // Populate main fields
            this.formData.certificate_serial = marriage.certificate_serial || '';
            this.formData.marriage_date = marriage.marriage_date || '';
            this.formData.reg_date = marriage.reg_date || '';
            this.formData.venue = marriage.venue || '';
            this.formData.county = marriage.county || '';
            this.formData.sub_county = marriage.sub_county || '';
            this.formData.ward_id = marriage.ward_id || '';
            this.formData.notes = marriage.notes || '';
            
            // Populate spouses
            if (marriage.spouses && marriage.spouses.length) {
                marriage.spouses.forEach(spouse => {
                    if (spouse.spouse_type === 'husband') {
                        this.formData.husband = {
                            name: spouse.name || '',
                            age: spouse.age || '',
                            occupation: spouse.occupation || '',
                            residence: spouse.residence || '',
                            marital_status: spouse.marital_status || '',
                            father_name: spouse.father_name || '',
                            father_occupation: spouse.father_occupation || '',
                            father_residence: spouse.father_residence || '',
                            mother_name: spouse.mother_name || '',
                            mother_occupation: spouse.mother_occupation || '',
                            mother_residence: spouse.mother_residence || ''
                        };
                    } else if (spouse.spouse_type === 'wife') {
                        this.formData.wife = {
                            name: spouse.name || '',
                            age: spouse.age || '',
                            occupation: spouse.occupation || '',
                            residence: spouse.residence || '',
                            marital_status: spouse.marital_status || '',
                            father_name: spouse.father_name || '',
                            father_occupation: spouse.father_occupation || '',
                            father_residence: spouse.father_residence || '',
                            mother_name: spouse.mother_name || '',
                            mother_occupation: spouse.mother_occupation || '',
                            mother_residence: spouse.mother_residence || ''
                        };
                    }
                });
            }
            
            // Populate witnesses
            if (marriage.witnesses && marriage.witnesses.length) {
                if (marriage.witnesses[0]) {
                    this.formData.witnesses.witness1 = {
                        name: marriage.witnesses[0].name || '',
                        side: marriage.witnesses[0].spouse_side || ''
                    };
                }
                if (marriage.witnesses[1]) {
                    this.formData.witnesses.witness2 = {
                        name: marriage.witnesses[1].name || '',
                        side: marriage.witnesses[1].spouse_side || ''
                    };
                }
            }
            
            // Populate extension
            if (marriage.marriage_extension) {
                this.formData.extension = marriage.marriage_extension;
            }
        },
        
        async submitReview(action) {
            this.isLoading = true;
            
            try {
                const response = await fetch(`/marriages/${this.marriage.id}/review`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: action,
                        data: this.formData,
                        notes: this.formData.notes
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success modal
                    if (window.showSuccessModal) {
                        window.showSuccessModal({
                            title: action === 'approve' ? 'Record Approved!' : 
                                   action === 'reject' ? 'Record Rejected' : 
                                   action === 'publish' ? 'Record Published!' : 'Success!',
                            message: result.message,
                            details: result.details || '',
                            timer: 3
                        });
                    } else {
                        alert(result.message);
                    }
                    
                    this.closeModal();
                    
                    // Reload after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    if (window.showAlertModal) {
                        window.showAlertModal({
                            type: 'error',
                            title: 'Error',
                            message: result.message || 'Failed to process request'
                        });
                    } else {
                        alert('Error: ' + (result.message || 'Failed to process request'));
                    }
                }
            } catch (error) {
                console.error('Review submission error:', error);
                if (window.showAlertModal) {
                    window.showAlertModal({
                        type: 'error',
                        title: 'Network Error',
                        message: 'Failed to connect to server. Please try again.'
                    });
                } else {
                    alert('Network error. Please try again.');
                }
            } finally {
                this.isLoading = false;
            }
        },
        
        closeModal() {
            this.showModal = false;
            document.body.style.overflow = '';
        }
    }));
});

// Make openReviewModal globally available
window.openReviewModal = function(marriage, pageStatus, marriageStatus, wards) {
    const modalEl = document.querySelector('[x-data="pdfUploadsReviewModal()"]');
    if (modalEl && modalEl.__x) {
        modalEl.__x.$data.openModal(marriage, pageStatus, marriageStatus, wards);
    } else {
        console.error('Review modal not found');
        if (window.showAlertModal) {
            window.showAlertModal({
                type: 'error',
                title: 'Modal Error',
                message: 'Review modal not available. Please refresh the page.'
            });
        } else {
            alert('Review modal not available. Please refresh the page.');
        }
    }
};
</script>
@endpush

<!-- Modal HTML -->
<div x-data="pdfUploadsReviewModal()" 
     x-show="showModal" 
     x-cloak
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-[99999] overflow-y-auto"
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" 
         x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()">
    </div>

    <!-- Modal Container -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl max-w-5xl w-full max-h-[90vh] overflow-hidden flex flex-col"
             x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">
            
            <!-- Modal Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span x-show="showApproveButton">Review Marriage Record</span>
                        <span x-show="showPublishButton">Publish Marriage Record</span>
                        <span x-show="!showApproveButton && !showPublishButton">View Marriage Record</span>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Certificate: <span x-text="formData.certificate_serial" class="font-mono"></span>
                    </p>
                </div>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Modal Body - Scrollable Form Fields -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Error Alert (if has_errors) -->
                <div x-show="marriage?.has_errors" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-medium text-red-800">This record has missing or invalid fields</p>
                            <p class="text-xs text-red-700 mt-1">Please review and complete the highlighted fields below</p>
                        </div>
                    </div>
                </div>
                
                <!-- Basic Information Section -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-gray-800">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            Basic Information
                        </h4>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Certificate No</label>
                                <input type="text" x-model="formData.certificate_serial" 
                                       class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">License No</label>
                                <input type="text" x-model="formData.license_no" 
                                       class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Marriage Date</label>
                                <input type="date" x-model="formData.marriage_date" 
                                       class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Registration Date</label>
                                <input type="date" x-model="formData.reg_date" 
                                       class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                                <input type="text" x-model="formData.venue" 
                                       class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Location Information Section -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-gray-800">
                            <i class="fas fa-location-dot text-green-500 mr-2"></i>
                            Location Information
                        </h4>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">County</label>
                                <input type="text" x-model="formData.county" readonly
                                       class="w-full rounded-lg border-gray-300 bg-gray-50 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Constituency</label>
                                <input type="text" x-model="formData.sub_county" 
                                       class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ward</label>
                                <select x-model="formData.ward_id" class="w-full rounded-lg border-gray-300">
                                    <option value="">Select Ward</option>
                                    <template x-for="ward in wards" :key="ward.id">
                                        <option :value="ward.id" x-text="ward.wards"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Spouses Information Section -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-gray-800">
                            <i class="fas fa-users text-purple-500 mr-2"></i>
                            Spouses Information
                        </h4>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Husband -->
                            <div class="space-y-3">
                                <h5 class="font-medium text-blue-700 flex items-center gap-2">
                                    <i class="fas fa-mars"></i> Husband
                                </h5>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Full Name</label>
                                    <input type="text" x-model="formData.husband.name" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Age</label>
                                    <input type="number" x-model="formData.husband.age" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Occupation</label>
                                    <input type="text" x-model="formData.husband.occupation" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Residence</label>
                                    <input type="text" x-model="formData.husband.residence" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Marital Status</label>
                                    <select x-model="formData.husband.marital_status" class="w-full rounded-lg border-gray-300">
                                        <option value="">Select</option>
                                        <option value="Bachelor">Bachelor</option>
                                        <option value="Married">Married</option>
                                        <option value="Widowed">Widowed</option>
                                        <option value="Divorced">Divorced</option>
                                    </select>
                                </div>
                                <div class="pt-2 border-t border-gray-100">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Father's Information</p>
                                    <input type="text" x-model="formData.husband.father_name" placeholder="Father's Name" 
                                           class="w-full rounded-lg border-gray-300 mb-2">
                                    <input type="text" x-model="formData.husband.father_occupation" placeholder="Father's Occupation" 
                                           class="w-full rounded-lg border-gray-300 mb-2">
                                    <input type="text" x-model="formData.husband.father_residence" placeholder="Father's Residence" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                                <div class="pt-2 border-t border-gray-100">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Mother's Information</p>
                                    <input type="text" x-model="formData.husband.mother_name" placeholder="Mother's Name" 
                                           class="w-full rounded-lg border-gray-300 mb-2">
                                    <input type="text" x-model="formData.husband.mother_occupation" placeholder="Mother's Occupation" 
                                           class="w-full rounded-lg border-gray-300 mb-2">
                                    <input type="text" x-model="formData.husband.mother_residence" placeholder="Mother's Residence" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                            </div>
                            
                            <!-- Wife -->
                            <div class="space-y-3">
                                <h5 class="font-medium text-pink-700 flex items-center gap-2">
                                    <i class="fas fa-venus"></i> Wife
                                </h5>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Full Name</label>
                                    <input type="text" x-model="formData.wife.name" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Age</label>
                                    <input type="number" x-model="formData.wife.age" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Occupation</label>
                                    <input type="text" x-model="formData.wife.occupation" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Residence</label>
                                    <input type="text" x-model="formData.wife.residence" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Marital Status</label>
                                    <select x-model="formData.wife.marital_status" class="w-full rounded-lg border-gray-300">
                                        <option value="">Select</option>
                                        <option value="Spinster">Spinster</option>
                                        <option value="Married">Married</option>
                                        <option value="Widowed">Widowed</option>
                                        <option value="Divorced">Divorced</option>
                                    </select>
                                </div>
                                <div class="pt-2 border-t border-gray-100">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Father's Information</p>
                                    <input type="text" x-model="formData.wife.father_name" placeholder="Father's Name" 
                                           class="w-full rounded-lg border-gray-300 mb-2">
                                    <input type="text" x-model="formData.wife.father_occupation" placeholder="Father's Occupation" 
                                           class="w-full rounded-lg border-gray-300 mb-2">
                                    <input type="text" x-model="formData.wife.father_residence" placeholder="Father's Residence" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                                <div class="pt-2 border-t border-gray-100">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Mother's Information</p>
                                    <input type="text" x-model="formData.wife.mother_name" placeholder="Mother's Name" 
                                           class="w-full rounded-lg border-gray-300 mb-2">
                                    <input type="text" x-model="formData.wife.mother_occupation" placeholder="Mother's Occupation" 
                                           class="w-full rounded-lg border-gray-300 mb-2">
                                    <input type="text" x-model="formData.wife.mother_residence" placeholder="Mother's Residence" 
                                           class="w-full rounded-lg border-gray-300">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Witnesses Information Section -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-gray-800">
                            <i class="fas fa-eye text-amber-500 mr-2"></i>
                            Witnesses Information
                        </h4>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Witness 1 Name</label>
                                <input type="text" x-model="formData.witnesses.witness1.name" 
                                       class="w-full rounded-lg border-gray-300">
                                <label class="block text-sm font-medium text-gray-700">Side</label>
                                <select x-model="formData.witnesses.witness1.side" class="w-full rounded-lg border-gray-300">
                                    <option value="">Select Side</option>
                                    <option value="husband">Husband's Side</option>
                                    <option value="wife">Wife's Side</option>
                                    <option value="both">Both Sides</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Witness 2 Name</label>
                                <input type="text" x-model="formData.witnesses.witness2.name" 
                                       class="w-full rounded-lg border-gray-300">
                                <label class="block text-sm font-medium text-gray-700">Side</label>
                                <select x-model="formData.witnesses.witness2.side" class="w-full rounded-lg border-gray-300">
                                    <option value="">Select Side</option>
                                    <option value="husband">Husband's Side</option>
                                    <option value="wife">Wife's Side</option>
                                    <option value="both">Both Sides</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notes Section -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-gray-800">
                            <i class="fas fa-pen-alt text-gray-500 mr-2"></i>
                            Notes
                        </h4>
                    </div>
                    <div class="p-4">
                        <textarea x-model="formData.notes" rows="3" 
                                  class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Add any notes about this review..."></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer - Action Buttons -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
                <button type="button" @click="closeModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                
                <!-- Approve Button (Teller action) -->
                <button type="button" x-show="showApproveButton" @click="submitReview('approve')" :disabled="isLoading"
                        class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                    <i class="fas fa-check mr-2"></i>
                    <span x-text="isLoading ? 'Processing...' : 'Approve & Complete'"></span>
                </button>
                
                <!-- Reject Button (Teller action) -->
                <button type="button" x-show="showRejectButton" @click="submitReview('reject')" :disabled="isLoading"
                        class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50">
                    <i class="fas fa-undo mr-2"></i>
                    <span x-text="isLoading ? 'Processing...' : 'Reject to Clerk'"></span>
                </button>
                
                <!-- Publish Button (Registrar action) -->
                <button type="button" x-show="showPublishButton" @click="submitReview('publish')" :disabled="isLoading"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                    <i class="fas fa-globe mr-2"></i>
                    <span x-text="isLoading ? 'Processing...' : 'Publish Record'"></span>
                </button>
            </div>
        </div>
    </div>
</div>