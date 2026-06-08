{{-- resources/views/partials/table/marriages-admin.blade.php --}}
<div
    x-data="{
        // Data properties
        marriages: {{ Js::from($marriages) }},
        marriageTypes: {{ Js::from($categories->where('type', 'marriage_type')->values()) }},
        marriageStatuses: {{ Js::from($categories->where('type', 'marriage_status')->values()) }},
        verificationStatuses: {{ Js::from($categories->where('type', 'verification_status')->values()) }},
        counties: {{ Js::from($counties ?? []) }},
        constituencies: {{ Js::from($constituencies ?? []) }},
        wards: {{ Js::from($wards ?? []) }},
        
        // Filter properties
        search: '',
        filterYear: '',
        filterMonth: '',
        filterCounty: '',
        filterSubCounty: '',
        filterMarriageType: '',
        filterSystemStatus: '',
        perPage: '10',
        sortColumn: 'id',
        sortDirection: 'desc',
        currentPage: 1,
        totalPages: 1,
        totalItems: {{ $marriages->count() }},
        filteredItems: [],
        paginatedItems: [],
        
        // Modal properties
        isCreateModalOpen: false,
        isEditModalOpen: false,
        isDeleteModalOpen: false,
        selectedMarriage: null,
        modalError: null,
        isLoading: false,
        
        // Form data (kept for modals)
        formData: {
            id: '',
            certificate_serial: '',
            marriage_date: '',
            reg_date: '',
            venue: '',
            county: '',
            sub_county: '',
            ward_id: '',
            marriage_type_id: '',
            marriage_status_id: '',
            verification_status_id: '',
            year: new Date().getFullYear().toString(),
            month: (new Date().getMonth() + 1).toString(),
            husband_name: '',
            husband_age: '',
            husband_occupation: '',
            husband_residence: '',
            husband_father_name: '',
            husband_father_occupation: '',
            husband_father_residence: '',
            husband_mother_name: '',
            husband_mother_occupation: '',
            husband_mother_residence: '',
            wife_name: '',
            wife_age: '',
            wife_occupation: '',
            wife_residence: '',
            wife_father_name: '',
            wife_father_occupation: '',
            wife_father_residence: '',
            wife_mother_name: '',
            wife_mother_occupation: '',
            wife_mother_residence: '',
            witness1_name: '',
            witness1_side: '',
            witness2_name: '',
            witness2_side: '',
            marriage_officer: '',
            mahr_agreed: '',
            mahr_paid: '',
            mahr_deferred: '',
            gifts: '',
            muslim_officer: '',
            church_org: '',
            pastor_name: '',
            entry_no: '',
            temple: '',
            dowry: ''
        },
        
        // Routes
        storeRoute: '{{ route("marriages.store") }}',
        updateRoute: '{{ route("marriages.update", ":id") }}',
        destroyRoute: '{{ route("marriages.destroy", ":id") }}',
        showRoute: '{{ route("marriages.show", ":id") }}',
        
        // Month names
        monthNames: {
            '1': 'January', '2': 'February', '3': 'March', '4': 'April',
            '5': 'May', '6': 'June', '7': 'July', '8': 'August',
            '9': 'September', '10': 'October', '11': 'November', '12': 'December'
        },
        
        // Status color mapping
        statusColors: {
            'completed': { bg: 'bg-green-100', text: 'text-green-800', darkBg: 'dark:bg-green-900/30', darkText: 'dark:text-green-400' },
            'under_review': { bg: 'bg-yellow-100', text: 'text-yellow-800', darkBg: 'dark:bg-yellow-900/30', darkText: 'dark:text-yellow-400' },
            'in_progress': { bg: 'bg-blue-100', text: 'text-blue-800', darkBg: 'dark:bg-blue-900/30', darkText: 'dark:text-blue-400' },
            'skipped': { bg: 'bg-gray-100', text: 'text-gray-800', darkBg: 'dark:bg-gray-700', darkText: 'dark:text-gray-400' },
            'pending': { bg: 'bg-purple-100', text: 'text-purple-800', darkBg: 'dark:bg-purple-900/30', darkText: 'dark:text-purple-400' }
        },
        
        // Type color mapping
        typeColors: {
            'civil': { bg: 'bg-indigo-100', text: 'text-indigo-800', darkBg: 'dark:bg-indigo-900/30', darkText: 'dark:text-indigo-400' },
            'christian': { bg: 'bg-emerald-100', text: 'text-emerald-800', darkBg: 'dark:bg-emerald-900/30', darkText: 'dark:text-emerald-400' },
            'muslim': { bg: 'bg-amber-100', text: 'text-amber-800', darkBg: 'dark:bg-amber-900/30', darkText: 'dark:text-amber-400' },
            'hindu': { bg: 'bg-orange-100', text: 'text-orange-800', darkBg: 'dark:bg-orange-900/30', darkText: 'dark:text-orange-400' },
            'customary': { bg: 'bg-teal-100', text: 'text-teal-800', darkBg: 'dark:bg-teal-900/30', darkText: 'dark:text-teal-400' }
        },
        
        init() {
            console.log('Marriage admin table initialized with', this.marriages.length, 'records');
            this.updateSystemStatuses();
            this.applyFilters();
        },
        
        // Update system statuses for all marriages
        updateSystemStatuses() {
            this.marriages = this.marriages.map(marriage => {
                return {
                    ...marriage,
                    calculated_status: this.calculateSystemStatus(marriage)
                };
            });
        },
        
        // ==================== FILTERING & SORTING ====================
        
        get hasActiveFilters() {
            return !!(
                this.search || 
                this.filterYear || 
                this.filterMonth || 
                this.filterCounty || 
                this.filterSubCounty ||
                this.filterMarriageType ||
                this.filterSystemStatus ||
                this.perPage !== '10' ||
                this.sortColumn !== 'id' || 
                this.sortDirection !== 'desc'
            );
        },
        
        calculateSystemStatus(marriage) {
            // If manually set status exists, use it
            if (marriage.system_status && ['completed', 'skipped'].includes(marriage.system_status)) {
                return marriage.system_status;
            }
            
            // Calculate based on completion
            const hasBasicInfo = marriage.certificate_serial && marriage.marriage_date && marriage.venue && marriage.county;
            const hasSpouses = marriage.spouses && marriage.spouses.length >= 2;
            const hasWitnesses = marriage.witnesses && marriage.witnesses.length >= 2;
            const hasLocation = marriage.sub_county && marriage.ward_id;
            
            if (!hasBasicInfo || !hasSpouses || !hasWitnesses) {
                return 'in_progress';
            }
            
            if (hasBasicInfo && hasSpouses && hasWitnesses && hasLocation) {
                return 'under_review';
            }
            
            return 'pending';
        },
        
        getStatusClass(status) {
            const colors = this.statusColors[status] || this.statusColors.pending;
            return `${colors.bg} ${colors.text} ${colors.darkBg} ${colors.darkText}`;
        },
        
        getStatusText(status) {
            const statusMap = {
                'completed': 'Completed',
                'under_review': 'Under Review',
                'in_progress': 'In Progress',
                'skipped': 'Skipped',
                'pending': 'Pending'
            };
            return statusMap[status] || status;
        },
        
        getTypeClass(typeName) {
            if (!typeName) return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
            
            const normalizedName = typeName.toLowerCase();
            const colors = this.typeColors[normalizedName] || 
                          { bg: 'bg-gray-100', text: 'text-gray-800', darkBg: 'dark:bg-gray-800', darkText: 'dark:text-gray-400' };
            
            return `${colors.bg} ${colors.text} ${colors.darkBg} ${colors.darkText}`;
        },
        
        formatNameCase(str) {
            if (!str) return '';
            return str
                .toLowerCase()
                .split(' ')
                .map(word => {
                    const firstLetterIndex = word.search(/[a-zA-Z]/);
                    if (firstLetterIndex === -1) return word; // no letters in word
                    return (
                        word.substring(0, firstLetterIndex) +
                        word[firstLetterIndex].toUpperCase() +
                        word.substring(firstLetterIndex + 1)
                    );
                })
                .join(' ');
        },
        
        applyFilters() {
            this.filteredItems = this.marriages.filter(marriage => {
                let matches = true;
                
                if (this.search) {
                    const searchTerm = this.search.toLowerCase();
                    const certificateSerial = (marriage.certificate_serial || '').toLowerCase();
                    const venue = (marriage.venue || '').toLowerCase();
                    const county = (marriage.county || '').toLowerCase();
                    const subCounty = (marriage.sub_county || '').toLowerCase();
                    const husbandName = marriage.spouses?.find(s => s.spouse_type === 'husband')?.name?.toLowerCase() || '';
                    const wifeName = marriage.spouses?.find(s => s.spouse_type === 'wife')?.name?.toLowerCase() || '';
                    
                    matches = matches && (
                        certificateSerial.includes(searchTerm) ||
                        venue.includes(searchTerm) ||
                        county.includes(searchTerm) ||
                        subCounty.includes(searchTerm) ||
                        husbandName.includes(searchTerm) ||
                        wifeName.includes(searchTerm)
                    );
                }
                
                if (this.filterYear) matches = matches && (marriage.year == this.filterYear);
                if (this.filterMonth) matches = matches && (marriage.month == this.filterMonth);
                if (this.filterCounty) matches = matches && (marriage.county === this.filterCounty);
                if (this.filterSubCounty) matches = matches && (marriage.sub_county === this.filterSubCounty);
                if (this.filterMarriageType) matches = matches && (marriage.marriage_type_id == this.filterMarriageType);
                
                const calculatedStatus = this.calculateSystemStatus(marriage);
                if (this.filterSystemStatus) matches = matches && (calculatedStatus === this.filterSystemStatus);
                
                return matches;
            });
            
            this.applySorting();
            this.updatePagination();
        },
        
        applySorting() {
            this.filteredItems.sort((a, b) => {
                let aValue, bValue;
                
                switch(this.sortColumn) {
                    case 'id': aValue = a.id; bValue = b.id; break;
                    case 'certificate_serial': 
                        aValue = (a.certificate_serial || '').toLowerCase(); 
                        bValue = (b.certificate_serial || '').toLowerCase(); 
                        break;
                    case 'marriage_date': aValue = a.marriage_date || ''; bValue = b.marriage_date || ''; break;
                    case 'venue': aValue = (a.venue || '').toLowerCase(); bValue = (b.venue || '').toLowerCase(); break;
                    case 'county': aValue = (a.county || '').toLowerCase(); bValue = (b.county || '').toLowerCase(); break;
                    case 'sub_county': aValue = (a.sub_county || '').toLowerCase(); bValue = (b.sub_county || '').toLowerCase(); break;
                    default: aValue = a.id; bValue = b.id;
                }
                
                if (typeof aValue === 'string' && typeof bValue === 'string') {
                    return this.sortDirection === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                }
                return this.sortDirection === 'asc' ? (aValue > bValue ? 1 : -1) : (aValue < bValue ? 1 : -1);
            });
        },
        
        updatePagination() {
            const itemsPerPage = parseInt(this.perPage);
            const startIndex = (this.currentPage - 1) * itemsPerPage;
            this.paginatedItems = this.filteredItems.slice(startIndex, startIndex + itemsPerPage);
            this.totalPages = Math.ceil(this.filteredItems.length / itemsPerPage);
            this.totalItems = this.filteredItems.length;
        },
        
        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.updatePagination();
            }
        },
        
        getPageNumbers() {
            const pages = [];
            const current = this.currentPage;
            const last = this.totalPages;
            
            if (last <= 5) {
                for (let i = 1; i <= last; i++) pages.push(i);
            } else {
                if (current <= 3) pages.push(1, 2, 3, 4, '...', last);
                else if (current >= last - 2) pages.push(1, '...', last - 3, last - 2, last - 1, last);
                else pages.push(1, '...', current - 1, current, current + 1, '...', last);
            }
            return pages;
        },
        
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'desc';
            }
            this.applyFilters();
        },
        
        clearFilters() {
            this.search = '';
            this.filterYear = '';
            this.filterMonth = '';
            this.filterCounty = '';
            this.filterSubCounty = '';
            this.filterMarriageType = '';
            this.filterSystemStatus = '';
            this.perPage = '10';
            this.sortColumn = 'id';
            this.sortDirection = 'desc';
            this.currentPage = 1;
            this.applyFilters();
        },
        
        // ==================== UTILITY FUNCTIONS ====================

        getMonthName(monthNumber) {
            return this.monthNames[monthNumber] || monthNumber;
        },
        
        getMarriageTypeName(typeId) {
            if (!typeId) return 'N/A';
            const type = this.marriageTypes.find(t => t.id == typeId);
            return type ? type.name : 'N/A';
        },
        
        getWardName(wardId) {
            if (!wardId) return 'N/A';
            const ward = this.wards.find(w => w.id == wardId);
            return ward ? this.formatNameCase(ward.name) : 'N/A';
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        },
        
        // ==================== MODAL FUNCTIONS ====================
        
        openCreateModal() {
            this.resetForm();
            this.isCreateModalOpen = true;
            this.modalError = null;
            this.formData.marriage_date = new Date().toISOString().split('T')[0];
        },
        
        openEditModal(marriage) {
            this.selectedMarriage = marriage;
            this.resetForm();
            
            this.formData.id = marriage.id;
            this.formData.certificate_serial = marriage.certificate_serial || '';
            this.formData.marriage_date = marriage.marriage_date || '';
            this.formData.reg_date = marriage.reg_date || '';
            this.formData.venue = marriage.venue || '';
            this.formData.county = marriage.county || '';
            this.formData.sub_county = marriage.sub_county || '';
            this.formData.ward_id = marriage.ward_id || '';
            this.formData.marriage_type_id = marriage.marriage_type_id || '';
            this.formData.marriage_status_id = marriage.marriage_status_id || '';
            this.formData.verification_status_id = marriage.verification_status_id || '';
            this.formData.year = marriage.year?.toString() || new Date().getFullYear().toString();
            this.formData.month = marriage.month?.toString() || (new Date().getMonth() + 1).toString();
            
            if (marriage.spouses) {
                const husband = marriage.spouses.find(s => s.spouse_type === 'husband');
                const wife = marriage.spouses.find(s => s.spouse_type === 'wife');
                
                if (husband) {
                    this.formData.husband_name = husband.name || '';
                    this.formData.husband_age = husband.age || '';
                    this.formData.husband_occupation = husband.occupation || '';
                    this.formData.husband_residence = husband.residence || '';
                    this.formData.husband_father_name = husband.father_name || '';
                    this.formData.husband_father_occupation = husband.father_occupation || '';
                    this.formData.husband_father_residence = husband.father_residence || '';
                    this.formData.husband_mother_name = husband.mother_name || '';
                    this.formData.husband_mother_occupation = husband.mother_occupation || '';
                    this.formData.husband_mother_residence = husband.mother_residence || '';
                }
                
                if (wife) {
                    this.formData.wife_name = wife.name || '';
                    this.formData.wife_age = wife.age || '';
                    this.formData.wife_occupation = wife.occupation || '';
                    this.formData.wife_residence = wife.residence || '';
                    this.formData.wife_father_name = wife.father_name || '';
                    this.formData.wife_father_occupation = wife.father_occupation || '';
                    this.formData.wife_father_residence = wife.father_residence || '';
                    this.formData.wife_mother_name = wife.mother_name || '';
                    this.formData.wife_mother_occupation = wife.mother_occupation || '';
                    this.formData.wife_mother_residence = wife.mother_residence || '';
                }
            }
            
            if (marriage.witnesses) {
                if (marriage.witnesses[0]) {
                    this.formData.witness1_name = marriage.witnesses[0].name || '';
                    this.formData.witness1_side = marriage.witnesses[0].spouse_side || '';
                }
                if (marriage.witnesses[1]) {
                    this.formData.witness2_name = marriage.witnesses[1].name || '';
                    this.formData.witness2_side = marriage.witnesses[1].spouse_side || '';
                }
            }
            
            if (marriage.marriage_extension) {
                this.formData.marriage_officer = marriage.marriage_extension.marriage_officer || '';
                this.formData.mahr_agreed = marriage.marriage_extension.mahr_agreed || '';
                this.formData.mahr_paid = marriage.marriage_extension.mahr_paid || '';
                this.formData.mahr_deferred = marriage.marriage_extension.mahr_deferred || '';
                this.formData.gifts = marriage.marriage_extension.gifts || '';
                this.formData.muslim_officer = marriage.marriage_extension.muslim_officer || '';
                this.formData.church_org = marriage.marriage_extension.church_org || '';
                this.formData.pastor_name = marriage.marriage_extension.pastor_name || '';
                this.formData.entry_no = marriage.marriage_extension.entry_no || '';
                this.formData.temple = marriage.marriage_extension.temple || '';
                this.formData.dowry = marriage.marriage_extension.dowry || '';
            }
            
            this.isEditModalOpen = true;
            this.modalError = null;
        },
        
        openDeleteModal(marriage) {
            this.selectedMarriage = marriage;
            this.isDeleteModalOpen = true;
            this.modalError = null;
        },
        
        resetForm() {
            this.formData = {
                id: '', certificate_serial: '', marriage_date: '', reg_date: '', venue: '', county: '',
                sub_county: '', ward_id: '', marriage_type_id: '', marriage_status_id: '', verification_status_id: '',
                year: new Date().getFullYear().toString(), month: (new Date().getMonth() + 1).toString(),
                husband_name: '', husband_age: '', husband_occupation: '', husband_residence: '',
                husband_father_name: '', husband_father_occupation: '', husband_father_residence: '',
                husband_mother_name: '', husband_mother_occupation: '', husband_mother_residence: '',
                wife_name: '', wife_age: '', wife_occupation: '', wife_residence: '',
                wife_father_name: '', wife_father_occupation: '', wife_father_residence: '',
                wife_mother_name: '', wife_mother_occupation: '', wife_mother_residence: '',
                witness1_name: '', witness1_side: '', witness2_name: '', witness2_side: '',
                marriage_officer: '', mahr_agreed: '', mahr_paid: '', mahr_deferred: '', gifts: '',
                muslim_officer: '', church_org: '', pastor_name: '', entry_no: '', temple: '', dowry: ''
            };
        },
        
        showModalError(message) {
            this.modalError = message;
            setTimeout(() => { this.modalError = null; }, 5000);
        },
        
        async submitCreateForm() {
            this.isLoading = true;
            this.modalError = null;
            
            if (!this.formData.certificate_serial || !this.formData.marriage_date || !this.formData.venue || 
                !this.formData.county || !this.formData.marriage_type_id || !this.formData.husband_name || 
                !this.formData.wife_name || !this.formData.witness1_name || !this.formData.witness2_name) {
                this.showModalError('Please fill in all required fields.');
                this.isLoading = false;
                return;
            }
            
            const formData = new FormData();
            Object.keys(this.formData).forEach(key => {
                let value = this.formData[key];
                if (value && typeof value === 'string' && !['marriage_date', 'reg_date', 'year', 'month', 
                    'husband_age', 'wife_age', 'marriage_type_id', 'marriage_status_id', 'verification_status_id',
                    'witness1_side', 'witness2_side', 'ward_id'].includes(key) && !key.endsWith('_id')) {
                    value = value.toUpperCase();
                }
                formData.append(key, value || '');
            });
            formData.append('_token', '{{ csrf_token() }}');
            
            try {
                const response = await fetch(this.storeRoute, { 
                    method: 'POST', 
                    body: formData,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (response.ok && result.success) window.location.reload();
                else this.showModalError(result.message || 'Failed to create marriage record.');
            } catch (error) {
                this.showModalError('An error occurred. Please try again.');
            } finally { this.isLoading = false; }
        },
        
        async submitEditForm() {
            this.isLoading = true;
            this.modalError = null;
            
            if (!this.formData.certificate_serial || !this.formData.marriage_date || !this.formData.venue || 
                !this.formData.county || !this.formData.marriage_type_id || !this.formData.husband_name || 
                !this.formData.wife_name || !this.formData.witness1_name || !this.formData.witness2_name) {
                this.showModalError('Please fill in all required fields.');
                this.isLoading = false;
                return;
            }
            
            const formData = new FormData();
            Object.keys(this.formData).forEach(key => {
                let value = this.formData[key];
                if (value && typeof value === 'string' && !['marriage_date', 'reg_date', 'year', 'month',
                    'husband_age', 'wife_age', 'marriage_type_id', 'marriage_status_id', 'verification_status_id',
                    'witness1_side', 'witness2_side', 'ward_id'].includes(key) && !key.endsWith('_id')) {
                    value = value.toUpperCase();
                }
                formData.append(key, value || '');
            });
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PUT');
            
            try {
                const response = await fetch(this.updateRoute.replace(':id', this.formData.id), {
                    method: 'POST', 
                    body: formData,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (response.ok && result.success) window.location.reload();
                else this.showModalError(result.message || 'Failed to update marriage record.');
            } catch (error) {
                this.showModalError('An error occurred. Please try again.');
            } finally { this.isLoading = false; }
        },
        
        async submitDelete() {
            this.isLoading = true;
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'DELETE');
            
            try {
                const response = await fetch(this.destroyRoute.replace(':id', this.selectedMarriage.id), {
                    method: 'POST', 
                    body: formData,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (response.ok && result.success) window.location.reload();
                else this.showModalError(result.message || 'Failed to delete marriage record.');
            } catch (error) {
                this.showModalError('An error occurred. Please try again.');
            } finally { this.isLoading = false; }
        }
    }"
    x-init="init()"
    class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]"
>
    <!-- Table Controls -->
    <div class="mb-4 px-4">
        <div class="flex flex-wrap items-center justify-between gap-2">

            <!-- Show Entries -->
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>

                <div class="relative w-16">
                    <select
                        x-model="perPage"
                        @change="currentPage = 1; applyFilters()"
                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-2 pr-6 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <span class="text-sm text-gray-500 dark:text-gray-400">entries</span>
            </div>


            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2">

                <!-- Year -->
                <div class="relative w-24">
                    <select x-model="filterYear" @change="currentPage = 1; applyFilters()"
                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-2 pr-6 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Year</option>

                        <template x-for="year in Array.from({length: new Date().getFullYear() - 1999}, (_, i) => new Date().getFullYear() - i)" :key="year">
                            <option :value="year" x-text="year"></option>
                        </template>
                    </select>
                </div>

                <!-- Month -->
                <div class="relative w-28">
                    <select x-model="filterMonth" @change="currentPage = 1; applyFilters()"
                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-2 pr-6 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Month</option>

                        <template x-for="[num, name] in Object.entries(monthNames)" :key="num">
                            <option :value="num" x-text="name"></option>
                        </template>
                    </select>
                </div>

                <!-- County -->
                <div class="relative w-28">
                    <select x-model="filterCounty" @change="currentPage = 1; applyFilters()"
                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-2 pr-6 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">County</option>

                        <template x-for="county in counties" :key="county">
                            <option :value="county" x-text="county"></option>
                        </template>
                    </select>
                </div>

                <!-- Sub County -->
                <div class="relative w-32">
                    <select x-model="filterSubCounty" @change="currentPage = 1; applyFilters()"
                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-2 pr-6 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Sub County</option>

                        <template x-for="constituency in constituencies" :key="constituency">
                            <option :value="constituency" x-text="constituency"></option>
                        </template>
                    </select>
                </div>

                <!-- Type -->
                <div class="relative w-28">
                    <select x-model="filterMarriageType" @change="currentPage = 1; applyFilters()"
                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-2 pr-6 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Type</option>

                        <template x-for="type in marriageTypes" :key="type.id">
                            <option :value="type.id" x-text="type.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Status -->
                <div class="relative w-28">
                    <select x-model="filterSystemStatus" @change="currentPage = 1; applyFilters()"
                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-2 pr-6 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Status</option>
                        <option value="in_progress">Progress</option>
                        <option value="under_review">Review</option>
                        <option value="completed">Done</option>
                        <option value="skipped">Skipped</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <!-- Clear -->
                <button
                    x-show="hasActiveFilters"
                    x-cloak
                    @click="clearFilters()"
                    class="h-10 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                >
                    Clear
                </button>

            </div>


            <!-- Search + Add -->
            <div class="flex items-center gap-2 w-full md:w-auto">

                <div class="relative flex-1 md:w-56">
                    <input
                        type="text"
                        x-model="search"
                        x-on:input.debounce.500ms="currentPage = 1; applyFilters()"
                        placeholder="Search..."
                        class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                <button
                    @click="openCreateModal()"
                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm text-white hover:bg-blue-700"
                >
                    Add Marriage
                </button>

            </div>

        </div>
    </div>

    <!-- Active Filters Display -->
    <div x-show="hasActiveFilters" x-cloak class="mb-4 px-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">Active filters:</span>
            
            <template x-if="search">
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                    Search: "<span x-text="search"></span>"
                    <button @click="search = ''; currentPage = 1; applyFilters()" class="ml-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            
            <template x-if="filterYear">
                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                    Year: <span x-text="filterYear"></span>
                    <button @click="filterYear = ''; currentPage = 1; applyFilters()" class="ml-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            
            <template x-if="filterMonth">
                <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                    Month: <span x-text="getMonthName(filterMonth)"></span>
                    <button @click="filterMonth = ''; currentPage = 1; applyFilters()" class="ml-1 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            
            <template x-if="filterCounty">
                <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                    County: <span x-text="filterCounty"></span>
                    <button @click="filterCounty = ''; currentPage = 1; applyFilters()" class="ml-1 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            
            <template x-if="filterSubCounty">
                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                    Sub-County: <span x-text="filterSubCounty"></span>
                    <button @click="filterSubCounty = ''; currentPage = 1; applyFilters()" class="ml-1 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            
            <template x-if="filterMarriageType">
                <span class="inline-flex items-center gap-1 rounded-full bg-pink-100 px-3 py-1 text-xs font-medium text-pink-800 dark:bg-pink-900/30 dark:text-pink-400">
                    Type: <span x-text="getMarriageTypeName(filterMarriageType)"></span>
                    <button @click="filterMarriageType = ''; currentPage = 1; applyFilters()" class="ml-1 text-pink-600 hover:text-pink-800 dark:text-pink-400 dark:hover:text-pink-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            
            <template x-if="filterSystemStatus">
                <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-3 py-1 text-xs font-medium text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                    Status: <span x-text="filterSystemStatus.replace('_', ' ')"></span>
                    <button @click="filterSystemStatus = ''; currentPage = 1; applyFilters()" class="ml-1 text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
        </div>
    </div>

    <!-- Table -->
    <div class="max-w-full overflow-x-auto">
        <div class="min-w-[1400px]">
            <!-- Table Header - Adjusted column spans for better fit -->
            <div class="grid grid-cols-12 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <div class="col-span-1 px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-400 uppercase tracking-wider">ID</div>
                <div class="col-span-1 px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-400 uppercase tracking-wider">Certificate</div>
                <div class="col-span-1 px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-400 uppercase tracking-wider">Type</div>
                <div class="col-span-1 px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-400 uppercase tracking-wider">Date</div>
                <div class="col-span-2 px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-400 uppercase tracking-wider">Spouses</div>
                <div class="col-span-1 px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-400 uppercase tracking-wider">Venue</div>
                <div class="col-span-1 px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-400 uppercase tracking-wider">County</div>
                <div class="col-span-1 px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-400 uppercase tracking-wider">Sub-County</div>
                <div class="col-span-1 px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-400 uppercase tracking-wider">Ward</div>
                <div class="col-span-1 px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-400 uppercase tracking-wider">Status</div>
                <div class="col-span-1 px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-400 uppercase tracking-wider">Actions</div>
            </div>

            <!-- Table Body -->
            <template x-if="paginatedItems.length > 0">
                <template x-for="marriage in paginatedItems" :key="marriage.id">
                    <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                        <!-- ID -->
                        <div class="col-span-1 px-3 py-3">
                            <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="marriage.id"></span>
                        </div>
                        
                        <!-- Certificate Serial -->
                        <div class="col-span-1 px-3 py-3 truncate" :title="marriage.certificate_serial">
                            <span class="text-sm text-gray-900 dark:text-white" x-text="marriage.certificate_serial || 'N/A'"></span>
                        </div>
                        
                        <!-- Marriage Type with color -->
                        <div class="col-span-1 px-3 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                  :class="getTypeClass(getMarriageTypeName(marriage.marriage_type_id))"
                                  x-text="getMarriageTypeName(marriage.marriage_type_id)"></span>
                        </div>
                        
                        <!-- Marriage Date -->
                        <div class="col-span-1 px-3 py-3">
                            <span class="text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(marriage.marriage_date)"></span>
                        </div>
                        
                        <!-- Spouses - Name case -->
                        <div class="col-span-2 px-3 py-3">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                <div class="font-medium truncate" :title="marriage.spouses?.find(s => s.spouse_type === 'husband')?.name">
                                    <span x-text="formatNameCase(marriage.spouses?.find(s => s.spouse_type === 'husband')?.name || 'Husband')"></span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate" :title="marriage.spouses?.find(s => s.spouse_type === 'wife')?.name">
                                    <span x-text="formatNameCase(marriage.spouses?.find(s => s.spouse_type === 'wife')?.name || 'Wife')"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Venue - Increased width with truncation -->
                        <div class="col-span-1 px-3 py-3 truncate" :title="marriage.venue">
                            <span class="text-sm text-gray-700 dark:text-gray-300" x-text="formatNameCase(marriage.venue || 'N/A')"></span>
                        </div>
                        
                        <!-- County - Name case -->
                        <div class="col-span-1 px-3 py-3 truncate" :title="marriage.county">
                            <span class="text-sm text-gray-700 dark:text-gray-300" x-text="formatNameCase(marriage.county || 'N/A')"></span>
                        </div>
                        
                        <!-- Sub-County/Constituency - Name case -->
                        <div class="col-span-1 px-3 py-3 truncate" :title="marriage.sub_county">
                            <span class="text-sm text-gray-700 dark:text-gray-300" x-text="formatNameCase(marriage.sub_county || 'N/A')"></span>
                        </div>
                        
                        <!-- Ward - Name case -->
                        <div class="col-span-1 px-3 py-3 truncate" :title="getWardName(marriage.ward_id)">
                            <span class="text-sm text-gray-700 dark:text-gray-300" x-text="getWardName(marriage.ward_id)"></span>
                        </div>
                        
                        <!-- System Status with color -->
                        <div class="col-span-1 px-3 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                  :class="getStatusClass(calculateSystemStatus(marriage))"
                                  x-text="getStatusText(calculateSystemStatus(marriage))">
                            </span>
                        </div>
                        
                        <!-- Actions - Ellipsis Menu -->
                        <div class="col-span-1 px-3 py-3">
                            <div class="flex items-center justify-center">
                                <div x-data="{ open: false }" class="relative">
                                    <button 
                                        @click="open = !open" 
                                        class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                                    >
                                        <svg
                                            class="fill-current"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                clip-rule="evenodd"
                                                d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z"
                                                fill="currentColor"
                                            />
                                        </svg>
                                    </button>

                                    <!-- Dropdown menu -->
                                    <div 
                                        x-show="open" 
                                        @click.outside="open = false" 
                                        x-transition
                                        x-cloak
                                        class="absolute right-0 z-50 mt-2 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
                                    >
                                        <!-- View button -->
                                        <a 
                                            :href="showRoute.replace(':id', marriage.id)"
                                            class="text-xs flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                                        >
                                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M2.5 10C2.5 10 5 5 10 5C15 5 17.5 10 17.5 10C17.5 10 15 15 10 15C5 15 2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                                <path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                            </svg>
                                            View
                                        </a>
                                        
                                        <!-- Edit button -->
                                        <button 
                                            @click="openEditModal(marriage); open = false"
                                            class="text-xs flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                                        >
                                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M14.4167 2.91667L17.0833 5.58333L6.25 16.4167H3.58333V13.75L14.4167 2.91667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                                <path d="M11.6667 5L15 8.33333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                            </svg>
                                            Edit
                                        </button>
                                        
                                        <!-- Delete button -->
                                        <button 
                                            @click="openDeleteModal(marriage); open = false"
                                            class="text-xs flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left font-medium text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300"
                                        >
                                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none">
                                                <path d="M4.16675 5.83333H15.8334V15.8333C15.8334 16.7538 15.0872 17.5 14.1667 17.5H5.83341C4.91294 17.5 4.16675 16.7538 4.16675 15.8333V5.83333Z"
                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M7.5 5.83333V4.16667C7.5 3.24619 8.24619 2.5 9.16667 2.5H10.8333C11.7538 2.5 12.5 3.24619 12.5 4.16667V5.83333"
                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M8.33325 9.16667V13.3333" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M11.6667 9.16667V13.3333" stroke="currentColor" stroke-width="1.5"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </template>
            
            <!-- Empty State -->
            <template x-if="paginatedItems.length === 0">
                <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">
                    <div class="col-span-12 flex items-center justify-center px-4 py-8">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                <span x-show="hasActiveFilters">No marriage records match your filters</span>
                                <span x-show="!hasActiveFilters">No marriage records found</span>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                <span x-show="hasActiveFilters">Try adjusting your filters or search terms</span>
                                <span x-show="!hasActiveFilters">Get started by creating a new marriage record</span>
                            </p>
                            <div class="mt-4 flex flex-col sm:flex-row gap-3 justify-center">
                                <button 
                                    @click="openCreateModal()"
                                    class="inline-flex items-center justify-center rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Your First Marriage
                                </button>
                                <button 
                                    x-show="hasActiveFilters"
                                    @click="clearFilters()"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    Clear All Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Pagination - Keep existing -->
    <div x-show="paginatedItems.length > 0" class="border-t border-gray-100 py-4 pr-4 pl-[18px] dark:border-gray-800">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between">
            <div class="mb-4 xl:mb-0">
                <p class="text-sm text-gray-700 dark:text-gray-400">
                    Showing
                    <span x-text="paginatedItems.length > 0 ? (currentPage - 1) * parseInt(perPage) + 1 : 0"></span>
                    to
                    <span x-text="Math.min(currentPage * parseInt(perPage), totalItems)"></span>
                    of
                    <span x-text="totalItems"></span>
                    entries
                </p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Previous Button -->
                <button 
                    @click="changePage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-400"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <!-- Page Numbers -->
                <template x-for="page in getPageNumbers()" :key="page">
                    <div>
                        <button 
                            x-show="page !== '...'"
                            @click="changePage(page)"
                            :class="page === currentPage 
                                ? 'bg-blue-600 text-white border-blue-600' 
                                : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800'"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border text-sm font-medium transition"
                            x-text="page"
                        ></button>
                        <span 
                            x-show="page === '...'"
                            class="flex h-9 w-9 items-center justify-center text-gray-500"
                        >...</span>
                    </div>
                </template>

                <!-- Next Button -->
                <button 
                    @click="changePage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    :class="currentPage === totalPages ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-400"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Modals - Note: using 'modal' folder, not 'modals' -->
    @include('partials.modal.marriage-create-modal')
    @include('partials.modal.marriage-edit-modal')
    @include('partials.modal.marriage-delete-modal')
</div>

<style>
[x-cloak] { display: none !important; }
</style>