<!-- resources/views/partials/table/marriages-teller.blade.php -->
<div x-data="marriageTellerTable()" x-init="initTable()" class="space-y-5 sm:space-y-6">
    
    <!-- PDF Pages Statistics Section -->
    @if(Auth::user()->role->name === 'marriage_teller' && isset($stats['data_clerk_pdf_pages']))
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                PDF Pages Statistics
            </h3>
        </div>
        <div class="p-5 border-t border-gray-100 dark:border-gray-800 sm:p-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total PDF Pages -->
                <div class="rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-5 dark:border-blue-800 dark:from-blue-900/20 dark:to-gray-900">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500">
                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2 2V8l-6-6z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total PDF Pages</h4>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($stats['data_clerk_pdf_pages']) }}
                            </p>
                            <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                                {{ number_format($stats['linked_pdf_pages'] ?? 0) }} linked
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Completed Pages -->
                <div class="rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-white p-5 dark:border-green-800 dark:from-green-900/20 dark:to-gray-900">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500">
                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed Pages</h4>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($stats['completed_pdf_pages']) }}
                            </p>
                            <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                                {{ $stats['pdf_pages_completion_rate'] }}% complete
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Unlinked Pages -->
                <div class="rounded-xl border border-yellow-200 bg-gradient-to-br from-yellow-50 to-white p-5 dark:border-yellow-800 dark:from-yellow-900/20 dark:to-gray-900">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-yellow-500">
                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Unlinked Pages</h4>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($stats['unlinked_pdf_pages']) }}
                            </p>
                            <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">
                                {{ $stats['pending_pdf_pages'] ?? 0 }} pending
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Active Clerks -->
                <div class="rounded-xl border border-purple-200 bg-gradient-to-br from-purple-50 to-white p-5 dark:border-purple-800 dark:from-purple-900/20 dark:to-gray-900">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500">
                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13 0h-6"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Clerks</h4>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($stats['active_clerks']) }}
                            </p>
                            <p class="mt-1 text-xs text-purple-600 dark:text-purple-400">
                                {{ $stats['team_completed'] ?? 0 }} marriages completed
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Progress Bars -->
            <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
                <!-- Completion Progress -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h4 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Page Completion Progress</h4>
                    <div class="space-y-4">
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Completed Pages</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $stats['pdf_pages_completion_rate'] }}%</span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-2.5 rounded-full bg-green-500" style="width: {{ min($stats['pdf_pages_completion_rate'], 100) }}%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Linked to Marriages</span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    @if($stats['data_clerk_pdf_pages'] > 0)
                                        {{ round(($stats['linked_pdf_pages'] / $stats['data_clerk_pdf_pages']) * 100) }}%
                                    @else
                                        0%
                                    @endif
                                </span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-2.5 rounded-full bg-blue-500" 
                                    style="width: {{ $stats['data_clerk_pdf_pages'] > 0 ? round(($stats['linked_pdf_pages'] / $stats['data_clerk_pdf_pages']) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Status Distribution -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h4 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Page Status Distribution</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-green-500"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Completed</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $stats['completed_pdf_pages'] }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-yellow-500"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Pending</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $stats['pending_pdf_pages'] ?? 0 }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-red-500"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Unlinked</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $stats['unlinked_pdf_pages'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Data Tables Section -->
    @if(count($marriages) > 0 || (isset($unlinkedImages) && count($unlinkedImages) > 0) || (isset($unlinkedPdfPages) && count($unlinkedPdfPages) > 0))
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Marriage Records Management
                    </h3>
                    
                    <!-- Table Navigation -->
                    <div class="flex items-center gap-2">
                        <button
                            @click="activeTab = 'marriages'"
                            :class="activeTab === 'marriages' 
                                ? 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/15 dark:text-blue-400 dark:ring-blue-500/20' 
                                : 'bg-white text-gray-700 ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700'"
                            class="inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-medium shadow-theme-xs ring-1 transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            Marriages
                        </button>
                        
                        @if(isset($unlinkedImages) && count($unlinkedImages) > 0)
                        <button
                            @click="activeTab = 'images'"
                            :class="activeTab === 'images' 
                                ? 'bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/15 dark:text-yellow-400 dark:ring-yellow-500/20' 
                                : 'bg-white text-gray-700 ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700'"
                            class="inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-medium shadow-theme-xs ring-1 transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Images
                        </button>
                        @endif
                        
                        @if(isset($unlinkedPdfPages) && count($unlinkedPdfPages) > 0)
                        <button
                            @click="activeTab = 'pdfs'"
                            :class="activeTab === 'pdfs' 
                                ? 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/15 dark:text-red-400 dark:ring-red-500/20' 
                                : 'bg-white text-gray-700 ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700'"
                            class="inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-medium shadow-theme-xs ring-1 transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            PDF Pages
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-100 dark:border-gray-800">
                <!-- Tab Content - Marriage Records -->
                <div x-show="activeTab === 'marriages'" class="p-5 sm:p-6" x-cloak>
                    @php
                        $marriagesData = $marriages->map(function($marriage) {
                            // Calculate completion rate
                            $completionRate = 0;
                            $totalFields = 0;
                            $completedFields = 0;

                            $basicFields = ['certificate_serial', 'marriage_date', 'reg_date', 'venue'];
                            $totalFields += count($basicFields);
                            foreach ($basicFields as $field) {
                                if (!empty($marriage->$field)) {
                                    $completedFields++;
                                }
                            }

                            $locationFields = ['county', 'sub_county'];
                            $totalFields += count($locationFields);
                            foreach ($locationFields as $field) {
                                if (!empty($marriage->$field)) {
                                    $completedFields++;
                                }
                            }

                            $totalFields += 2;
                            $completedFields += min($marriage->spouses->count(), 2);

                            $totalFields += 2;
                            $completedFields += min($marriage->witnesses->count(), 2);

                            $completionRate = $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;

                            $verificationStatus = $marriage->verification_status ?? 'Unverified';
                            if ($marriage->verificationStatus && $marriage->verificationStatus->name) {
                                $verificationStatus = $marriage->verificationStatus->name;
                            }

                            $marriageDate = $marriage->marriage_date ? $marriage->marriage_date->format('Y-m-d') : null;
                            
                            $regDate = null;
                            if ($marriage->reg_date) {
                                if (is_string($marriage->reg_date)) {
                                    try {
                                        $regDate = \Carbon\Carbon::parse($marriage->reg_date)->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        $regDate = $marriage->reg_date;
                                    }
                                } elseif ($marriage->reg_date instanceof \Carbon\Carbon) {
                                    $regDate = $marriage->reg_date->format('Y-m-d');
                                }
                            }

                            return [
                                'id' => $marriage->id,
                                'certificate_serial' => $marriage->certificate_serial ?? '',
                                'marriage_date' => $marriageDate,
                                'reg_date' => $regDate,
                                'venue' => $marriage->venue ?? '',
                                'county' => $marriage->county ?? '',
                                'sub_county' => $marriage->sub_county ?? '',
                                'system_status' => $marriage->system_status ?? 'Pending',
                                'verification_status' => $verificationStatus,
                                'created_by_name' => $marriage->createdBy->name ?? 'System',
                                'created_by_role' => $marriage->createdBy->role->name ?? 'Unknown',
                                'spouses_count' => $marriage->spouses->count(),
                                'witnesses_count' => $marriage->witnesses->count(),
                                'completion_rate' => $completionRate,
                                'marriage_type' => $marriage->marriageType ? $marriage->marriageType->name : null,
                                'has_image' => $marriage->image_id ? true : false,
                                'has_pdf' => $marriage->pdf_id ? true : false,
                                'created_at' => $marriage->created_at ? $marriage->created_at->format('Y-m-d H:i:s') : null
                            ];
                        })->values()->toArray();
                    @endphp
                    
                    <!-- Marriage Records Table -->
                    <div x-data="{
                        marriages: {{ json_encode($marriagesData) }},
                        filteredMarriages: [],
                        searchQuery: '',
                        statusFilter: 'all',
                        sortColumn: 'created_at',
                        sortDirection: 'desc',
                        currentPage: 1,
                        pageSize: 10,
                        
                        init() {
                            console.log('Initializing marriage table with data:', this.marriages.length);
                            this.filterTable();
                        },
                        
                        get pagedMarriages() {
                            if (this.pageSize === 0) {
                                return this.filteredMarriages;
                            }
                            const start = (this.currentPage - 1) * this.pageSize;
                            const end = start + this.pageSize;
                            return this.filteredMarriages.slice(start, end);
                        },
                        
                        get totalPages() {
                            if (this.pageSize === 0) return 1;
                            return Math.ceil(this.filteredMarriages.length / this.pageSize);
                        },
                        
                        filterTable() {
                            console.log('Filtering marriage table...');
                            let filtered = [...this.marriages];
                            
                            if (this.searchQuery) {
                                const query = this.searchQuery.toLowerCase();
                                filtered = filtered.filter(marriage => 
                                    (marriage.certificate_serial && marriage.certificate_serial.toLowerCase().includes(query)) ||
                                    (marriage.venue && marriage.venue.toLowerCase().includes(query)) ||
                                    (marriage.county && marriage.county.toLowerCase().includes(query)) ||
                                    (marriage.created_by_name && marriage.created_by_name.toLowerCase().includes(query))
                                );
                            }
                            
                            if (this.statusFilter !== 'all') {
                                filtered = filtered.filter(marriage => {
                                    switch(this.statusFilter) {
                                        case 'pending': return marriage.system_status === 'Pending';
                                        case 'completed': return marriage.system_status === 'Completed';
                                        case 'verified': return marriage.verification_status === 'Verified';
                                        case 'unverified': return marriage.verification_status === 'Unverified';
                                        default: return true;
                                    }
                                });
                            }
                            
                            filtered.sort((a, b) => {
                                let aValue = a[this.sortColumn];
                                let bValue = b[this.sortColumn];
                                
                                if (this.sortColumn === 'marriage_date' || this.sortColumn === 'created_at') {
                                    aValue = aValue ? new Date(aValue) : new Date(0);
                                    bValue = bValue ? new Date(bValue) : new Date(0);
                                }
                                
                                if (aValue === null || aValue === undefined) aValue = '';
                                if (bValue === null || bValue === undefined) bValue = '';
                                
                                if (this.sortDirection === 'asc') {
                                    return aValue > bValue ? 1 : -1;
                                } else {
                                    return aValue < bValue ? 1 : -1;
                                }
                            });
                            
                            this.filteredMarriages = filtered;
                            this.currentPage = 1;
                            console.log('Filtered marriages:', this.filteredMarriages.length);
                        },
                        
                        sortBy(column) {
                            if (this.sortColumn === column) {
                                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                            } else {
                                this.sortColumn = column;
                                this.sortDirection = 'asc';
                            }
                            this.filterTable();
                        },
                        
                        formatDate(dateString) {
                            if (!dateString) return '';
                            try {
                                const date = new Date(dateString);
                                return date.toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'short', 
                                    day: 'numeric' 
                                });
                            } catch (e) {
                                return dateString;
                            }
                        }
                    }" x-init="init()">
                        <!-- Table Controls -->
                        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                <!-- Search -->
                                <div class="relative">
                                    <input 
                                        x-model="searchQuery"
                                        @keyup.debounce.300ms="filterTable()"
                                        type="text"
                                        placeholder="Search marriages..."
                                        class="w-full sm:w-64 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                    <svg class="absolute right-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.16667 3.33334C5.94501 3.33334 3.33334 5.94501 3.33334 9.16668C3.33334 12.3883 5.94501 15 9.16667 15C12.3883 15 15 12.3883 15 9.16668C15 5.94501 12.3883 3.33334 9.16667 3.33334ZM1.66667 9.16668C1.66667 5.02455 5.02455 1.66668 9.16667 1.66668C13.3088 1.66668 16.6667 5.02455 16.6667 9.16668C16.6667 13.3088 13.3088 16.6667 9.16667 16.6667C5.02455 16.6667 1.66667 13.3088 1.66667 9.16668Z" fill="#9CA3AF"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.2858 13.2857C13.6112 12.9603 14.1388 12.9603 14.4642 13.2857L18.0892 16.9107C18.4147 17.2361 18.4147 17.7638 18.0892 18.0892C17.7638 18.4147 17.2362 18.4147 16.9108 18.0892L13.2858 14.4642C12.9603 14.1388 12.9603 13.6112 13.2858 13.2857Z" fill="#9CA3AF"/>
                                    </svg>
                                </div>

                                <!-- Status Filter -->
                                <select 
                                    x-model="statusFilter"
                                    @change="filterTable()"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                >
                                    <option value="all">All Records</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="completed">Completed</option>
                                    <option value="verified">Verified</option>
                                    <option value="unverified">Unverified</option>
                                </select>

                                <!-- Page Size -->
                                <select 
                                    x-model="pageSize"
                                    @change="currentPage = 1"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                >
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                    <option value="0">Show All</option>
                                </select>
                            </div>

                            <!-- Stats -->
                            <div class="flex items-center gap-2">
                                <span class="bg-blue-50 text-blue-700 text-theme-xs dark:bg-blue-500/15 dark:text-blue-400 inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-medium">
                                    Total: <span x-text="marriages.length"></span>
                                </span>
                                <span class="bg-green-50 text-green-700 text-theme-xs dark:bg-green-500/15 dark:text-green-400 inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-medium">
                                    Showing: <span x-text="pagedMarriages.length"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="max-w-full overflow-x-auto custom-scrollbar">
                                <table class="w-full min-w-[1102px]">
                                    <thead>
                                        <tr class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                                            <th class="px-5 py-3 text-left">
                                                <button @click="sortBy('certificate_serial')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Certificate
                                                    <svg :class="{'rotate-180': sortColumn === 'certificate_serial' && sortDirection === 'desc'}" 
                                                          class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <button @click="sortBy('marriage_date')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Marriage Date
                                                    <svg :class="{'rotate-180': sortColumn === 'marriage_date' && sortDirection === 'desc'}" 
                                                          class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Details
                                                </p>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Status
                                                </p>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Progress
                                                </p>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Actions
                                                </p>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="filteredMarriages.length === 0">
                                            <tr>
                                                <td colspan="6" class="px-5 py-8 text-center">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <svg class="h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                            <span x-show="marriages.length === 0">No marriage records found.</span>
                                                            <span x-show="marriages.length > 0 && filteredMarriages.length === 0">No records match your search or filter.</span>
                                                        </p>
                                                        <p x-show="searchQuery || statusFilter !== 'all'" class="text-xs text-gray-400 dark:text-gray-500">
                                                            Try adjusting your search or filter
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        
                                        <template x-for="marriage in pagedMarriages" :key="marriage.id">
                                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/50">
                                                <!-- Certificate -->
                                                <td class="px-5 py-4">
                                                    <div>
                                                        <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            <template x-if="marriage.certificate_serial">
                                                                <span x-text="marriage.certificate_serial" class="font-mono"></span>
                                                            </template>
                                                            <template x-if="!marriage.certificate_serial">
                                                                <span class="text-gray-400 italic">Not entered</span>
                                                            </template>
                                                        </span>
                                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            <span x-text="marriage.created_by_name"></span>
                                                        </span>
                                                    </div>
                                                </td>
                                                
                                                <!-- Marriage Date -->
                                                <td class="px-5 py-4">
                                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                        <template x-if="marriage.marriage_date">
                                                            <span x-text="formatDate(marriage.marriage_date)"></span>
                                                        </template>
                                                        <template x-if="!marriage.marriage_date">
                                                            <span class="text-gray-400 italic">Not set</span>
                                                        </template>
                                                    </p>
                                                </td>
                                                
                                                <!-- Details -->
                                                <td class="px-5 py-4">
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                            <span x-text="marriage.venue || 'No venue'"></span>
                                                        </span>
                                                        <div class="flex items-center gap-2">
                                                            <span class="bg-blue-50 text-blue-700 text-theme-xs dark:bg-blue-500/15 dark:text-blue-400 inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-medium"
                                                                  x-show="marriage.has_image">
                                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                </svg>
                                                                Image
                                                            </span>
                                                            <span class="bg-red-50 text-red-700 text-theme-xs dark:bg-red-500/15 dark:text-red-400 inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-medium"
                                                                  x-show="marriage.has_pdf">
                                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                                </svg>
                                                                PDF
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                
                                                <!-- Status -->
                                                <td class="px-5 py-4">
                                                    <div class="flex flex-wrap gap-1">
                                                        <span class="px-2 py-1 text-xs rounded-full font-medium"
                                                              :class="{
                                                                'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400': marriage.system_status === 'Completed',
                                                                'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400': marriage.system_status === 'Pending',
                                                                'bg-gray-50 text-gray-700 dark:bg-gray-500/15 dark:text-gray-400': !['Completed', 'Pending'].includes(marriage.system_status)
                                                              }"
                                                              x-text="marriage.system_status || 'Unknown'">
                                                        </span>
                                                        
                                                        <span class="px-2 py-1 text-xs rounded-full font-medium"
                                                              :class="{
                                                                'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400': marriage.verification_status === 'Verified',
                                                                'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400': marriage.verification_status === 'Unverified',
                                                                'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400': marriage.verification_status === 'Rejected',
                                                                'bg-gray-50 text-gray-700 dark:bg-gray-500/15 dark:text-gray-400': !['Verified', 'Unverified', 'Rejected'].includes(marriage.verification_status)
                                                              }"
                                                              x-text="marriage.verification_status || 'Unknown'">
                                                        </span>
                                                    </div>
                                                </td>
                                                
                                                <!-- Progress -->
                                                <td class="px-5 py-4">
                                                    <div>
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                                                <div class="h-2 rounded-full transition-all duration-300"
                                                                     :class="{
                                                                        'bg-green-500': marriage.completion_rate >= 80,
                                                                        'bg-yellow-500': marriage.completion_rate >= 50 && marriage.completion_rate < 80,
                                                                        'bg-red-500': marriage.completion_rate < 50
                                                                     }"
                                                                     :style="'width: ' + Math.min(marriage.completion_rate, 100) + '%'">
                                                                </div>
                                                            </div>
                                                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300"
                                                                  x-text="marriage.completion_rate + '%'">
                                                            </span>
                                                        </div>
                                                        <p class="text-gray-500 text-theme-xs dark:text-gray-400">
                                                            <span x-text="marriage.spouses_count"></span> spouses • 
                                                            <span x-text="marriage.witnesses_count"></span> witnesses
                                                        </p>
                                                    </div>
                                                </td>
                                                
                                                <!-- Actions -->
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center gap-2">
                                                        <a :href="'/marriages/' + marriage.id"
                                                           class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                                                            View
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                            </svg>
                                                        </a>
                                                        
                                                        <template x-if="marriage.system_status === 'Pending' || marriage.system_status === 'Pending Review'">
                                                            <a :href="'/marriages/' + marriage.id + '/edit'"
                                                               class="inline-flex items-center gap-1 rounded-lg bg-yellow-50 px-3 py-1.5 text-sm font-medium text-yellow-700 shadow-theme-xs ring-1 ring-yellow-200 transition hover:bg-yellow-100 dark:bg-yellow-500/15 dark:text-yellow-400 dark:ring-yellow-500/20">
                                                                Edit
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div x-show="filteredMarriages.length > 0 && pageSize > 0" class="flex items-center justify-between border-t border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
                                <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                                    <span>Showing</span>
                                    <span x-text="(currentPage - 1) * pageSize + 1"></span>
                                    <span>to</span>
                                    <span x-text="Math.min(currentPage * pageSize, filteredMarriages.length)"></span>
                                    <span>of</span>
                                    <span x-text="filteredMarriages.length"></span>
                                    <span>entries</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="currentPage--"
                                        :disabled="currentPage === 1"
                                        :class="currentPage === 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                        Previous
                                    </button>
                                    <div class="flex items-center gap-1">
                                        <template x-for="page in totalPages">
                                            <button
                                                @click="currentPage = page"
                                                :class="currentPage === page 
                                                    ? 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/15 dark:text-blue-400 dark:ring-blue-500/20' 
                                                    : 'bg-white text-gray-700 ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700'"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium shadow-theme-xs ring-1 transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                                                x-text="page"
                                            ></button>
                                        </template>
                                    </div>
                                    <button
                                        @click="currentPage++"
                                        :disabled="currentPage === totalPages"
                                        :class="currentPage === totalPages ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                    >
                                        Next
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unlinked Images Tab -->
                @if(isset($unlinkedImages) && count($unlinkedImages) > 0)
                <div x-show="activeTab === 'images'" class="p-5 sm:p-6" x-cloak>
                    @php
                        $imagesData = $unlinkedImages->map(function($image) {
                            return [
                                'id' => $image->id,
                                'name' => $image->name ?? 'Unnamed Image',
                                'certificate_serial' => $image->certificate_serial ?? '',
                                'image_path' => $image->image_path ? Storage::url($image->image_path) : null,
                                'uploader_name' => $image->uploader->name ?? 'Unknown',
                                'uploader_role' => $image->uploader->role->name ?? 'Unknown Role',
                                'created_at' => $image->created_at ? $image->created_at->format('Y-m-d H:i:s') : null,
                                'formatted_date' => $image->created_at ? $image->created_at->format('M d, Y') : '',
                                'formatted_time' => $image->created_at ? $image->created_at->format('h:i A') : '',
                                'file_size' => $image->file_size ?? '0'
                            ];
                        })->values()->toArray();
                    @endphp
                    
                    <div x-data="{
                        images: {{ json_encode($imagesData) }},
                        filteredImages: [],
                        searchQuery: '',
                        sortColumn: 'created_at',
                        sortDirection: 'desc',
                        currentPage: 1,
                        pageSize: 10,
                        
                        init() {
                            console.log('Initializing image table with:', this.images.length, 'records');
                            this.filteredImages = [...this.images];
                            this.filterTable();
                        },
                        
                        get pagedImages() {
                            if (this.pageSize === 0) {
                                return this.filteredImages;
                            }
                            const start = (this.currentPage - 1) * this.pageSize;
                            const end = start + this.pageSize;
                            return this.filteredImages.slice(start, end);
                        },
                        
                        get totalPages() {
                            if (this.pageSize === 0) return 1;
                            return Math.ceil(this.filteredImages.length / this.pageSize);
                        },
                        
                        filterTable() {
                            let filtered = [...this.images];
                            
                            if (this.searchQuery) {
                                const query = this.searchQuery.toLowerCase();
                                filtered = filtered.filter(image => 
                                    (image.name && image.name.toLowerCase().includes(query)) ||
                                    (image.certificate_serial && image.certificate_serial.toLowerCase().includes(query)) ||
                                    (image.uploader_name && image.uploader_name.toLowerCase().includes(query))
                                );
                            }
                            
                            filtered.sort((a, b) => {
                                let aValue = a[this.sortColumn];
                                let bValue = b[this.sortColumn];
                                
                                if (this.sortColumn === 'created_at') {
                                    aValue = aValue ? new Date(aValue) : new Date(0);
                                    bValue = bValue ? new Date(bValue) : new Date(0);
                                }
                                
                                if (aValue === null || aValue === undefined) aValue = '';
                                if (bValue === null || bValue === undefined) bValue = '';
                                
                                if (this.sortDirection === 'asc') {
                                    return aValue > bValue ? 1 : -1;
                                } else {
                                    return aValue < bValue ? 1 : -1;
                                }
                            });
                            
                            this.filteredImages = filtered;
                            this.currentPage = 1;
                        },
                        
                        sortBy(column) {
                            if (this.sortColumn === column) {
                                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                            } else {
                                this.sortColumn = column;
                                this.sortDirection = 'asc';
                            }
                            this.filterTable();
                        },
                        
                        previewImage(imageUrl) {
                            window.open(imageUrl, '_blank');
                        }
                    }" x-init="init()">
                        <!-- Table Controls -->
                        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                <!-- Search -->
                                <div class="relative">
                                    <input 
                                        x-model="searchQuery"
                                        @keyup.debounce.300ms="filterTable()"
                                        type="text"
                                        placeholder="Search images..."
                                        class="w-full sm:w-64 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                    <svg class="absolute right-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.16667 3.33334C5.94501 3.33334 3.33334 5.94501 3.33334 9.16668C3.33334 12.3883 5.94501 15 9.16667 15C12.3883 15 15 12.3883 15 9.16668C15 5.94501 12.3883 3.33334 9.16667 3.33334ZM1.66667 9.16668C1.66667 5.02455 5.02455 1.66668 9.16667 1.66668C13.3088 1.66668 16.6667 5.02455 16.6667 9.16668C16.6667 13.3088 13.3088 16.6667 9.16667 16.6667C5.02455 16.6667 1.66667 13.3088 1.66667 9.16668Z" fill="#9CA3AF"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.2858 13.2857C13.6112 12.9603 14.1388 12.9603 14.4642 13.2857L18.0892 16.9107C18.4147 17.2361 18.4147 17.7638 18.0892 18.0892C17.7638 18.4147 17.2362 18.4147 16.9108 18.0892L13.2858 14.4642C12.9603 14.1388 12.9603 13.6112 13.2858 13.2857Z" fill="#9CA3AF"/>
                                    </svg>
                                </div>

                                <!-- Page Size -->
                                <select 
                                    x-model="pageSize"
                                    @change="currentPage = 1"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                >
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                    <option value="0">Show All</option>
                                </select>
                            </div>

                            <!-- Stats -->
                            <div class="flex items-center gap-2">
                                <span class="bg-yellow-50 text-yellow-700 text-theme-xs dark:bg-yellow-500/15 dark:text-yellow-400 inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-medium">
                                    Total: <span x-text="images.length"></span>
                                </span>
                                <span class="bg-green-50 text-green-700 text-theme-xs dark:bg-green-500/15 dark:text-green-400 inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-medium">
                                    Showing: <span x-text="pagedImages.length"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="max-w-full overflow-x-auto custom-scrollbar">
                                <table class="w-full min-w-[1102px]">
                                    <thead>
                                        <tr class="border-b border-gray-100 bg-yellow-50 dark:border-gray-800 dark:bg-yellow-900/10">
                                            <th class="px-5 py-3 text-left">
                                                <button @click="sortBy('name')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Image Preview
                                                    <svg :class="{'rotate-180': sortColumn === 'name' && sortDirection === 'desc'}" 
                                                          class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <button @click="sortBy('certificate_serial')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Certificate Details
                                                    <svg :class="{'rotate-180': sortColumn === 'certificate_serial' && sortDirection === 'desc'}" 
                                                          class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <button @click="sortBy('uploader_name')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Upload Details
                                                    <svg :class="{'rotate-180': sortColumn === 'uploader_name' && sortDirection === 'desc'}" 
                                                          class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <button @click="sortBy('created_at')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Date & Size
                                                    <svg :class="{'rotate-180': sortColumn === 'created_at' && sortDirection === 'desc'}" 
                                                          class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Actions
                                                </p>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="filteredImages.length === 0">
                                            <tr>
                                                <td colspan="5" class="px-5 py-8 text-center">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <svg class="h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                            <span x-show="images.length === 0">No unlinked images found.</span>
                                                            <span x-show="images.length > 0 && filteredImages.length === 0">No images match your search.</span>
                                                        </p>
                                                        <p x-show="searchQuery" class="text-xs text-gray-400 dark:text-gray-500">
                                                            Try adjusting your search
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        
                                        <template x-for="image in pagedImages" :key="image.id">
                                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/50">
                                                <!-- Image Preview -->
                                                <td class="px-5 py-4">
                                                    <div class="relative w-20 h-20">
                                                        <template x-if="image.image_path">
                                                            <img :src="image.image_path" 
                                                                 :alt="image.name"
                                                                 class="w-full h-full object-cover rounded-lg border-2 border-gray-300 dark:border-gray-600">
                                                        </template>
                                                        <template x-if="!image.image_path">
                                                            <div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                                                                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                </svg>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </td>
                                                
                                                <!-- Certificate Details -->
                                                <td class="px-5 py-4">
                                                    <div>
                                                        <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            <span x-text="image.name"></span>
                                                        </span>
                                                        <template x-if="image.certificate_serial">
                                                            <span class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                                Serial: <span x-text="image.certificate_serial" class="font-mono"></span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                </td>
                                                
                                                <!-- Upload Details -->
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                            <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <span class="block text-sm font-medium text-gray-900 dark:text-white">
                                                                <span x-text="image.uploader_name"></span>
                                                            </span>
                                                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                                                <span x-text="image.uploader_role"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                
                                                <!-- Date & Size -->
                                                <td class="px-5 py-4">
                                                    <div>
                                                        <span class="block text-sm text-gray-900 dark:text-white">
                                                            <span x-text="image.formatted_date"></span>
                                                        </span>
                                                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                                                            <span x-text="image.formatted_time"></span>
                                                        </span>
                                                        <span class="block text-xs text-gray-400 dark:text-gray-500">
                                                            <span x-text="image.file_size"></span> KB
                                                        </span>
                                                    </div>
                                                </td>
                                                
                                                <!-- Actions -->
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center gap-2">
                                                        <a :href="'/marriages/create-from-image/' + image.id" 
                                                           class="inline-flex items-center gap-1 rounded-lg bg-green-50 px-3 py-1.5 text-sm font-medium text-green-700 shadow-theme-xs ring-1 ring-green-200 transition hover:bg-green-100 dark:bg-green-500/15 dark:text-green-400 dark:ring-green-500/20">
                                                            Create
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                        </a>
                                                        <template x-if="image.image_path">
                                                            <button @click="previewImage(image.image_path)" 
                                                                    class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700 shadow-theme-xs ring-1 ring-blue-200 transition hover:bg-blue-100 dark:bg-blue-500/15 dark:text-blue-400 dark:ring-blue-500/20">
                                                                Preview
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                                </svg>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div x-show="filteredImages.length > 0 && pageSize > 0" class="flex items-center justify-between border-t border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
                                <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                                    <span>Showing</span>
                                    <span x-text="(currentPage - 1) * pageSize + 1"></span>
                                    <span>to</span>
                                    <span x-text="Math.min(currentPage * pageSize, filteredImages.length)"></span>
                                    <span>of</span>
                                    <span x-text="filteredImages.length"></span>
                                    <span>entries</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="currentPage--"
                                        :disabled="currentPage === 1"
                                        :class="currentPage === 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                        Previous
                                    </button>
                                    <div class="flex items-center gap-1">
                                        <template x-for="page in totalPages">
                                            <button
                                                @click="currentPage = page"
                                                :class="currentPage === page 
                                                    ? 'bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/15 dark:text-yellow-400 dark:ring-yellow-500/20' 
                                                    : 'bg-white text-gray-700 ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700'"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium shadow-theme-xs ring-1 transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                                                x-text="page"
                                            ></button>
                                        </template>
                                    </div>
                                    <button
                                        @click="currentPage++"
                                        :disabled="currentPage === totalPages"
                                        :class="currentPage === totalPages ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                    >
                                        Next
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Unlinked PDF Pages Tab -->
                @if(isset($unlinkedPdfPages) && count($unlinkedPdfPages) > 0)
                <div x-show="activeTab === 'pdfs'" class="p-5 sm:p-6" x-cloak>
                    @php
                        $pdfPagesData = $unlinkedPdfPages->map(function($page) {
                            $pdf = $page->pdfUpload;
                            return [
                                'id' => $page->id,
                                'pdf_id' => $pdf->id,
                                'pdf_name' => $pdf->name ?? 'PDF Document',
                                'year' => $pdf->year ?? '',
                                'month' => str_pad($pdf->month ?? '', 2, '0', STR_PAD_LEFT),
                                'page_number' => $page->page_number,
                                'total_pages' => $pdf->total_pages ?? '?',
                                'status' => $page->status,
                                'uploader_name' => $pdf->uploader->name ?? 'Unknown',
                                'uploader_role' => $pdf->uploader->role->name ?? 'Unknown Role',
                                'assigned_to' => $page->assignedUser->name ?? null,
                                'created_at' => $page->created_at ? $page->created_at->format('Y-m-d H:i:s') : null,
                                'status_colors' => [
                                    'completed' => 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400',
                                    'processing' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
                                    'pending' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400'
                                ]
                            ];
                        })->values()->toArray();
                    @endphp
                    
                    <div x-data="{
                        pdfPages: {{ json_encode($pdfPagesData) }},
                        filteredPdfPages: [],
                        searchQuery: '',
                        statusFilter: 'all',
                        sortColumn: 'page_number',
                        sortDirection: 'asc',
                        currentPage: 1,
                        pageSize: 10,
                        
                        init() {
                            console.log('Initializing PDF table with:', this.pdfPages.length, 'records');
                            this.filteredPdfPages = [...this.pdfPages];
                            this.filterTable();
                        },
                        
                        get pagedPdfPages() {
                            if (this.pageSize === 0) {
                                return this.filteredPdfPages;
                            }
                            const start = (this.currentPage - 1) * this.pageSize;
                            const end = start + this.pageSize;
                            return this.filteredPdfPages.slice(start, end);
                        },
                        
                        get totalPages() {
                            if (this.pageSize === 0) return 1;
                            return Math.ceil(this.filteredPdfPages.length / this.pageSize);
                        },
                        
                        filterTable() {
                            let filtered = [...this.pdfPages];
                            
                            if (this.searchQuery) {
                                const query = this.searchQuery.toLowerCase();
                                filtered = filtered.filter(page => 
                                    (page.pdf_name && page.pdf_name.toLowerCase().includes(query)) ||
                                    (page.uploader_name && page.uploader_name.toLowerCase().includes(query))
                                );
                            }
                            
                            if (this.statusFilter !== 'all') {
                                filtered = filtered.filter(page => page.status === this.statusFilter);
                            }
                            
                            filtered.sort((a, b) => {
                                let aValue = a[this.sortColumn];
                                let bValue = b[this.sortColumn];
                                
                                if (aValue === null || aValue === undefined) aValue = '';
                                if (bValue === null || bValue === undefined) bValue = '';
                                
                                if (this.sortDirection === 'asc') {
                                    return aValue > bValue ? 1 : -1;
                                } else {
                                    return aValue < bValue ? 1 : -1;
                                }
                            });
                            
                            this.filteredPdfPages = filtered;
                            this.currentPage = 1;
                        },
                        
                        sortBy(column) {
                            if (this.sortColumn === column) {
                                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                            } else {
                                this.sortColumn = column;
                                this.sortDirection = 'asc';
                            }
                            this.filterTable();
                        }
                    }" x-init="init()">
                        <!-- Table Controls -->
                        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                <!-- Search -->
                                <div class="relative">
                                    <input 
                                        x-model="searchQuery"
                                        @keyup.debounce.300ms="filterTable()"
                                        type="text"
                                        placeholder="Search PDF pages..."
                                        class="w-full sm:w-64 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                    <svg class="absolute right-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.16667 3.33334C5.94501 3.33334 3.33334 5.94501 3.33334 9.16668C3.33334 12.3883 5.94501 15 9.16667 15C12.3883 15 15 12.3883 15 9.16668C15 5.94501 12.3883 3.33334 9.16667 3.33334ZM1.66667 9.16668C1.66667 5.02455 5.02455 1.66668 9.16667 1.66668C13.3088 1.66668 16.6667 5.02455 16.6667 9.16668C16.6667 13.3088 13.3088 16.6667 9.16667 16.6667C5.02455 16.6667 1.66667 13.3088 1.66667 9.16668Z" fill="#9CA3AF"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.2858 13.2857C13.6112 12.9603 14.1388 12.9603 14.4642 13.2857L18.0892 16.9107C18.4147 17.2361 18.4147 17.7638 18.0892 18.0892C17.7638 18.4147 17.2362 18.4147 16.9108 18.0892L13.2858 14.4642C12.9603 14.1388 12.9603 13.6112 13.2858 13.2857Z" fill="#9CA3AF"/>
                                    </svg>
                                </div>

                                <!-- Status Filter -->
                                <select 
                                    x-model="statusFilter"
                                    @change="filterTable()"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                >
                                    <option value="all">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                </select>

                                <!-- Page Size -->
                                <select 
                                    x-model="pageSize"
                                    @change="currentPage = 1"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                >
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                    <option value="0">Show All</option>
                                </select>
                            </div>

                            <!-- Stats -->
                            <div class="flex items-center gap-2">
                                <span class="bg-red-50 text-red-700 text-theme-xs dark:bg-red-500/15 dark:text-red-400 inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-medium">
                                    Total: <span x-text="pdfPages.length"></span>
                                </span>
                                <span class="bg-green-50 text-green-700 text-theme-xs dark:bg-green-500/15 dark:text-green-400 inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-medium">
                                    Showing: <span x-text="pagedPdfPages.length"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="max-w-full overflow-x-auto custom-scrollbar">
                                <table class="w-full min-w-[1102px]">
                                    <thead>
                                        <tr class="border-b border-gray-100 bg-red-50 dark:border-gray-800 dark:bg-red-900/10">
                                            <th class="px-5 py-3 text-left">
                                                <button @click="sortBy('pdf_name')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    PDF Info
                                                    <svg :class="{'rotate-180': sortColumn === 'pdf_name' && sortDirection === 'desc'}" 
                                                          class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <button @click="sortBy('page_number')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Page Details
                                                    <svg :class="{'rotate-180': sortColumn === 'page_number' && sortDirection === 'desc'}" 
                                                          class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <button @click="sortBy('uploader_name')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Upload Details
                                                    <svg :class="{'rotate-180': sortColumn === 'uploader_name' && sortDirection === 'desc'}" 
                                                          class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <button @click="sortBy('status')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Status
                                                    <svg :class="{'rotate-180': sortColumn === 'status' && sortDirection === 'desc'}" 
                                                          class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </th>
                                            <th class="px-5 py-3 text-left">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Actions
                                                </p>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="filteredPdfPages.length === 0">
                                            <tr>
                                                <td colspan="5" class="px-5 py-8 text-center">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <svg class="h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                            <span x-show="pdfPages.length === 0">No unlinked PDF pages found.</span>
                                                            <span x-show="pdfPages.length > 0 && filteredPdfPages.length === 0">No PDF pages match your search or filter.</span>
                                                        </p>
                                                        <p x-show="searchQuery || statusFilter !== 'all'" class="text-xs text-gray-400 dark:text-gray-500">
                                                            Try adjusting your search or filter
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        
                                        <template x-for="page in pagedPdfPages" :key="page.id">
                                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/50">
                                                <!-- PDF Info -->
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="h-12 w-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <span class="block text-sm font-semibold text-gray-900 dark:text-white">
                                                                <span x-text="page.pdf_name"></span>
                                                            </span>
                                                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                                                <span x-text="page.year"></span>/<span x-text="page.month"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                
                                                <!-- Page Details -->
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="h-10 w-10 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800 rounded-lg flex items-center justify-center">
                                                            <span class="text-lg font-bold text-blue-700 dark:text-blue-300" x-text="page.page_number"></span>
                                                        </div>
                                                        <div>
                                                            <span class="block text-sm font-medium text-gray-900 dark:text-white">
                                                                Page <span x-text="page.page_number"></span>
                                                            </span>
                                                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                                                of <span x-text="page.total_pages"></span> pages
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                
                                                <!-- Upload Details -->
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                                            <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <span class="block text-sm font-medium text-gray-900 dark:text-white">
                                                                <span x-text="page.uploader_name"></span>
                                                            </span>
                                                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                                                <span x-text="page.uploader_role"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                
                                                <!-- Status -->
                                                <td class="px-5 py-4">
                                                    <div class="flex flex-col gap-2">
                                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium"
                                                              :class="page.status_colors[page.status] || 'bg-gray-50 text-gray-700 dark:bg-gray-500/15 dark:text-gray-400'">
                                                            <span x-text="page.status.charAt(0).toUpperCase() + page.status.slice(1)"></span>
                                                        </span>
                                                        <template x-if="page.assigned_to">
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                                Assigned: <span x-text="page.assigned_to"></span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                </td>
                                                
                                                <!-- Actions -->
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center gap-2">
                                                        <a :href="'/pdf-uploads/' + page.pdf_id + '/page/' + page.page_number" 
                                                           target="_blank"
                                                           class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700 shadow-theme-xs ring-1 ring-blue-200 transition hover:bg-blue-100 dark:bg-blue-500/15 dark:text-blue-400 dark:ring-blue-500/20">
                                                            View
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                            </svg>
                                                        </a>
                                                        <a :href="'/marriages/create?pdf_upload_id=' + page.pdf_id + '&pdf_page_id=' + page.id + '&page_number=' + page.page_number" 
                                                           class="inline-flex items-center gap-1 rounded-lg bg-green-50 px-3 py-1.5 text-sm font-medium text-green-700 shadow-theme-xs ring-1 ring-green-200 transition hover:bg-green-100 dark:bg-green-500/15 dark:text-green-400 dark:ring-green-500/20">
                                                            Create
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div x-show="filteredPdfPages.length > 0 && pageSize > 0" class="flex items-center justify-between border-t border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
                                <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                                    <span>Showing</span>
                                    <span x-text="(currentPage - 1) * pageSize + 1"></span>
                                    <span>to</span>
                                    <span x-text="Math.min(currentPage * pageSize, filteredPdfPages.length)"></span>
                                    <span>of</span>
                                    <span x-text="filteredPdfPages.length"></span>
                                    <span>entries</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="currentPage--"
                                        :disabled="currentPage === 1"
                                        :class="currentPage === 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                        Previous
                                    </button>
                                    <div class="flex items-center gap-1">
                                        <template x-for="page in totalPages">
                                            <button
                                                @click="currentPage = page"
                                                :class="currentPage === page 
                                                    ? 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/15 dark:text-red-400 dark:ring-red-500/20' 
                                                    : 'bg-white text-gray-700 ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700'"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium shadow-theme-xs ring-1 transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                                                x-text="page"
                                            ></button>
                                        </template>
                                    </div>
                                    <button
                                        @click="currentPage++"
                                        :disabled="currentPage === totalPages"
                                        :class="currentPage === totalPages ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                    >
                                        Next
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="rounded-2xl border border-gray-200 bg-white py-12 text-center dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900">
            <svg class="h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </div>
        <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-white">No Marriage Records Found</h3>
        <p class="mx-auto mb-8 max-w-md text-gray-600 dark:text-gray-400">
            @if((isset($unlinkedImages) && count($unlinkedImages) > 0) || (isset($unlinkedPdfPages) && count($unlinkedPdfPages) > 0))
                You have {{ count($unlinkedImages ?? []) }} unlinked images and {{ count($unlinkedPdfPages ?? []) }} unlinked PDF pages available.
            @else
                No marriage records have been created yet.
            @endif
        </p>
        <div class="flex items-center justify-center gap-4">
            <a href="{{ route('marriages.create') }}" 
               class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-purple-500 to-purple-600 px-5 py-3 font-medium text-white shadow-lg transition-all duration-200 hover:-translate-y-1 hover:from-purple-600 hover:to-purple-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create New Marriage
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-75 p-4">
    <div class="max-h-[90vh] w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
        <div class="flex items-center justify-between border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 dark:text-white"></h3>
            <button onclick="closeImageModal()" 
                    class="rounded-full p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="max-h-[calc(90vh-5rem)] overflow-auto p-6">
            <img id="modalImage" src="" alt="" class="mx-auto max-w-full rounded-lg shadow-lg">
        </div>
        <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
            <div class="flex justify-end">
                <button onclick="closeImageModal()" 
                        class="rounded-lg bg-gradient-to-r from-gray-600 to-gray-700 px-4 py-2 font-medium text-white transition-all duration-200 hover:from-gray-700 hover:to-gray-800">
                    Close Preview
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function marriageTellerTable() {
    return {
        activeTab: 'marriages',
        initTable() {
            console.log('Marriage teller table initialized');
        }
    };
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}
</script>

<style>
[x-cloak] {
    display: none !important;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    height: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.dark .custom-scrollbar::-webkit-scrollbar-track {
    background: #374151;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4b5563;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}
</style>