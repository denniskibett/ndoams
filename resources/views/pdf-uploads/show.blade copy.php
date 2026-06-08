@extends('layouts.app')

@section('content')
<!-- Breadcrumb Start -->
<div x-data="{ pageName: '{{ Str::limit($pdfUpload->name, 100) }} | Page {{ $pdfPage->page_number }} | {{ $pdfUpload->marriageType->name ?? 'No Type' }}' }">
    @include('partials.breadcrumb')
</div>
    
<!-- Page Header -->
<div class="mb-6">
    <div class="flex flex-wrap gap-2">
        @if($pdfPage->status === 'pending' && auth()->user()->can('update', $pdfPage))
        <form action="{{ route('pdf-pages.assign', $pdfPage) }}" method="POST" class="inline">
            @csrf
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 shadow-sm">
                <i class="fas fa-user-check mr-2"></i> Assign to Me
            </button>
        </form>
        @endif
        
        @if($pdfPage->status === 'in_progress' && $pdfPage->assigned_to === auth()->id() && !$marriage)
        <a href="{{ route('marriages.create') }}?pdf_upload_id={{ $constants['pdf_upload_id'] }}&pdf_page_id={{ $constants['pdf_page_id'] }}&page_number={{ $constants['page_number'] }}&county_code={{ $constants['county_code'] }}&year={{ $constants['year'] }}&month={{ $constants['month'] }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 shadow-sm">
            <i class="fas fa-plus mr-2"></i> Add Marriage Record
        </a>
        @endif
    </div>
</div>

<!-- Main Content Grid -->
<div x-data="{
    // Data properties
    pageStatus: '{{ $pdfPage->status }}',
    marriageDate: '{{ date('Y-m-d') }}',
    regDate: '{{ date('Y-m-d') }}',
    certificateSerial: '',
    
    // Initialize function
    init() {
        console.log('PDF Page Viewer initialized');
        
        // Auto-generate certificate serial on load
        this.autoGenerateSerial();
        
        // Sync dates initially
        this.syncDates();
        
        // Initialize date pickers
        this.initDatePickers();
    },
    
    // Auto-generate certificate serial
    autoGenerateSerial() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const countyPrefix = '{{ strtoupper(substr($constants["county_name"], 0, 3)) }}';
        const pageNum = '{{ str_pad($pdfPage->page_number, 3, "0", STR_PAD_LEFT) }}';
        
        // Format: COUNTY-YYYY-MM-DD-PAGENUM (e.g., NAI-2024-01-25-001)
        this.certificateSerial = `${countyPrefix}-${year}-${month}-${day}-${pageNum}`;
        
        // Also update the form input
        const certInput = document.getElementById('certificate_serial');
        if (certInput) {
            certInput.value = this.certificateSerial;
        }
    },
    
    // Sync dates between marriage and registration
    syncDates() {
        // When marriage date changes, update registration date
        const marriageInput = document.getElementById('marriage_date');
        const regInput = document.getElementById('reg_date');
        
        if (marriageInput && regInput) {
            // Set both to today initially
            marriageInput.value = this.marriageDate;
            regInput.value = this.regDate;
            
            // Set max date to today
            const today = new Date().toISOString().split('T')[0];
            marriageInput.max = today;
            regInput.max = today;
            
            // Add event listeners for synchronization
            marriageInput.addEventListener('change', (e) => {
                this.marriageDate = e.target.value;
                regInput.value = e.target.value;
                this.regDate = e.target.value;
            });
            
            regInput.addEventListener('change', (e) => {
                this.regDate = e.target.value;
                marriageInput.value = e.target.value;
                this.marriageDate = e.target.value;
            });
        }
    },
    
    // Initialize date pickers
    initDatePickers() {
        // You can add custom date picker logic here
        // For now, we'll just ensure the native date pickers work properly
        console.log('Date pickers initialized');
    },
    
    // Format status for display
    formatStatus(status) {
        if (!status) return 'Unknown';
        
        const statusMap = {
            'completed': 'Completed',
            'pending': 'Pending',
            'in_progress': 'In Progress',
            'assigned': 'Assigned',
            'review_needed': 'Review Needed',
            'skipped': 'Skipped'
        };
        
        return statusMap[status] || status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
    },
    
    // Get status class
    getStatusClass(status) {
        if (!status) return 'bg-gray-100 text-gray-800';
        
        switch(status) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'in_progress':
                return 'bg-blue-100 text-blue-800';
            case 'assigned':
                return 'bg-indigo-100 text-indigo-800';
            case 'review_needed':
                return 'bg-orange-100 text-orange-800';
            case 'skipped':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    },
    
    // Handle auto-generate button click
    handleAutoGenerate() {
        this.autoGenerateSerial();
    },
    
    // Print PDF page
    printPage() {
        const pdfFrame = document.getElementById('pdf-frame');
        if (pdfFrame && pdfFrame.contentWindow) {
            pdfFrame.contentWindow.focus();
            pdfFrame.contentWindow.print();
        }
    },
    
    // Adjust PDF viewer
    adjustPDFViewer() {
        const pdfFrame = document.getElementById('pdf-frame');
        const pageImage = document.getElementById('page-image');
        
        if (pdfFrame) {
            try {
                pdfFrame.contentDocument.body.style.zoom = '100%';
                pdfFrame.contentDocument.body.style.transform = 'scale(1)';
            } catch (e) {
                console.log('Could not adjust PDF scale:', e);
            }
        }
        
        if (pageImage) {
            pageImage.style.maxWidth = '100%';
            pageImage.style.height = 'auto';
            pageImage.style.objectFit = 'contain';
        }
    }
}" 
x-init="init()"
class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - PDF Viewer -->
    <div class="lg:col-span-2 space-y-6">
        <!-- PDF Viewer Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="far fa-file-pdf text-red-500 mr-2"></i> Page {{ $pdfPage->page_number }} | 
                    County: {{ $constants['county_name'] }} | 
                    Period: {{ $month }}/{{ $constants['year'] }} |
                    Type: {{ $pdfUpload->marriageType->name ?? 'No Type' }}
                </h2>
                <div class="flex items-center gap-3">
                    <div class="text-sm text-gray-500">
                        Page {{ $pdfPage->page_number }} of {{ $pdfUpload->total_pages }}
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" 
                          :class="getStatusClass(pageStatus)"
                          x-text="formatStatus(pageStatus)">
                    </span>
                </div>
            </div>
            
            <!-- PDF Viewer Container - Fixed Size -->
            <div class="relative bg-gray-100">
                @if($pageContent)
                    @if(strpos($pageContent, 'data:image/png;base64,') === 0)
                        <!-- PNG Image Display -->
                        <div class="flex justify-center items-center p-4" style="min-height: 700px; max-height: 700px;">
                            <div class="relative" style="width: 100%; height: 700px; overflow: auto;">
                                <img src="{{ $pageContent }}" 
                                     alt="Page {{ $pdfPage->page_number }}" 
                                     class="max-w-full h-auto shadow-lg border border-gray-300"
                                     style="object-fit: contain;"
                                     id="page-image">
                            </div>
                        </div>
                    @elseif(strpos($pageContent, 'data:application/pdf;base64,') === 0)
                        <!-- PDF Display with Fixed Container -->
                        <div class="flex justify-center items-center p-4" style="min-height: 700px; max-height: 700px;">
                            <div class="relative" style="width: 100%; height: 700px; overflow: auto;">
                                <iframe 
                                    src="{{ $pageContent }}"
                                    style="width: 100%; height: 700px; border: none;"
                                    allowfullscreen
                                    title="Page {{ $pdfPage->page_number }}"
                                    id="pdf-frame"
                                    @load="adjustPDFViewer()">
                                </iframe>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- No content available -->
                    <div class="p-8 flex flex-col items-center justify-center" style="min-height: 700px; max-height: 700px;">
                        <i class="far fa-file-excel text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-600">Unable to load page content</p>
                        <p class="text-sm text-gray-500 mt-2">The PDF page could not be extracted</p>
                    </div>
                @endif
            </div>
            
            <!-- Page Controls -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center space-x-2">
                        @if($pdfPage->page_number > 1)
                            @php
                                $prevPage = $allPages->where('page_number', $pdfPage->page_number - 1)->first();
                            @endphp
                            @if($prevPage)
                            <a href="{{ route('pdf-uploads.show', $prevPage->id) }}" 
                               class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                                <i class="fas fa-chevron-left mr-1.5"></i> Previous Page
                            </a>
                            @endif
                        @endif
                        
                        <div class="text-sm font-medium text-gray-700 px-3">
                            Page {{ $pdfPage->page_number }} of {{ $pdfUpload->total_pages }}
                        </div>
                        
                        @if($pdfPage->page_number < $pdfUpload->total_pages)
                            @php
                                $nextPage = $allPages->where('page_number', $pdfPage->page_number + 1)->first();
                            @endphp
                            @if($nextPage)
                            <a href="{{ route('pdf-uploads.show', $nextPage->id) }}" 
                               class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                                Next Page <i class="fas fa-chevron-right ml-1.5"></i>
                            </a>
                            @endif
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('pdf-uploads.download', $pdfUpload) }}" 
                           class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                            <i class="fas fa-download mr-1.5"></i> Download PDF
                        </a>
                        
                        @if($pageContent && strpos($pageContent, 'data:application/pdf;base64,') === 0)
                        <button @click="printPage()" 
                                class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                            <i class="fas fa-print mr-1.5"></i> Print Page
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page Information Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i> Page Information
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Status Details</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Current Status:</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="formatStatus(pageStatus)"></span>
                                </div>
                                @if($pdfPage->assignedUser)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Assigned To:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $pdfPage->assignedUser->name }}</span>
                                </div>
                                @endif
                                @if($pdfPage->assigned_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Assigned At:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $pdfPage->assigned_at->format('M d, Y h:i A') }}</span>
                                </div>
                                @endif
                                @if($pdfPage->started_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Started At:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $pdfPage->started_at->format('M d, Y h:i A') }}</span>
                                </div>
                                @endif
                                @if($pdfPage->completed_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Completed At:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $pdfPage->completed_at->format('M d, Y h:i A') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        @if($pdfPage->notes)
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Notes</h3>
                            <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $pdfPage->notes }}</p>
                        </div>
                        @endif
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Locked Constants</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">County:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $constants['county_name'] }} ({{ $constants['county_code'] }})</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Year:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $constants['year'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Month:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $month }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Marriage Type:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $pdfUpload->marriageType->name ?? 'No Type' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Page Number:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $constants['page_number'] }}</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($pdfPage->status === 'in_progress' && $pdfPage->assigned_to === auth()->id())
                        <div class="pt-4 border-t border-gray-200">
                            <form action="{{ route('pdf-pages.complete', $pdfPage) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="completion_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                        Completion Notes (Optional)
                                    </label>
                                    <textarea name="notes" id="completion_notes" rows="2"
                                              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                              placeholder="Add any notes about completing this page..."></textarea>
                                </div>
                                <button type="submit" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                    <i class="fas fa-check-circle mr-2"></i> Mark as Completed
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Navigation Pills -->
        <div class="mb-6">
            <div class="flex flex-wrap items-center gap-2">
                @foreach($allPages as $page)
                    <a href="{{ route('pdf-uploads.show', $page->id) }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md {{ $pdfPage->id == $page->id ? 'bg-blue-100 text-blue-800 border border-blue-300' : 'bg-gray-100 text-gray-800 hover:bg-gray-200 border border-gray-300' }}">
                         {{ $page->page_number }}
                        @if($page->status == 'completed')
                            <svg class="w-4 h-4 ml-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($page->status == 'in_progress')
                            <svg class="w-4 h-4 ml-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 1.5a8.5 8.5 0 100 17 8.5 8.5 0 000-17zM4.5 10a5.5 5.5 0 1111 0 5.5 5.5 0 01-11 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Right Column - Marriage Data & Actions -->
    <div class="space-y-6">
        <!-- Marriage Data Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-heart text-pink-500 mr-2"></i> Marriage Record
                </h2>
                <div class="flex items-center space-x-2">
                    @if($marriage)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check mr-1"></i> Linked
                    </span>
                    @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <i class="fas fa-unlink mr-1"></i> Unlinked
                    </span>
                    @endif
                </div>
            </div>
            
            <div class="p-6" id="marriage-data-container">
                @if($marriage)
                    <!-- View Mode for Existing Marriage -->
                    <div id="marriage-view-mode">
                        <div class="space-y-4">
                            <div>
                                <h3 class="font-medium text-gray-900">{{ $marriage->certificate_serial ?? 'No Marriage Number' }}</h3>
                                <p class="text-sm text-gray-600 mt-1">Marriage Record</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Certificate Serial</label>
                                        <p class="text-sm text-gray-900">{{ $marriage->certificate_serial ?? '--' }}</p>
                                    </div>
                                    
                                    @if($marriage->marriage_date)
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Marriage Date</label>
                                        <p class="text-sm text-gray-900">{{ optional($marriage->marriage_date)->format('d-m-Y') }}</p>
                                    </div>
                                    @endif
                                    
                                    @if($marriage->county)
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">County</label>
                                        <p class="text-sm text-gray-900">{{ $marriage->county }}</p>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="space-y-3">
                                    @if($marriage->venue)
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Venue</label>
                                        <p class="text-sm text-gray-900">{{ $marriage->venue }}</p>
                                    </div>
                                    @endif
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">System Status</label>
                                        <p class="text-sm text-gray-900">{{ $marriage->system_status ?? '--' }}</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Verification</label>
                                        <p class="text-sm text-gray-900">{{ $marriage->verificationStatus->name ?? '--' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Marriage Type</label>
                                        <p class="text-sm text-gray-900">{{ $pdfUpload->marriageType->name ?? 'No Type' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t border-gray-200">
                                <a href="{{ route('marriages.show', $marriage) }}" 
                                class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-eye mr-2"></i> View Full Record
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- No Marriage Record - Show Create Form -->
                    <div id="marriage-create-form">
                        <div class="space-y-6">
                            <!-- Quick Create Form -->
                            <form action="{{ route('pdf.quick-create-from-page') }}" method="POST" id="quick-create-form">
                                @csrf
                                
                                <!-- Constants from PDF Upload -->
                                <input type="hidden" name="pdf_page_id" value="{{ $pdfPage->id }}">
                                <input type="hidden" name="pdf_id" value="{{ $pdfUpload->id }}">
                                <input type="hidden" name="year" value="{{ $constants['year'] }}">
                                <input type="hidden" name="month" value="{{ $constants['month'] }}">
                                <input type="hidden" name="county" value="{{ $constants['county_name'] }}">
                                <input type="hidden" name="marriage_type_id" value="{{ $pdfUpload->marriage_type_id }}">
                                
                                <div class="grid grid-cols-1 gap-4 mb-4">
                                    <!-- Marriage Type (Hidden - using PDF upload's type) -->
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
                                        <div class="flex gap-2">
                                            <input type="text" 
                                                name="certificate_serial" 
                                                id="certificate_serial"
                                                :value="certificateSerial"
                                                @change="certificateSerial = $event.target.value"
                                                placeholder="e.g., {{ strtoupper(substr($constants['county_name'], 0, 3)) }}-{{ $constants['year'] }}-{{ str_pad($constants['month'], 2, '0', STR_PAD_LEFT) }}-001"
                                                class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                                required>
                                            <button type="button" 
                                                    @click="handleAutoGenerate()"
                                                    class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-lg text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100">
                                                <i class="fas fa-magic mr-2"></i> Auto
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Constituency Selection -->
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                            Constituency <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <select name="sub_county" 
                                                    id="sub_county"
                                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                                    required>
                                                <option value="">Select Constituency</option>
                                                @if($subCounties->isNotEmpty())
                                                    @foreach($subCounties as $subCounty)
                                                        @if(!empty($subCounty->constituency))
                                                            <option value="{{ $subCounty->constituency }}">
                                                                {{ $subCounty->constituency }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <option value="">No constituencies found</option>
                                                @endif
                                            </select>
                                            <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500">
                                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10 14.1666C9.77899 14.1666 9.56702 14.0788 9.41074 13.9225L3.57741 8.08921C3.26008 7.77188 3.26008 7.22855 3.57741 6.91121C3.89475 6.59388 4.43808 6.59388 4.75541 6.91121L10 12.1557L15.2446 6.91121C15.5619 6.59388 16.1052 6.59388 16.4226 6.91121C16.7399 7.22855 16.7399 7.77188 16.4226 8.08921L10.5893 13.9225C10.433 14.0788 10.221 14.1666 10 14.1666Z" fill=""/>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Venue -->
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                            Venue <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                            name="venue" 
                                            id="venue"
                                            placeholder="e.g., {{ $constants['county_name'] }} Registry"
                                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                            required>
                                    </div>

                                    <!-- Marriage Date -->
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Marriage Date <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                type="date"
                                                name="marriage_date"
                                                id="marriage_date"
                                                :value="marriageDate"
                                                @change="marriageDate = $event.target.value"
                                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                                required
                                            />
                                            <span
                                                class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400"
                                            >
                                                <svg
                                                    class="fill-current"
                                                    width="20"
                                                    height="20"
                                                    viewBox="0 0 20 20"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        clip-rule="evenodd"
                                                        d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z"
                                                        fill=""
                                                    />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Registration Date -->
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Registration Date <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                type="date"
                                                name="reg_date"
                                                id="reg_date"
                                                :value="regDate"
                                                @change="regDate = $event.target.value"
                                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                                required
                                            />
                                            <span
                                                class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400"
                                            >
                                                <svg
                                                    class="fill-current"
                                                    width="20"
                                                    height="20"
                                                    viewBox="0 0 20 20"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        clip-rule="evenodd"
                                                        d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z"
                                                        fill=""
                                                    />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Notes - Textarea -->
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                            Notes <span class="text-gray-500">(Optional)</span>
                                        </label>
                                        <textarea
                                            name="notes" 
                                            id="notes"
                                            rows="3"
                                            placeholder="Notes/Feedback about this marriage record"
                                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                        ></textarea>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-3">
                                    <button type="submit" 
                                            class="flex-1 inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                        <i class="fas fa-save mr-2"></i> Create Marriage Record
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Related Pages Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-copy text-indigo-500 mr-2"></i> Nearby Pages
                </h2>
            </div>
            <div class="p-6">
                <div class="space-y-2">
                    @if($relatedPages->count() > 0)
                        @foreach($relatedPages as $relatedPage)
                        <a href="{{ route('pdf-uploads.show', $relatedPage->id) }}" 
                        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3
                                    {{ $relatedPage->status === 'completed' ? 'bg-green-100 text-green-700' : 
                                    ($relatedPage->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 
                                    ($relatedPage->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700')) }}">
                                    {{ $relatedPage->page_number }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600">
                                        Page {{ $relatedPage->page_number }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ ucfirst(str_replace('_', ' ', $relatedPage->status)) }}
                                        @if($relatedPage->marriage)
                                        • <span class="text-green-600">Linked</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600"></i>
                        </a>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500 text-sm">No other pages in this range</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Additional global functions if needed
// These are separate from Alpine.js data scope
document.addEventListener('alpine:init', () => {
    // You can register global Alpine.js components here if needed
    console.log('Alpine.js initialized');
});
</script>
@endpush

@endsection