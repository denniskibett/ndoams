{{-- resources/views/partials/table/marriages-table.blade.php --}}
<div
    x-data="{
        // Data properties
        marriages: {{ Js::from($marriages) }},
        unlinkedImages: {{ Js::from($unlinkedImages ?? []) }},
        unlinkedPdfPages: {{ Js::from($unlinkedPdfPages ?? []) }},
        
        // Filter properties
        search: '',
        filterYear: '',
        filterMonth: '',
        filterCounty: '',
        filterStatus: '',
        filterMarriageType: '',
        filterVerificationStatus: '',
        filterSource: 'all', // all, images, pdfs, direct
        
        // Pagination
        perPage: '10',
        currentPage: 1,
        totalPages: 1,
        totalItems: 0,
        
        // Sorting
        sortColumn: 'created_at',
        sortDirection: 'desc',
        
        // Filtered data
        filteredMarriages: [],
        paginatedMarriages: [],
        
        // Modal states
        activeTab: 'marriages',
        showCreateModal: false,
        showImagePreview: false,
        currentImage: null,
        
        // Month names helper
        monthNames: {
            '1': 'January', '2': 'February', '3': 'March', '4': 'April',
            '5': 'May', '6': 'June', '7': 'July', '8': 'August',
            '9': 'September', '10': 'October', '11': 'November', '12': 'December'
        },
        
        // Marriage types mapping (from categories)
        marriageTypes: {{ Js::from($marriageTypes ?? []) }},
        
        init() {
            console.log('Marriage table initialized with', this.marriages.length, 'records');
            this.applyFilters();
        },
        
        applyFilters() {
            let filtered = [...this.marriages];
            
            // Apply source filter
            if (this.filterSource !== 'all') {
                filtered = filtered.filter(m => {
                    if (this.filterSource === 'images') return m.image_id;
                    if (this.filterSource === 'pdfs') return m.pdf_id || m.pdf_page_id;
                    if (this.filterSource === 'direct') return !m.image_id && !m.pdf_id && !m.pdf_page_id;
                    return true;
                });
            }
            
            // Apply search
            if (this.search) {
                const searchTerm = this.search.toLowerCase();
                filtered = filtered.filter(m => 
                    (m.certificate_serial && m.certificate_serial.toLowerCase().includes(searchTerm)) ||
                    (m.spouses && m.spouses.some(s => s.name.toLowerCase().includes(searchTerm))) ||
                    (m.witnesses && m.witnesses.some(w => w.name.toLowerCase().includes(searchTerm))) ||
                    (m.county && m.county.toLowerCase().includes(searchTerm))
                );
            }
            
            // Apply year filter
            if (this.filterYear) {
                filtered = filtered.filter(m => m.year == this.filterYear);
            }
            
            // Apply month filter
            if (this.filterMonth) {
                filtered = filtered.filter(m => m.month == this.filterMonth);
            }
            
            // Apply county filter
            if (this.filterCounty) {
                filtered = filtered.filter(m => m.county == this.filterCounty);
            }
            
            // Apply status filter
            if (this.filterStatus) {
                filtered = filtered.filter(m => m.system_status === this.filterStatus);
            }
            
            // Apply verification status filter
            if (this.filterVerificationStatus) {
                filtered = filtered.filter(m => m.verification_status === this.filterVerificationStatus);
            }
            
            // Apply marriage type filter
            if (this.filterMarriageType) {
                filtered = filtered.filter(m => m.marriage_type_id == this.filterMarriageType);
            }
            
            // Apply sorting
            this.applySorting(filtered);
            
            this.filteredMarriages = filtered;
            this.totalItems = filtered.length;
            this.updatePagination();
        },
        
        applySorting(filtered) {
            filtered.sort((a, b) => {
                let aVal, bVal;
                
                switch(this.sortColumn) {
                    case 'certificate_serial':
                        aVal = a.certificate_serial || '';
                        bVal = b.certificate_serial || '';
                        break;
                    case 'marriage_date':
                        aVal = a.marriage_date ? new Date(a.marriage_date) : new Date(0);
                        bVal = b.marriage_date ? new Date(b.marriage_date) : new Date(0);
                        break;
                    case 'created_at':
                        aVal = a.created_at ? new Date(a.created_at) : new Date(0);
                        bVal = b.created_at ? new Date(b.created_at) : new Date(0);
                        break;
                    case 'completion_rate':
                        aVal = a.completion_rate || 0;
                        bVal = b.completion_rate || 0;
                        break;
                    default:
                        aVal = a.id;
                        bVal = b.id;
                }
                
                if (this.sortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
        },
        
        updatePagination() {
            const itemsPerPage = parseInt(this.perPage);
            const start = (this.currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            
            this.paginatedMarriages = this.filteredMarriages.slice(start, end);
            this.totalPages = Math.ceil(this.filteredMarriages.length / itemsPerPage);
            
            if (this.currentPage > this.totalPages && this.totalPages > 0) {
                this.currentPage = 1;
                this.updatePagination();
            }
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
            this.filterStatus = '';
            this.filterMarriageType = '';
            this.filterVerificationStatus = '';
            this.filterSource = 'all';
            this.sortColumn = 'created_at';
            this.sortDirection = 'desc';
            this.currentPage = 1;
            this.applyFilters();
        },
        
        get hasActiveFilters() {
            return !!(this.search || this.filterYear || this.filterMonth || 
                     this.filterCounty || this.filterStatus || this.filterMarriageType || 
                     this.filterVerificationStatus || this.filterSource !== 'all');
        },
        
        getMonthName(monthNum) {
            return this.monthNames[monthNum] || '';
        },
        
        formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            return new Date(dateStr).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric'
            });
        },
        
        getSpouseNames(marriage, type) {
            if (!marriage.spouses) return 'N/A';
            if (type === 'husband') {
                const husband = marriage.spouses.find(s => s.spouse_type === 'husband' || s.gender === 'male');
                return husband ? husband.name : 'N/A';
            }
            if (type === 'wife') {
                const wife = marriage.spouses.find(s => s.spouse_type === 'wife' || s.gender === 'female');
                return wife ? wife.name : 'N/A';
            }
            return 'N/A';
        },
        
        // Get marriage type extension data
        getExtensionField(marriage, field) {
            return marriage.extension?.[field] || 'N/A';
        },
        
        // Check if marriage has specific type
        hasExtensionType(marriage) {
            return marriage.extension && Object.keys(marriage.extension).length > 0;
        },
        
        // Get marriage type name
        getMarriageTypeName(typeId) {
            const type = this.marriageTypes.find(t => t.id == typeId);
            return type ? type.name : 'Unknown';
        },
        
        previewImage(imageUrl) {
            this.currentImage = imageUrl;
            this.showImagePreview = true;
        },
        
        getStatusClass(status) {
            const classes = {
                'Completed': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'Pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                'Approved': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'
            };
            return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
        },
        
        getVerificationClass(status) {
            const classes = {
                'Verified': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'Unverified': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                'Rejected': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
            };
            return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
        },
        
        getSourceIcon(marriage) {
            if (marriage.image_id) return '📷';
            if (marriage.pdf_id || marriage.pdf_page_id) return '📄';
            return '✏️';
        },
        
        getPageNumbers() {
            const pages = [];
            const current = this.currentPage;
            const last = this.totalPages;
            
            if (last <= 5) {
                for (let i = 1; i <= last; i++) pages.push(i);
            } else {
                if (current <= 3) {
                    pages.push(1, 2, 3, 4, '...', last);
                } else if (current >= last - 2) {
                    pages.push(1, '...', last - 3, last - 2, last - 1, last);
                } else {
                    pages.push(1, '...', current - 1, current, current + 1, '...', last);
                }
            }
            return pages;
        }
    }"
    x-init="init()"
    class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]"
>
    <!-- Tabs for different views -->
    <div class="px-4 mb-4 border-b border-gray-200 dark:border-gray-800">
        <div class="flex space-x-6">
            <button 
                @click="activeTab = 'marriages'; applyFilters()"
                :class="activeTab === 'marriages' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="pb-2 px-1 border-b-2 font-medium text-sm transition-colors"
            >
                Marriage Records (<span x-text="marriages.length"></span>)
            </button>
            
            <template x-if="unlinkedImages.length > 0">
                <button 
                    @click="activeTab = 'images'; applyFilters()"
                    :class="activeTab === 'images' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="pb-2 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    Unlinked Images (<span x-text="unlinkedImages.length"></span>)
                </button>
            </template>
            
            <template x-if="unlinkedPdfPages.length > 0">
                <button 
                    @click="activeTab = 'pdfs'; applyFilters()"
                    :class="activeTab === 'pdfs' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="pb-2 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    Unlinked PDF Pages (<span x-text="unlinkedPdfPages.length"></span>)
                </button>
            </template>
        </div>
    </div>

    <!-- Tab Content: Marriages -->
    <div x-show="activeTab === 'marriages'" x-cloak>
        <!-- Filters Section (similar to PDF table) -->
        <div class="mb-4 px-4">
            <div class="flex flex-nowrap items-start gap-4 overflow-x-auto pb-2 lg:overflow-visible lg:flex-row lg:items-center lg:justify-between">
                <!-- Left Controls - Show Entries -->
                <div class="flex shrink-0 items-center gap-2">
                    <span class="text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">Show</span>
                    <div class="relative w-20">
                        <select x-model="perPage" @change="currentPage = 1; applyFilters()" 
                                class="h-10 bg-none w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-8 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    <span class="text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">entries</span>
                </div>

                <!-- Filters -->
                <div class="flex shrink-0 items-center gap-2">
                    <!-- Source Filter -->
                    <div class="relative w-28">
                        <select x-model="filterSource" @change="currentPage = 1; applyFilters()"
                                class="h-10 bg-none w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-8 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="all">All Sources</option>
                            <option value="images">With Images</option>
                            <option value="pdfs">With PDFs</option>
                            <option value="direct">Direct Entry</option>
                        </select>
                        <span class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>

                    <!-- Year Filter -->
                    <div class="relative w-28">
                        <select x-model="filterYear" @change="currentPage = 1; applyFilters()"
                                class="h-10 bg-none w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-8 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Years</option>
                            @foreach(range(date('Y'), 2000) as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <span class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>

                    <!-- Month Filter -->
                    <div class="relative w-32">
                        <select x-model="filterMonth" @change="currentPage = 1; applyFilters()"
                                class="h-10 bg-none w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-8 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Months</option>
                            @foreach(range(1, 12) as $month)
                                <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                            @endforeach
                        </select>
                        <span class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>

                    <!-- County Filter -->
                    <div class="relative w-36 hidden md:block">
                        <select x-model="filterCounty" @change="currentPage = 1; applyFilters()"
                                class="h-10 bg-none w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-8 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Counties</option>
                            @foreach($counties ?? [] as $county)
                                <option value="{{ $county->name }}">{{ $county->name }}</option>
                            @endforeach
                        </select>
                        <span class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>

                    <!-- Marriage Type Filter -->
                    <div class="relative w-32 hidden lg:block">
                        <select x-model="filterMarriageType" @change="currentPage = 1; applyFilters()"
                                class="h-10 bg-none w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-8 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Types</option>
                            @foreach($marriageTypes ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <span class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>

                    <!-- Status Filters -->
                    <div class="relative w-32 hidden xl:block">
                        <select x-model="filterStatus" @change="currentPage = 1; applyFilters()"
                                class="h-10 bg-none w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-8 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                            <option value="Approved">Approved</option>
                        </select>
                        <span class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>

                    <!-- Verification Status Filter -->
                    <div class="relative w-32 hidden xl:block">
                        <select x-model="filterVerificationStatus" @change="currentPage = 1; applyFilters()"
                                class="h-10 bg-none w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-8 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Verification</option>
                            <option value="Verified">Verified</option>
                            <option value="Unverified">Unverified</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                        <span class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>

                    <!-- Clear Filters -->
                    <button 
                        @click="clearFilters()"
                        x-show="hasActiveFilters"
                        x-cloak
                        class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 whitespace-nowrap"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear
                    </button>
                </div>

                <!-- Search -->
                <div class="flex shrink-0 items-center gap-3">
                    <div class="relative w-64">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" 
                                    d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" 
                                    fill="currentColor"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            x-model="search"
                            @input.debounce.500ms="currentPage = 1; applyFilters()"
                            placeholder="Search by certificate, names..."
                            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-0 pl-9 pr-8 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        />
                        <button 
                            x-show="search"
                            @click="search = ''; currentPage = 1; applyFilters()"
                            class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            x-cloak
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
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
                        <button @click="search = ''; currentPage = 1; applyFilters()" class="ml-1 text-blue-600 hover:text-blue-800">✕</button>
                    </span>
                </template>
                
                <template x-if="filterSource !== 'all'">
                    <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                        Source: <span x-text="filterSource"></span>
                        <button @click="filterSource = 'all'; currentPage = 1; applyFilters()" class="ml-1 text-purple-600 hover:text-purple-800">✕</button>
                    </span>
                </template>
                
                <template x-if="filterYear">
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        Year: <span x-text="filterYear"></span>
                        <button @click="filterYear = ''; currentPage = 1; applyFilters()" class="ml-1 text-green-600 hover:text-green-800">✕</button>
                    </span>
                </template>
                
                <template x-if="filterMonth">
                    <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                        Month: <span x-text="getMonthName(filterMonth)"></span>
                        <button @click="filterMonth = ''; currentPage = 1; applyFilters()" class="ml-1 text-yellow-600 hover:text-yellow-800">✕</button>
                    </span>
                </template>
            </div>
        </div>

        <!-- Main Table -->
        <div class="max-w-full overflow-x-auto">
            <div class="min-w-[1400px]">
                <!-- Table Header -->
                <div class="grid grid-cols-12 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                    <!-- Source -->
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Type</p>
                    </div>
                    
                    <!-- Certificate -->
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                        <button @click="sortBy('certificate_serial')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Certificate</p>
                            <span class="flex flex-col gap-0.5 ml-1">
                                <svg :class="{'fill-blue-500': sortColumn === 'certificate_serial' && sortDirection === 'asc'}" 
                                      width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                                </svg>
                                <svg :class="{'fill-blue-500': sortColumn === 'certificate_serial' && sortDirection === 'desc'}" 
                                      width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                    
                    <!-- Date -->
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                        <button @click="sortBy('marriage_date')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Date</p>
                            <span class="flex flex-col gap-0.5 ml-1">
                                <svg :class="{'fill-blue-500': sortColumn === 'marriage_date' && sortDirection === 'asc'}" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                                </svg>
                                <svg :class="{'fill-blue-500': sortColumn === 'marriage_date' && sortDirection === 'desc'}" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                    
                    <!-- Couple -->
                    <div class="col-span-2 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Husband & Wife</p>
                    </div>
                    
                    <!-- County & Type -->
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">County/Type</p>
                    </div>
                    
                    <!-- Marriage Type Specific Fields -->
                    <div class="col-span-2 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Type Details</p>
                    </div>
                    
                    <!-- Status -->
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                        <button @click="sortBy('system_status')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Status</p>
                            <span class="flex flex-col gap-0.5 ml-1">
                                <svg :class="{'fill-blue-500': sortColumn === 'system_status' && sortDirection === 'asc'}" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                                </svg>
                                <svg :class="{'fill-blue-500': sortColumn === 'system_status' && sortDirection === 'desc'}" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                    
                    <!-- Verification -->
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Verification</p>
                    </div>
                    
                    <!-- Progress -->
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                        <button @click="sortBy('completion_rate')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Progress</p>
                            <span class="flex flex-col gap-0.5 ml-1">
                                <svg :class="{'fill-blue-500': sortColumn === 'completion_rate' && sortDirection === 'asc'}" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                                </svg>
                                <svg :class="{'fill-blue-500': sortColumn === 'completion_rate' && sortDirection === 'desc'}" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                    
                    <!-- Actions -->
                    <div class="col-span-1 flex items-center px-2 py-3">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Actions</p>
                    </div>
                </div>

                <!-- Table Body -->
                <template x-if="paginatedMarriages.length > 0">
                    <template x-for="marriage in paginatedMarriages" :key="marriage.id">
                        <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            <!-- Source Icon -->
                            <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                                <span class="text-xl" x-text="getSourceIcon(marriage)" :title="marriage.image_id ? 'From Image' : (marriage.pdf_id ? 'From PDF' : 'Direct Entry')"></span>
                            </div>
                            
                            <!-- Certificate -->
                            <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                                <a :href="'/marriages/' + marriage.id" class="text-sm font-medium text-gray-800 dark:text-white/90 hover:text-blue-600 truncate" x-text="marriage.certificate_serial || 'N/A'"></a>
                            </div>
                            
                            <!-- Date -->
                            <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                                <span class="text-sm text-gray-700 dark:text-gray-400" x-text="formatDate(marriage.marriage_date)"></span>
                            </div>
                            
                            <!-- Couple -->
                            <div class="col-span-2 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                                <div class="truncate">
                                    <p class="text-sm text-gray-700 dark:text-gray-400">
                                        <span class="font-medium">H:</span> <span x-text="getSpouseNames(marriage, 'husband')"></span>
                                    </p>
                                    <p class="text-sm text-gray-700 dark:text-gray-400">
                                        <span class="font-medium">W:</span> <span x-text="getSpouseNames(marriage, 'wife')"></span>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- County & Type -->
                            <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                                <div>
                                    <p class="text-sm text-gray-700 dark:text-gray-400" x-text="marriage.county || 'N/A'"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500" x-text="getMarriageTypeName(marriage.marriage_type_id)"></p>
                                </div>
                            </div>
                            
                            <!-- Marriage Type Specific Fields -->
                            <div class="col-span-2 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                                <div class="space-y-1">
                                    <!-- Civil Marriage -->
                                    <template x-if="getMarriageTypeName(marriage.marriage_type_id) === 'Civil'">
                                        <p class="text-xs text-gray-700 dark:text-gray-400">
                                            Officer: <span x-text="getExtensionField(marriage, 'marriage_officer')"></span>
                                        </p>
                                    </template>
                                    
                                    <!-- Muslim Marriage -->
                                    <template x-if="getMarriageTypeName(marriage.marriage_type_id) === 'Muslim'">
                                        <div>
                                            <p class="text-xs text-gray-700 dark:text-gray-400">
                                                Mahr: <span x-text="getExtensionField(marriage, 'mahr_agreed')"></span>
                                                <span x-show="getExtensionField(marriage, 'mahr_paid')">(Paid: <span x-text="getExtensionField(marriage, 'mahr_paid')"></span>)</span>
                                            </p>
                                            <p class="text-xs text-gray-700 dark:text-gray-400" x-show="getExtensionField(marriage, 'muslim_officer')">
                                                Officer: <span x-text="getExtensionField(marriage, 'muslim_officer')"></span>
                                            </p>
                                        </div>
                                    </template>
                                    
                                    <!-- Church Marriage -->
                                    <template x-if="getMarriageTypeName(marriage.marriage_type_id) === 'Church'">
                                        <div>
                                            <p class="text-xs text-gray-700 dark:text-gray-400">
                                                Church: <span x-text="getExtensionField(marriage, 'church_org')"></span>
                                            </p>
                                            <p class="text-xs text-gray-700 dark:text-gray-400" x-show="getExtensionField(marriage, 'pastor_name')">
                                                Pastor: <span x-text="getExtensionField(marriage, 'pastor_name')"></span>
                                            </p>
                                            <p class="text-xs text-gray-700 dark:text-gray-400" x-show="getExtensionField(marriage, 'entry_no')">
                                                Entry: <span x-text="getExtensionField(marriage, 'entry_no')"></span>
                                            </p>
                                        </div>
                                    </template>
                                    
                                    <!-- Hindu Marriage -->
                                    <template x-if="getMarriageTypeName(marriage.marriage_type_id) === 'Hindu'">
                                        <div>
                                            <p class="text-xs text-gray-700 dark:text-gray-400">
                                                Temple: <span x-text="getExtensionField(marriage, 'temple')"></span>
                                            </p>
                                            <p class="text-xs text-gray-700 dark:text-gray-400" x-show="getExtensionField(marriage, 'dowry')">
                                                Dowry: <span x-text="getExtensionField(marriage, 'dowry')"></span>
                                            </p>
                                        </div>
                                    </template>
                                    
                                    <!-- Customary Marriage (no extension) -->
                                    <template x-if="getMarriageTypeName(marriage.marriage_type_id) === 'Customary'">
                                        <p class="text-xs text-gray-400 italic">No additional details</p>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Status -->
                            <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                                <span class="text-xs inline-flex rounded-full px-2 py-1 font-medium" 
                                      :class="getStatusClass(marriage.system_status)"
                                      x-text="marriage.system_status || 'Unknown'">
                                </span>
                            </div>
                            
                            <!-- Verification -->
                            <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                                <span class="text-xs inline-flex rounded-full px-2 py-1 font-medium"
                                      :class="getVerificationClass(marriage.verification_status)"
                                      x-text="marriage.verification_status || 'Unknown'">
                                </span>
                            </div>
                            
                            <!-- Progress -->
                            <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                                <div class="w-full">
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                            <div class="h-2 rounded-full transition-all duration-300"
                                                 :class="{
                                                    'bg-green-500': marriage.completion_rate >= 80,
                                                    'bg-yellow-500': marriage.completion_rate >= 50 && marriage.completion_rate < 80,
                                                    'bg-red-500': marriage.completion_rate < 50
                                                 }"
                                                 :style="'width: ' + (marriage.completion_rate || 0) + '%'">
                                            </div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap"
                                              x-text="(marriage.completion_rate || 0) + '%'">
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="col-span-1 flex items-center px-2 py-3">
                                <div class="flex items-center gap-2">
                                    <a :href="'/marriages/' + marriage.id" 
                                       class="text-blue-600 hover:text-blue-900 dark:text-blue-400 px-2 py-1 rounded border border-blue-200 dark:border-blue-800 text-xs">
                                        View
                                    </a>
                                    <a :href="'/marriages/' + marriage.id + '/edit'" 
                                       class="text-green-600 hover:text-green-900 dark:text-green-400 px-2 py-1 rounded border border-green-200 dark:border-green-800 text-xs">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </template>
                
                <!-- Empty State -->
                <template x-if="paginatedMarriages.length === 0">
                    <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">
                        <div class="col-span-12 flex items-center justify-center px-4 py-8">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                    <span x-show="hasActiveFilters">No marriages match your filters</span>
                                    <span x-show="!hasActiveFilters">No marriages found</span>
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    <span x-show="hasActiveFilters">Try adjusting your filters or search terms</span>
                                    <span x-show="!hasActiveFilters">Get started by creating a new marriage record</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Pagination -->
        <div x-show="paginatedMarriages.length > 0" class="border-t border-gray-100 py-4 pr-4 pl-[18px] dark:border-gray-800">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between">
                <div class="mb-4 xl:mb-0">
                    <p class="text-sm text-gray-700 dark:text-gray-400">
                        Showing <span x-text="paginatedMarriages.length > 0 ? (currentPage - 1) * parseInt(perPage) + 1 : 0"></span>
                        to <span x-text="Math.min(currentPage * parseInt(perPage), totalItems)"></span>
                        of <span x-text="totalItems"></span> entries
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="currentPage--" :disabled="currentPage === 1"
                            :class="currentPage === 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-400">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <template x-for="page in getPageNumbers()" :key="page">
                        <div>
                            <button x-show="page !== '...'"
                                    @click="currentPage = page"
                                    :class="page === currentPage ? 'bg-blue-500 text-white border-blue-500' : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400'"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border text-sm font-medium transition"
                                    x-text="page">
                            </button>
                            <span x-show="page === '...'" class="flex h-9 w-9 items-center justify-center text-gray-500">...</span>
                        </div>
                    </template>

                    <button @click="currentPage++" :disabled="currentPage === totalPages"
                            :class="currentPage === totalPages ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-400">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content: Unlinked Images -->
    <div x-show="activeTab === 'images'" x-cloak class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="image in unlinkedImages" :key="image.id">
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-lg transition">
                    <div class="aspect-w-16 aspect-h-9 mb-3 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden cursor-pointer"
                         @click="previewImage(image.image_path)">
                        <img :src="image.image_path" :alt="image.name" class="object-cover w-full h-32">
                    </div>
                    <h4 class="font-medium text-gray-900 dark:text-white truncate" x-text="image.name"></h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="image.uploader_name"></p>
                    <div class="mt-3 flex justify-end">
                        <a :href="'/marriages/create-from-image/' + image.id" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                            Create Marriage
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Tab Content: Unlinked PDF Pages -->
    <div x-show="activeTab === 'pdfs'" x-cloak class="p-4">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700 dark:text-gray-400">PDF</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700 dark:text-gray-400">Page</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700 dark:text-gray-400">Uploader</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="page in unlinkedPdfPages" :key="page.id">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="py-3 px-4 text-sm text-gray-900 dark:text-white" x-text="page.pdf_upload?.name"></td>
                            <td class="py-3 px-4 text-sm text-gray-700 dark:text-gray-400">
                                <span x-text="page.page_number"></span>/<span x-text="page.pdf_upload?.total_pages"></span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700 dark:text-gray-400" x-text="page.pdf_upload?.uploader?.name"></td>
                            <td class="py-3 px-4">
                                <a :href="'/marriages/create?pdf_page_id=' + page.id" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm inline-block">
                                    Create Marriage
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div x-show="showImagePreview" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75">
        <div class="relative max-w-4xl w-full bg-white dark:bg-gray-800 rounded-lg p-4">
            <button @click="showImagePreview = false" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img :src="currentImage" class="max-w-full max-h-[80vh] mx-auto" alt="Preview">
        </div>
    </div>
</div>