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
    </div>
</div>

<!-- Alert Modal Component -->
@include('partials.modal.alert-modal')

<!-- Quick Entry Warning Modal -->
@include('partials.modal.quick-entry-warning')

<!-- Main Content Grid -->
<div x-data="pdfPageViewer()" x-init="init()" @wards-updated.window="wards = $event.detail.wards" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
            
            <!-- PDF Viewer Container -->
            <div class="relative bg-gray-100">
                @if($pageContent)
                    @if(strpos($pageContent, 'data:image/png;base64,') === 0)
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

        <!-- Page Navigation Pills with Equal Width -->
        <div class="mb-6">
            <div class="flex flex-wrap items-center gap-2">
                @foreach($allPages as $page)
                    <a href="{{ route('pdf-uploads.show', $page->id) }}"
                       class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-md min-w-[60px] {{ $pdfPage->id == $page->id ? 'bg-blue-100 text-blue-800 border border-blue-300' : 'bg-gray-100 text-gray-800 hover:bg-gray-200 border border-gray-300' }}">
                        <span class="flex items-center justify-center w-full">
                            {{ $page->page_number }}
                            @if($page->status == 'completed')
                                <svg class="w-4 h-4 ml-1 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @elseif($page->status == 'in_progress')
                                <svg class="w-4 h-4 ml-1 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 1.5a8.5 8.5 0 100 17 8.5 8.5 0 000-17zM4.5 10a5.5 5.5 0 1111 0 5.5 5.5 0 01-11 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </span>
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
                    @include('pdf-uploads.partials.view-mode')
                @else
                    <!-- No Marriage Record - Role-based Tabs -->
                    @php
                        $userRole = auth()->user()->role->name;
                        $showBothTabs = in_array($userRole, ['admin', 'marriage_registrar']);
                    @endphp
                    
                    <div x-data="{ 
                        activeTab: 'full',
                        showBothTabs: @json($showBothTabs)
                    }">
                        <!-- Tab Navigation - Only show both tabs for admin/registrar -->
                        <div class="mb-4 border-b border-gray-200" x-show="showBothTabs">
                            <ul class="flex w-full text-sm font-semibold text-center">
                                <li class="w-1/2">
                                    <a href="#"
                                       @click.prevent="activeTab = 'full'"
                                       class="flex items-center justify-center w-full py-3 border-b-2 transition-all duration-200"
                                       :class="activeTab === 'full'
                                            ? 'border-primary text-primary bg-primary/10'
                                            : 'border-transparent text-gray-500 hover:text-primary hover:bg-gray-50'">
                                        <i class="fas fa-list-alt mr-2"></i>
                                        Full Entry
                                    </a>
                                </li>
                                <li class="w-1/2">
                                    <a href="#"
                                       @click.prevent="activeTab = 'quick'"
                                       class="flex items-center justify-center w-full py-3 border-b-2 transition-all duration-200"
                                       :class="activeTab === 'quick'
                                            ? 'border-primary text-primary bg-primary/10'
                                            : 'border-transparent text-gray-500 hover:text-primary hover:bg-gray-50'">
                                        <i class="fas fa-bolt mr-2"></i>
                                        Quick Entry
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- For data clerk/teller, always show full entry without tabs -->
                        <template x-if="!showBothTabs">
                            <div>
                                @include('partials.form.full-entry-form', [
                                    'pdfPage' => $pdfPage,
                                    'pdfUpload' => $pdfUpload,
                                    'constants' => $constants,
                                    'subCounties' => $subCounties,
                                    'wards' => $wards,
                                    'marriageTypes' => $marriageTypes,
                                    'commonOccupations' => $commonOccupations ?? []
                                ])
                            </div>
                        </template>

                        <!-- For admin/registrar, show tabbed interface -->
                        <template x-if="showBothTabs">
                            <div>
                                <!-- Quick Entry Tab Content -->
                                <div x-show="activeTab === 'quick'" x-cloak>
                                    @include('partials.form.quick-entry-form', [
                                        'pdfPage' => $pdfPage,
                                        'pdfUpload' => $pdfUpload,
                                        'constants' => $constants,
                                        'subCounties' => $subCounties
                                    ])
                                </div>

                                <!-- Full Entry Tab Content -->
                                <div x-show="activeTab === 'full'" x-cloak>
                                    @include('partials.form.full-entry-form', [
                                        'pdfPage' => $pdfPage,
                                        'pdfUpload' => $pdfUpload,
                                        'constants' => $constants,
                                        'subCounties' => $subCounties,
                                        'wards' => $wards,
                                        'marriageTypes' => $marriageTypes,
                                        'commonOccupations' => $commonOccupations ?? []
                                    ])
                                </div>
                            </div>
                        </template>
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
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<script>
// Make pdfPageViewer globally available
window.pdfPageViewer = function() {
    return {
        // Data properties
        pageStatus: '{{ $pdfPage->status }}',
        marriageDate: '{{ date('Y-m-d') }}',
        regDate: '{{ date('Y-m-d') }}',
        certificateSerial: '',
        selectedConstituency: '',
        selectedMarriageType: '{{ $pdfUpload->marriage_type_id }}',
        countyCode: '{{ $constants['county_code'] }}',
        countyName: '{{ $constants['county_name'] }}',
        wards: @json($wards ?? []),
        alert: {
            show: false,
            type: 'success',
            title: '',
            message: ''
        },
        openQuickEntryModal: false,
        
        // Date sync flags
        marriageDateManuallyChanged: false,
        regDateManuallyChanged: false,
        
        // Initialize function
        init() {
            console.log('PDF Page Viewer initialized');
            this.initDatePickers();
            this.initUppercaseFields();
            
            // Check for session messages
            @if(session('success'))
                this.showAlert('success', 'Success', '{{ session('success') }}');
            @endif
            @if(session('error'))
                this.showAlert('error', 'Error', '{{ session('error') }}');
            @endif
            @if(session('info'))
                this.showAlert('info', 'Information', '{{ session('info') }}');
            @endif
            @if(session('warning'))
                this.showAlert('warning', 'Warning', '{{ session('warning') }}');
            @endif
            
            // Initialize after a short delay
            setTimeout(() => {
                this.initSelect2();
            }, 300);
        },
        
        // Initialize Select2
        initSelect2() {
            if (typeof $.fn.select2 === 'undefined') return;
            
            $('.select2-container').remove();
            
            // Sub County selects
            $('#sub_county, #full_sub_county').select2({
                placeholder: "Select Sub County",
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            }).on('change', (e) => {
                this.selectedConstituency = e.target.value;
                this.loadWards();
            });

            // Ward selects with tagging - Updated to work with new structure
            $('#full_ward, .residence-select').each((i, el) => {
                $(el).select2({
                    placeholder: "Select or type ward name",
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5',
                    tags: true,
                    createTag: (params) => {
                        const term = $.trim(params.term);
                        return term ? { id: term, text: term, newTag: true } : null;
                    }
                }).on('change', function(e) {
                    // Trigger Alpine update
                    const event = new Event('input', { bubbles: true });
                    this.dispatchEvent(event);
                });
            });
            
            // Update Select2 data with current wards
            this.updateWardSelect2();
        },
        
        // Load wards based on constituency using existing API
        async loadWards() {
            if (!this.selectedConstituency || !this.countyName) {
                this.wards = [];
                this.dispatchWardsEvent();
                return;
            }
            
            try {
                // Use the existing API route
                const response = await fetch(`/api/counties/${encodeURIComponent(this.countyName)}/constituencies/${encodeURIComponent(this.selectedConstituency)}/wards`);
                const data = await response.json();
                
                this.wards = data.wards || [];
                this.dispatchWardsEvent();
                this.updateWardSelect2();
            } catch (error) {
                console.error('Error loading wards:', error);
                this.wards = [];
                this.dispatchWardsEvent();
            }
        },
        
        dispatchWardsEvent() {
            window.dispatchEvent(new CustomEvent('wards-updated', { 
                detail: { wards: this.wards } 
            }));
        },
        
        updateWardSelect2() {
            const wardSelects = $('#full_ward, .residence-select');
            
            wardSelects.each((i, el) => {
                const $select = $(el);
                const currentValue = $select.val();
                
                // Clear existing options except placeholder
                $select.find('option:not(:first)').remove();
                
                // Add new options
                this.wards.forEach(ward => {
                    $select.append(`<option value="${ward.wards}">${ward.wards}</option>`);
                });
                
                // Restore value if it exists in new options
                if (currentValue && this.wards.some(w => w.wards === currentValue)) {
                    $select.val(currentValue).trigger('change');
                } else {
                    $select.val('').trigger('change');
                }
                
                // Refresh Select2
                $select.trigger('change.select2');
            });
        },
        
        // Initialize date pickers with bidirectional sync
        initDatePickers() {
            // This is now handled in the full entry form component
            // Keep for compatibility with other forms
        },
        
        // Initialize uppercase fields
        initUppercaseFields() {
            document.querySelectorAll('.uppercase-field').forEach(field => {
                field.addEventListener('input', function(e) {
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    this.value = this.value.toUpperCase();
                    this.setSelectionRange(start, end);
                });
                
                field.addEventListener('blur', function() {
                    this.value = this.value.toUpperCase();
                });
            });
        },
        
        // Show alert modal
        showAlert(type, title, message) {
            this.alert = {
                show: true,
                type: type,
                title: title,
                message: message
            };
        },
        
        // Close alert modal
        closeAlert() {
            this.alert.show = false;
        },
        
        // Show quick entry warning
        showQuickEntryWarning() {
            this.openQuickEntryModal = true;
        },
        
        // Format status
        formatStatus(status) {
            if (!status) return 'Unknown';
            const map = {
                'completed': 'Completed',
                'pending': 'Pending',
                'in_progress': 'In Progress',
                'assigned': 'Assigned',
                'review_needed': 'Review Needed',
                'skipped': 'Skipped'
            };
            return map[status] || status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
        },
        
        // Get status class
        getStatusClass(status) {
            if (!status) return 'bg-gray-100 text-gray-800';
            const map = {
                'completed': 'bg-green-100 text-green-800',
                'pending': 'bg-yellow-100 text-yellow-800',
                'in_progress': 'bg-blue-100 text-blue-800',
                'assigned': 'bg-indigo-100 text-indigo-800',
                'review_needed': 'bg-orange-100 text-orange-800',
                'skipped': 'bg-red-100 text-red-800'
            };
            return map[status] || 'bg-gray-100 text-gray-800';
        },
        
        // Get completion badge color based on percentage
        getCompletionBadgeClass(percentage) {
            if (percentage >= 80) return 'bg-green-100 text-green-800';
            if (percentage >= 50) return 'bg-orange-100 text-orange-800';
            if (percentage > 0) return 'bg-red-100 text-red-800';
            return 'bg-yellow-100 text-yellow-800';
        },
        
        // Print PDF
        printPage() {
            const pdfFrame = document.getElementById('pdf-frame');
            if (pdfFrame?.contentWindow) {
                pdfFrame.contentWindow.print();
            }
        },
        
        // Adjust PDF viewer
        adjustPDFViewer() {
            const pdfFrame = document.getElementById('pdf-frame');
            const pageImage = document.getElementById('page-image');
            
            if (pdfFrame?.contentDocument) {
                try {
                    pdfFrame.contentDocument.body.style.zoom = '100%';
                } catch (e) {}
            }
            
            if (pageImage) {
                pageImage.style.maxWidth = '100%';
                pageImage.style.height = 'auto';
                pageImage.style.objectFit = 'contain';
            }
        }
    };
}
</script>

<style>
[x-cloak] { display: none !important; }
.uppercase-field { text-transform: uppercase; }
.deceased-checkbox:checked + .residence-field { opacity: 0.5; }

/* Alert modal and datepicker z-index */
.fixed.inset-0.z-50,
.fixed.inset-0.z-[99999],
[class*="z-"] {
    z-index: 99999 !important;
}

/* Datepicker input styling */
input[type="date"] {
    position: relative;
    z-index: 99999 !important;
}

/* Ensure datepicker popup appears above modals */
input[type="date"]::-webkit-calendar-picker-indicator {
    position: relative;
    z-index: 99999;
    background: transparent;
    cursor: pointer;
}

/* Completion badge colors */
.bg-red-100 { background-color: #fee2e2; }
.text-red-800 { color: #991b1b; }
.bg-orange-100 { background-color: #ffedd5; }
.text-orange-800 { color: #9a3412; }
.bg-green-100 { background-color: #dcfce7; }
.text-green-800 { color: #166534; }
.bg-yellow-100 { background-color: #fef9c3; }
.text-yellow-800 { color: #854d0e; }

/* Select2 z-index fix */
.select2-container--open {
    z-index: 99999 !important;
}

/* Ensure the form container has proper spacing */
#marriage-data-container {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
    padding-right: 4px;
}

/* Scrollbar styling */
#marriage-data-container::-webkit-scrollbar {
    width: 6px;
}

#marriage-data-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#marriage-data-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

#marriage-data-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@endpush

@endsection