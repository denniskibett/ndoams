{{-- resources/views/pdf-uploads/show.blade.php --}}

@extends('layouts.app')

@section('content')

<!-- Add user role meta tag for JavaScript -->
<meta name="user-role" content="{{ auth()->user()->role->name ?? '' }}">

<!-- Breadcrumb Start -->
<div x-data="{ pageName: '{{ Str::limit($pdfUpload->name, 100) }} | Page {{ $pdfPage->page_number }} | {{ $pdfUpload->marriageType->name ?? 'No Type' }}' }">
    @include('partials.breadcrumb')
</div>

<!-- Error and Success Messages -->
@if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-600">{{ session('error') }}</p>
    </div>
@endif

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-600">{{ session('success') }}</p>
    </div>
@endif
    
<!-- Page Header -->
<div class="mb-6">
    <div class="flex flex-wrap gap-2">
        @if($pdfPage->status === 'pending' && auth()->user()->can('update', $pdfPage))
        <button type="button" 
                onclick="window.confirmPageAction('assign_to_me', 'Assign this page to yourself?', 'Page Assigned', '{{ route('pdf-pages.assign', $pdfPage) }}')"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 shadow-sm">
            <i class="fas fa-user-check mr-2"></i> Assign to Me
        </button>
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
<div x-data="pdfPageViewer()" 
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
            @php
                $userRole = auth()->user()->role->name ?? '';
                $isDataClerk = $userRole === 'data_clerk';
            @endphp

            @unless($isDataClerk)
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
            @endunless
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
                            @php
                                $notesData = is_string($pdfPage->notes) ? json_decode($pdfPage->notes, true) : $pdfPage->notes;
                            @endphp
                            @if(is_array($notesData) && isset($notesData['description']))
                                <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $notesData['description'] }}</p>
                                @if(isset($notesData['missing_fields']) && !empty($notesData['missing_fields']))
                                    <div class="mt-2 text-xs text-yellow-600 bg-yellow-50 p-2 rounded">
                                        <strong>Missing fields:</strong> {{ implode(', ', $notesData['missing_fields']) }}
                                    </div>
                                @endif
                            @else
                                <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $pdfPage->notes }}</p>
                            @endif
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
                            <div class="mb-3">
                                <label for="completion_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    Completion Notes (Optional)
                                </label>
                                <textarea name="notes" id="completion_notes" rows="2"
                                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                          placeholder="Add any notes about completing this page..."></textarea>
                            </div>
                            <button type="button" 
                                    onclick="window.confirmPageAction('complete_page', 'Mark this page as completed?', 'Page Completed', '{{ route('pdf-pages.complete', $pdfPage) }}')"
                                    class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                <i class="fas fa-check-circle mr-2"></i> Mark as Completed
                            </button>
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
                    @php
                        $pageNumberPadded = str_pad($page->page_number, 3, '0', STR_PAD_LEFT);
                        $statusIcon = '';
                        $statusColor = 'bg-gray-100 text-gray-800 border-gray-300 hover:bg-gray-200';
                        
                        if ($page->status === 'completed') {
                            $statusIcon = '<svg class="w-4 h-4 ml-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>';
                            $statusColor = 'bg-green-100 text-green-800 border-green-300 hover:bg-green-200';
                            
                        } elseif ($page->status === 'in_progress') {
                            $statusIcon = '<svg class="w-4 h-4 ml-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 102 0V6zm-1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>';
                            $statusColor = 'bg-blue-100 text-blue-800 border-blue-300 hover:bg-blue-200';
                            
                        } elseif ($page->status === 'review_needed') {
                            $statusIcon = '<svg class="w-4 h-4 ml-2 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 102 0V6zm-1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>';
                            $statusColor = 'bg-orange-100 text-orange-800 border-orange-300 hover:bg-orange-200';
                            
                        } elseif ($page->status === 'published') {
                            $statusIcon = '<svg class="w-4 h-4 ml-2 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4z" clip-rule="evenodd"/>
                                <path d="M6 8h8v2H6V8zm0 4h5v2H6v-2z" fill-rule="evenodd"/>
                            </svg>';
                            $statusColor = 'bg-purple-100 text-purple-800 border-purple-300 hover:bg-purple-200';
                            
                        } elseif ($page->status === 'pending') {
                            $statusIcon = '<svg class="w-4 h-4 ml-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 1.5a8.5 8.5 0 100 17 8.5 8.5 0 000-17zM4.5 10a5.5 5.5 0 1111 0 5.5 5.5 0 01-11 0z" clip-rule="evenodd"/>
                            </svg>';
                            $statusColor = 'bg-yellow-100 text-yellow-800 border-yellow-300 hover:bg-yellow-200';
                            
                        } elseif ($page->status === 'skipped') {
                            $statusIcon = '<svg class="w-4 h-4 ml-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>';
                            $statusColor = 'bg-red-100 text-red-800 border-red-300 hover:bg-red-200';
                        }

                        $isActive = $pdfPage->id == $page->id;
                    @endphp

                    <a href="{{ route('pdf-uploads.show', $page->id) }}"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md border transition-all duration-150 min-w-[60px] justify-center
                    {{ $isActive 
                            ? 'bg-blue-100 text-blue-800 border-blue-300 shadow-sm ring-2 ring-blue-400' 
                            : $statusColor }}">
                        
                        <span class="font-mono">{{ $pageNumberPadded }}</span>
                        {!! $statusIcon !!}
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
                        @if($marriage->has_errors)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Has Errors
                            </span>
                        @endif
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-link mr-1"></i> Linked
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-unlink mr-1"></i> Unlinked
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="p-6">
                @if($marriage)
                    @php
                        $husband = $marriage->spouses->where('spouse_type', 'husband')->first();
                        $wife = $marriage->spouses->where('spouse_type', 'wife')->first();
                        $ward = collect($wards)->firstWhere('id', $marriage->ward_id);
                        $entryNo = $marriage->marriageExtension->entry_no ?? null;
                        $registrar = $marriage->registrar ?? null;
                    @endphp
                    
                    <!-- Error Alert -->
                    @if($marriage->has_errors)
                        <div class="mb-5 bg-red-50 border-l-4 border-red-500 rounded-r-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-red-800">Record has validation errors</p>
                                    <p class="text-xs text-red-700">This record was submitted with missing required fields. Please review and complete.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                   
                    
                    <!-- Basic Information with Entry No and License No -->
                    <div id="viewModeContent">
                        <div class="mb-5">
                            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-200">
                                <i class="fas fa-info-circle text-blue-500 text-sm"></i>
                                <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Basic Information</h3>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Cert No</p>
                                    <p class="text-gray-800 font-mono text-xs">{{ $marriage->certificate_serial ?? '--' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Entry No</p>
                                    <p class="text-gray-800 font-mono text-xs">{{ $entryNo ?? '--' }}</p>
                                </div>

                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Marriage Date</p>
                                <p class="text-gray-800">{{ optional($marriage->marriage_date)->format('d-m-Y') ?? '--' }}</p>
                            </div>
                             <div>
                                <p class="text-xs text-gray-500 mb-0.5">Registration Date</p>
                                <p class="text-gray-800">{{ optional($marriage->reg_date)->format('d-m-Y') ?? '--' }}</p>
                            </div>
                               
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm mt-3">
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Marriage Type</p>
                                    <p class="text-gray-800">{{ $pdfUpload->marriageType->name ?? '--' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">License No</p>
                                    <p class="text-gray-800 font-mono text-xs">{{ $marriage->license_no ?? '--' }}</p>
                                </div>
                                <div>
                                     <label class="text-xs text-gray-500 block">
                                        @php
                                            $marriageTypeName = $pdfUpload->marriageType->name ?? '';
                                            $officerLabel = 'Registrar Officer';
                                            if ($marriageTypeName === 'Christian') {
                                                $officerLabel = 'Pastor Name';
                                            } elseif ($marriageTypeName === 'Muslim') {
                                                $officerLabel = 'Muslim Officer';
                                            }
                                        @endphp
                                        {{ $officerLabel }}
                                    </label>

                                    @php
                                        $marriageTypeName = $pdfUpload->marriageType->name ?? '';
                                        $officerValue = '--';
                                        
                                        if ($marriage && $marriage->marriageExtension) {
                                            if ($marriageTypeName === 'Muslim') {
                                                $officerValue = $marriage->marriageExtension->muslim_officer ?? '--';
                                            } elseif ($marriageTypeName === 'Christian') {
                                                $officerValue = $marriage->marriageExtension->pastor_name ?? '--';
                                            } elseif ($marriageTypeName === 'Hindu') {
                                                $officerValue = $marriage->marriageExtension->registrar_officer ?? '--';
                                            } else {
                                                // Civil and others
                                                $officerValue = $marriage->marriageExtension->registrar_officer ?? '--';
                                            }
                                        }
                                    @endphp

                                    <p class="text-gray-800">{{ $officerValue }}</p>
                                    
                                </div> 
                                <div class="col-span-2">
                                    <p class="text-xs text-gray-500 mb-0.5">Venue</p>
                                    <p class="text-gray-800">{{ $marriage->venue ?? '--' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Location Information -->
                        <div class="mb-5">
                            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-200">
                                <i class="fas fa-location-dot text-green-500 text-sm"></i>
                                <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Location Information</h3>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">County</p>
                                    <p class="text-gray-800">{{ $marriage->county ?? '--' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Sub-County</p>
                                    <p class="text-gray-800">{{ $marriage->sub_county ?? '--' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Ward</p>
                                    <p class="text-gray-800">{{ $ward['wards'] ?? '--' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Spouses Information -->
                        <div class="mb-5">
                            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-200">
                                <i class="fas fa-users text-purple-500 text-sm"></i>
                                <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Spouses Information</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Husband Card -->
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-50 to-white px-4 py-2 border-b border-gray-200">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-mars text-blue-600 text-sm"></i>
                                            <h4 class="font-semibold text-gray-800">Husband</h4>
                                        </div>
                                    </div>
                                    <div class="p-3 space-y-2">
                                        <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                                            <div><span class="text-xs text-gray-500">Full Name</span><p class="text-gray-800 font-medium">{{ $husband->name ?? '--' }}</p></div>
                                            <div><span class="text-xs text-gray-500">Age</span><p class="text-gray-800">{{ $husband->age ?? '--' }}</p></div>
                                            <div><span class="text-xs text-gray-500">Occupation</span><p class="text-gray-800">{{ $husband->occupation ?? '--' }}</p></div>
                                            <div><span class="text-xs text-gray-500">Residence</span><p class="text-gray-800">{{ $husband->residence ?? '--' }}</p></div>
                                            <div class="col-span-2"><span class="text-xs text-gray-500">Marital Status</span><p class="text-gray-800">{{ $husband->marital_status ?? '--' }}</p></div>
                                        </div>
                                        
                                        <div class="mt-2 pt-2 border-t border-gray-100">
                                            <p class="text-xs font-medium text-gray-600 mb-1">Parents</p>
                                            <div class="grid grid-cols-2 gap-2 text-xs">
                                                <div class="bg-gray-50 rounded p-2">
                                                    <span class="text-gray-500 block text-xs">Father</span>
                                                    <span class="text-gray-800 font-medium">{{ $husband->father_name ?? '--' }}</span>
                                                    <span class="text-gray-500 block text-xs mt-1">{{ $husband->father_occupation ?? '--' }}</span>
                                                    <span class="text-gray-500 block text-xs mt-1">{{ $husband->father_residence ?? '--' }}</span>
                                                </div>
                                                <div class="bg-gray-50 rounded p-2">
                                                    <span class="text-gray-500 block text-xs">Mother</span>
                                                    <span class="text-gray-800 font-medium">{{ $husband->mother_name ?? '--' }}</span>
                                                    <span class="text-gray-500 block text-xs mt-1">{{ $husband->mother_occupation ?? '--' }}</span>
                                                    <span class="text-gray-500 block text-xs mt-1">{{ $husband->mother_residence ?? '--' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Wife Card -->
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-gradient-to-r from-pink-50 to-white px-4 py-2 border-b border-gray-200">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-venus text-pink-500 text-sm"></i>
                                            <h4 class="font-semibold text-gray-800">Wife</h4>
                                        </div>
                                    </div>
                                    <div class="p-3 space-y-2">
                                        <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                                            <div><span class="text-xs text-gray-500">Full Name</span><p class="text-gray-800 font-medium">{{ $wife->name ?? '--' }}</p></div>
                                            <div><span class="text-xs text-gray-500">Age</span><p class="text-gray-800">{{ $wife->age ?? '--' }}</p></div>
                                            <div><span class="text-xs text-gray-500">Occupation</span><p class="text-gray-800">{{ $wife->occupation ?? '--' }}</p></div>
                                            <div><span class="text-xs text-gray-500">Residence</span><p class="text-gray-800">{{ $wife->residence ?? '--' }}</p></div>
                                            <div class="col-span-2"><span class="text-xs text-gray-500">Marital Status</span><p class="text-gray-800">{{ $wife->marital_status ?? '--' }}</p></div>
                                        </div>
                                        
                                        <div class="mt-2 pt-2 border-t border-gray-100">
                                            <p class="text-xs font-medium text-gray-600 mb-1">Parents</p>
                                            <div class="grid grid-cols-2 gap-2 text-xs">
                                                <div class="bg-gray-50 rounded p-2">
                                                    <span class="text-gray-500 block text-xs">Father</span>
                                                    <span class="text-gray-800 font-medium">{{ $wife->father_name ?? '--' }}</span>
                                                    <span class="text-gray-500 block text-xs mt-1">{{ $wife->father_occupation ?? '--' }}</span>
                                                    <span class="text-gray-500 block text-xs mt-1">{{ $wife->father_residence ?? '--' }}</span>
                                                </div>
                                                <div class="bg-gray-50 rounded p-2">
                                                    <span class="text-gray-500 block text-xs">Mother</span>
                                                    <span class="text-gray-800 font-medium">{{ $wife->mother_name ?? '--' }}</span>
                                                    <span class="text-gray-500 block text-xs mt-1">{{ $wife->mother_occupation ?? '--' }}</span>
                                                    <span class="text-gray-500 block text-xs mt-1">{{ $wife->mother_residence ?? '--' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Witnesses -->
                        @if($marriage->witnesses->count() > 0)
                        <div class="mb-5">
                            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-200">
                                <i class="fas fa-eye text-amber-500 text-sm"></i>
                                <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Witnesses</h3>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                @foreach($marriage->witnesses as $witness)
                                    <div class="bg-gray-50 rounded-full px-4 py-1.5 text-sm border border-gray-200">
                                        <i class="fas fa-user-check text-gray-400 mr-1 text-xs"></i>
                                        {{ $witness->name ?? '--' }}
                                        @if($witness->spouse_side)
                                            <span class="text-xs text-gray-500 ml-1">({{ ucfirst($witness->spouse_side) }})</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <!-- Status & Audit -->
                        <div class="mb-4 pt-2">
                            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-200">
                                <i class="fas fa-chart-line text-teal-500 text-sm"></i>
                                <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Status & Audit</h3>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">System Status</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $marriage->getStatusBadgeClass() ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $marriage->system_status ?? '--' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Verification Status</p>
                                    <p class="text-gray-800">{{ $marriage->verificationStatus->name ?? '--' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Created By</p>
                                    <p class="text-gray-800">{{ $marriage->createdBy->name ?? '--' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Created At</p>
                                    <p class="text-gray-800 text-xs">{{ $marriage->created_at ? $marriage->created_at->format('d-m-Y H:i') : '--' }}</p>
                                </div>
                            </div>
                            @if($marriage->notes)
                                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs text-gray-500 mb-1">Notes</p>
                                    <p class="text-sm text-gray-700">{{ $marriage->notes }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Notes Section - Handles JSON data -->
                        <div class="mb-4 pt-2">
                            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-200">
                                <i class="fas fa-sticky-note text-gray-500 text-sm"></i>
                                <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Page Notes</h3>
                            </div>
                            
                            @php
                                $pageNotes = $pdfPage->notes;
                                $notesData = null;
                                $notesText = '';
                                
                                if (!empty($pageNotes)) {
                                    // Try to decode JSON
                                    if (is_string($pageNotes)) {
                                        $decoded = json_decode($pageNotes, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                            $notesData = $decoded;
                                            // Extract description if exists
                                            if (isset($notesData['description'])) {
                                                $notesText = $notesData['description'];
                                            } elseif (isset($notesData['latest_note'])) {
                                                $notesText = $notesData['latest_note'];
                                            }
                                        } else {
                                            $notesText = $pageNotes;
                                        }
                                    } else {
                                        $notesText = is_array($pageNotes) ? json_encode($pageNotes) : (string)$pageNotes;
                                    }
                                }
                            @endphp
                            
                            <!-- Display Notes -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                @if(!empty($notesText))
                                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $notesText }}</p>
                                @else
                                    <p class="text-sm text-gray-400 italic">No notes available</p>
                                @endif
                                
                                <!-- Display JSON structure nicely if there's more data -->
                                @if(is_array($notesData) && count($notesData) > 1)
                                    <div class="mt-3 pt-2 border-t border-gray-200">
                                        <details class="text-xs">
                                            <summary class="cursor-pointer text-gray-500 hover:text-gray-700">View additional details</summary>
                                            <div class="mt-2 p-2 bg-white rounded border border-gray-200">
                                                @foreach($notesData as $key => $value)
                                                    @if($key !== 'description' && $key !== 'latest_note')
                                                        <div class="mb-1">
                                                            <span class="font-medium text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                            @if(is_array($value))
                                                                <pre class="mt-1 text-xs text-gray-600 overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                            @else
                                                                <span class="text-gray-700 ml-1">{{ $value }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </details>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Inline Edit Form (Hidden by default, shown when Edit button clicked) -->
                    <div id="inlineEditForm" style="display: none;" class="mt-4 pt-4 border-t border-gray-200">
                        <form id="marriageEditForm" method="POST" action="{{ route('marriages.update', $marriage->id) }}">
                            @csrf
                            @method('PUT')

                            <!-- Hidden Fields - THESE ARE CRITICAL -->
                            <input type="hidden" name="pdf_page_id" value="{{ $pdfPage->id }}">
                            <input type="hidden" name="pdf_id" value="{{ $pdfUpload->id }}">
                            <input type="hidden" name="year" value="{{ $constants['year'] }}">
                            <input type="hidden" name="month" value="{{ $constants['month'] }}">
                            <input type="hidden" name="county" value="{{ $constants['county_name'] }}">
                            <input type="hidden" name="marriage_type_id" value="{{ $pdfUpload->marriage_type_id }}">
                            <input type="hidden" name="created_by" value="{{ Auth::id() }}">
                            <input type="hidden" name="verified_by" value="{{ Auth::id() }}">
                            
                            <!-- Validation Bypass Toggle -->
                            <div class="mb-5 p-4 rounded-lg border-2 transition-all border-gray-200 bg-gray-50" id="skipValidationContainer">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="relative">
                                            <input type="radio" name="has_errors_toggle" id="has_errors_true" value="1" class="w-5 h-5 text-red-600 focus:ring-red-500">
                                            <label for="has_errors_true" class="ml-2 text-sm font-medium text-red-700">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Has Errors / Bypass Validation
                                            </label>
                                        </div>
                                        <div class="relative ml-6">
                                            <input type="radio" name="has_errors_toggle" id="has_errors_false" value="0" checked class="w-5 h-5 text-green-600 focus:ring-green-500">
                                            <label for="has_errors_false" class="ml-2 text-sm font-medium text-green-700">
                                                <i class="fas fa-check-circle mr-1"></i> Complete / Validate All Fields
                                            </label>
                                        </div>
                                    </div>
                                    <div class="text-xs text-red-600" id="bypassWarning" style="display: none;">
                                        <i class="fas fa-info-circle mr-1"></i> Validation bypassed - record will be marked "Under Review"
                                    </div>
                                    <div class="text-xs text-green-600" id="validateInfo">
                                        <i class="fas fa-info-circle mr-1"></i> All required fields will be validated
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="skip_validation" id="skip_validation" value="0">
                            
                            <!-- Basic Information -->
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-800 mb-3">Basic Information</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500 block">Cert No</label>
                                        <input type="text" name="certificate_serial" value="{{ $marriage->certificate_serial ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 block">Entry No</label>
                                        <input type="text" name="entry_no" value="{{ $entryNo ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                    
                                    <div>
                                        <label class="text-xs text-gray-500 block">Marriage Date</label>
                                        <input type="date" name="marriage_date" value="{{ $marriage->marriage_date ? \Carbon\Carbon::parse($marriage->marriage_date)->format('Y-m-d') : '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 block">Registration Date</label>
                                        <input type="date" name="reg_date" value="{{ $marriage->reg_date ? \Carbon\Carbon::parse($marriage->reg_date)->format('Y-m-d') : '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">
                                    <div>
                                        <label class="text-xs text-gray-500 block">Marriage Type</label>
                                        <input type="text" value="{{ $pdfUpload->marriageType->name ?? '--' }}" readonly class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 block">License No</label>
                                        <input type="text" name="license_no" value="{{ $marriage->license_no ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                            <!-- Dynamic label based on marriage type -->
                                            <label class="text-xs text-gray-500 block">
                                                @php
                                                    $marriageTypeName = $pdfUpload->marriageType->name ?? '';
                                                    $officerLabel = 'Registrar Officer';
                                                    if ($marriageTypeName === 'Christian') {
                                                        $officerLabel = 'Pastor Name';
                                                    } elseif ($marriageTypeName === 'Muslim') {
                                                        $officerLabel = 'Muslim Officer';
                                                    }
                                                @endphp
                                                {{ $officerLabel }}
                                            </label>
                                            <input type="text" name="officer_name" id="officer_name" 
                                                @php
                                                    $marriageTypeName = $pdfUpload->marriageType->name ?? '';
                                                    $officerValue = '';
                                                    if ($marriage && $marriage->marriageExtension) {
                                                        if ($marriageTypeName === 'Muslim') {
                                                            $officerValue = $marriage->marriageExtension->muslim_officer ?? '';
                                                        } elseif ($marriageTypeName === 'Christian') {
                                                            $officerValue = $marriage->marriageExtension->pastor_name ?? '';
                                                        } elseif ($marriageTypeName === 'Hindu') {
                                                            $officerValue = $marriage->marriageExtension->registrar_officer ?? '';
                                                        } else {
                                                            // Civil and other types
                                                            $officerValue = $marriage->marriageExtension->registrar_officer ?? '';
                                                        }
                                                    }
                                                @endphp
                                                value="{{ $officerValue }}" 
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div> 
                                    <div class="col-span-2">
                                        <label class="text-xs text-gray-500 block">Venue</label>
                                        <input type="text" name="venue" value="{{ $marriage->venue ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Location Information -->
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-800 mb-3">Location Information</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500 block">County</label>
                                        <input type="text" value="{{ $marriage->county ?? '--' }}" readonly class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 block">Sub-County</label>
                                        <select name="sub_county" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <option value="">Select Constituency</option>
                                            @foreach($subCounties as $sub)
                                                <option value="{{ $sub->constituency }}"
                                                {{ trim(strtolower($marriage->sub_county ?? '')) == trim(strtolower($sub->constituency)) ? 'selected' : '' }}>
                                                {{ $sub->constituency }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 block">Ward</label>
                                        <select name="ward_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <option value="">Select Ward</option>
                                            @foreach($wards as $wardItem)
                                                @php
                                                    $wardId = is_object($wardItem) ? $wardItem->id : ($wardItem['id'] ?? '');
                                                    $wardName = is_object($wardItem) ? $wardItem->wards : ($wardItem['wards'] ?? '');
                                                @endphp
                                                <option value="{{ $wardId }}" {{ ($marriage->ward_id ?? '') == $wardId ? 'selected' : '' }}>
                                                    {{ $wardName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Spouses -->
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-800 mb-3">Spouses</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="border p-3 rounded-lg">
                                        <h5 class="font-medium text-blue-700 mb-2">Husband</h5>
                                        <div class="space-y-2">
                                            <input type="text" name="husband_name" placeholder="Full Name" value="{{ $husband->name ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="number" name="husband_age" placeholder="Age" value="{{ $husband->age ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                                <input type="text" name="husband_occupation" placeholder="Occupation" value="{{ $husband->occupation ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            </div>
                                            <input type="text" name="husband_residence" placeholder="Residence" value="{{ $husband->residence ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <select name="husband_marital_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                                <option value="">Marital Status</option>
                                                <option value="Bachelor" {{ ($husband->marital_status ?? '') == 'Bachelor' ? 'selected' : '' }}>Bachelor</option>
                                                <option value="Married" {{ ($husband->marital_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                                                <option value="Widowed" {{ ($husband->marital_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                                <option value="Divorced" {{ ($husband->marital_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                            </select>
                                            <input type="text" name="husband_father_name" placeholder="Father's Name" value="{{ $husband->father_name ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <input type="text" name="husband_father_occupation" placeholder="Father's Occupation" value="{{ $husband->father_occupation ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <input type="text" name="husband_father_residence" placeholder="Father's Residence" value="{{ $husband->father_residence ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <input type="text" name="husband_mother_name" placeholder="Mother's Name" value="{{ $husband->mother_name ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <input type="text" name="husband_mother_occupation" placeholder="Mother's Occupation" value="{{ $husband->mother_occupation ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <input type="text" name="husband_mother_residence" placeholder="Mother's Residence" value="{{ $husband->mother_residence ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                        </div>
                                    </div>
                                    <div class="border p-3 rounded-lg">
                                        <h5 class="font-medium text-pink-700 mb-2">Wife</h5>
                                        <div class="space-y-2">
                                            <input type="text" name="wife_name" placeholder="Full Name" value="{{ $wife->name ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="number" name="wife_age" placeholder="Age" value="{{ $wife->age ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                                <input type="text" name="wife_occupation" placeholder="Occupation" value="{{ $wife->occupation ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            </div>
                                            <input type="text" name="wife_residence" placeholder="Residence" value="{{ $wife->residence ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <select name="wife_marital_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                                <option value="">Marital Status</option>
                                                <option value="Spinster" {{ ($wife->marital_status ?? '') == 'Spinster' ? 'selected' : '' }}>Spinster</option>
                                                <option value="Married" {{ ($wife->marital_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                                                <option value="Widowed" {{ ($wife->marital_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                                <option value="Divorced" {{ ($wife->marital_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                            </select>
                                            <input type="text" name="wife_father_name" placeholder="Father's Name" value="{{ $wife->father_name ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <input type="text" name="wife_father_occupation" placeholder="Father's Occupation" value="{{ $wife->father_occupation ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">    
                                            <input type="text" name="wife_father_residence" placeholder="Father's Residence" value="{{ $wife->father_residence ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">    
                                            <input type="text" name="wife_mother_name" placeholder="Mother's Name" value="{{ $wife->mother_name ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <input type="text" name="wife_mother_occupation" placeholder="Mother's Occupation" value="{{ $wife->mother_occupation ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"> 
                                            <input type="text" name="wife_mother_residence" placeholder="Mother's Residence" value="{{ $wife->mother_residence ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Witnesses -->
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-800 mb-3">Witnesses</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs text-gray-500 block">Witness 1 Name</label>
                                        <input type="text" name="witness1_name" value="{{ $marriage->witnesses[0]->name ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 block">Witness 1 Side</label>
                                        <select name="witness1_side" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <option value="">Select Side</option>
                                            <option value="husband" {{ ($marriage->witnesses[0]->spouse_side ?? '') == 'husband' ? 'selected' : '' }}>Husband's Side</option>
                                            <option value="wife" {{ ($marriage->witnesses[0]->spouse_side ?? '') == 'wife' ? 'selected' : '' }}>Wife's Side</option>
                                            <option value="both" {{ ($marriage->witnesses[0]->spouse_side ?? '') == 'both' ? 'selected' : '' }}>Both Sides</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 block">Witness 2 Name</label>
                                        <input type="text" name="witness2_name" value="{{ $marriage->witnesses[1]->name ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 block">Witness 2 Side</label>
                                        <select name="witness2_side" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <option value="">Select Side</option>
                                            <option value="husband" {{ ($marriage->witnesses[1]->spouse_side ?? '') == 'husband' ? 'selected' : '' }}>Husband's Side</option>
                                            <option value="wife" {{ ($marriage->witnesses[1]->spouse_side ?? '') == 'wife' ? 'selected' : '' }}>Wife's Side</option>
                                            <option value="both" {{ ($marriage->witnesses[1]->spouse_side ?? '') == 'both' ? 'selected' : '' }}>Both Sides</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notes - This saves to pdf_pages.notes, NOT marriages -->
                            <div class="mb-4">
                                <label class="text-xs text-gray-500 block">Page Notes (will be saved to PDF page)</label>
                                <textarea name="page_notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ $pdfPage->notes ?? '' }}</textarea>
                            </div>
                            
                            <div class="flex gap-3 pt-3">
                                <button type="button" onclick="cancelInlineEdit()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-gray-200 space-y-3">
                        
                        @php
                            $userRole = auth()->user()->role->name ?? '';
                        @endphp

                        <!-- Marriage Teller Actions (when page needs review) -->
                        @if($userRole === 'marriage_teller' && $pdfPage->status === 'review_needed')
                            
                            <!-- Edit Button -->
                            <button type="button" 
                                    onclick="toggleInlineEdit()"
                                    id="editRecordBtn"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fas fa-edit mr-2 text-gray-500"></i> Review & Edit
                            </button>

                            <!-- Teller Action Buttons -->
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" 
                                        onclick="window.confirmPageAction('approve_completed', 'Approve this marriage record as complete?', 'Marriage Approved')"
                                        class="inline-flex justify-center items-center px-5 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
                                    <i class="fas fa-check-double mr-2"></i> Approve & Complete
                                </button>

                                <button type="button" 
                                        onclick="window.confirmPageAction('reject_to_clerk', 'Reject this marriage record back to clerk?', 'Marriage Rejected')"
                                        class="inline-flex justify-center items-center px-5 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors">
                                    <i class="fas fa-undo mr-2"></i> Reject to Clerk
                                </button>
                            </div>

                        <!-- Marriage Registrar Actions (when page is completed) -->
                        @elseif($userRole === 'marriage_registrar' && $pdfPage->status === 'completed')
                            
                            <!-- Edit Button (for registrar to make corrections) -->
                            <button type="button" 
                                    onclick="toggleInlineEdit()"
                                    id="editRecordBtn"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fas fa-edit mr-2 text-gray-500"></i> Edit Record
                            </button>

                            <!-- Registrar Action Buttons -->
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" 
                                        onclick="window.confirmPageAction('publish', 'Publish this marriage record? It will be publicly visible.', 'Record Published')"
                                        class="inline-flex justify-center items-center px-5 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-globe mr-2"></i> Publish
                                </button>

                                <button type="button" 
                                        onclick="window.confirmPageAction('send_back_to_teller', 'Send this page back to teller for review?', 'Sent Back to Teller')"
                                        class="inline-flex justify-center items-center px-5 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-yellow-700 bg-white hover:bg-yellow-50 transition-colors">
                                    <i class="fas fa-arrow-left mr-2"></i> Send Back to Teller
                                </button>
                            </div>
                            
                            <!-- Additional Registrar Action: Send to Data Clerk -->
                            <div class="grid grid-cols-1 gap-3">
                                <button type="button" 
                                        onclick="window.confirmPageAction('send_back_to_clerk', 'Send this page back to data clerk for corrections?', 'Sent Back to Clerk')"
                                        class="inline-flex justify-center items-center px-5 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors">
                                    <i class="fas fa-undo-alt mr-2"></i> Return to Data Clerk
                                </button>
                            </div>

                        <!-- Admin Actions (full control) -->
                        @elseif($userRole === 'admin')
                            
                            <!-- Edit Button -->
                            <button type="button" 
                                    onclick="toggleInlineEdit()"
                                    id="editRecordBtn"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fas fa-edit mr-2 text-gray-500"></i> Edit Record
                            </button>

                            <!-- Admin Action Buttons - All actions -->
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" 
                                        onclick="window.confirmPageAction('approve_completed', 'Approve this marriage record as complete?', 'Marriage Approved')"
                                        class="inline-flex justify-center items-center px-5 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
                                    <i class="fas fa-check-double mr-2"></i> Approve & Complete
                                </button>

                                <button type="button" 
                                        onclick="window.confirmPageAction('reject_to_clerk', 'Reject this marriage record back to clerk?', 'Marriage Rejected')"
                                        class="inline-flex justify-center items-center px-5 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors">
                                    <i class="fas fa-undo mr-2"></i> Reject to Clerk
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" 
                                        onclick="window.confirmPageAction('publish', 'Publish this marriage record? It will be publicly visible.', 'Record Published')"
                                        class="inline-flex justify-center items-center px-5 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-globe mr-2"></i> Publish
                                </button>

                                <button type="button" 
                                        onclick="window.confirmPageAction('send_back_to_teller', 'Send this page back to teller for review?', 'Sent Back to Teller')"
                                        class="inline-flex justify-center items-center px-5 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-yellow-700 bg-white hover:bg-yellow-50 transition-colors">
                                    <i class="fas fa-arrow-left mr-2"></i> Send Back to Teller
                                </button>
                            </div>

                        <!-- Default User Actions (when record has errors) -->
                        @else
                            
                            <!-- Edit Button -->
                            <button type="button" 
                                    onclick="toggleInlineEdit()"
                                    id="editRecordBtn"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fas fa-edit mr-2 text-gray-500"></i> Edit & Complete Record
                            </button>

                            <!-- Fix Errors Button (only if record has errors) -->
                            @if($marriage && $marriage->has_errors)
                                <a href="{{ route('marriages.edit', $marriage) }}?focus=errors" 
                                class="w-full inline-flex justify-center items-center px-4 py-3 border border-red-300 rounded-lg shadow-sm text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                                    <i class="fas fa-exclamation-triangle mr-2"></i> Fix Errors
                                </a>
                            @endif
                            
                        @endif
                    </div>

                @else
                    <!-- No Marriage Record - Show Entry Mode Tabs -->
                    <div class="space-y-6">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Entry Mode Tabs">
                                <button @click="entryMode = 'full'"
                                        :class="entryMode === 'full' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center">
                                    <i class="fas fa-file-alt mr-2"></i>
                                    Full Entry
                                </button>
                                <button @click="entryMode = 'quick'"
                                        :class="entryMode === 'quick' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center">
                                    <i class="fas fa-bolt mr-2"></i>
                                    Quick Entry
                                </button>
                            </nav>
                        </div>

                        <div x-show="entryMode === 'full'" x-cloak>
                            @include('partials.form.full-entry-form')
                        </div>
                        
                        <div x-show="entryMode === 'quick'" x-cloak>
                            @include('partials.form.quick-entry-form')
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Role-Based Action Buttons -->
        @php
            $userRole = auth()->user()->role->name ?? '';
        @endphp

        @if($userRole === 'data_clerk' && $pdfPage->status === 'in_progress')
            <!-- Data Clerk Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-tasks text-blue-500 mr-2"></i> Page Actions
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" 
                            onclick="window.confirmPageAction('submit_for_review', 'Submit this page for review?', 'Submitted for Review')"
                            class="inline-flex justify-center items-center px-4 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
                            <i class="fas fa-check-circle mr-2"></i> Submit for Review
                        </button>
                        <button type="button" 
                            onclick="window.confirmPageAction('skip_page', 'Skip this page?', 'Page Skipped')"
                            class="inline-flex justify-center items-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors">
                            <i class="fas fa-ban mr-2"></i> Skip Page
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($userRole === 'marriage_registrar' && $pdfPage->status === 'completed')
            <!-- Marriage Registrar Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-stamp text-indigo-500 mr-2"></i> Publishing Actions
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" 
                            onclick="window.confirmPageAction('publish', 'Publish this marriage record? It will be publicly visible.', 'Record Published')"
                            class="inline-flex justify-center items-center px-4 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            <i class="fas fa-globe mr-2"></i> Publish
                        </button>
                        <button type="button" 
                            onclick="window.confirmPageAction('send_back_to_teller', 'Send this page back to teller for review?', 'Sent Back')"
                            class="inline-flex justify-center items-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-yellow-700 bg-white hover:bg-yellow-50 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i> Send Back to Teller
                        </button>
                    </div>
                </div>
            </div>
        @endif
                
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

<!-- Include Modals -->
@include('partials.modal.alert-modal')
@include('partials.modal.success-modal')
@include('partials.modal.pdf-uploads-review')

<!-- JavaScript Functions -->
<script>
// Helper function to safely get Alpine component data (no __x dependency)
function getAlpineData(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return null;
    
    const dataStack = element._x_dataStack;
    if (dataStack && dataStack.length > 0) {
        return dataStack[0];
    }
    return null;
}

// Debounce utility for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// PDF Page Viewer Alpine Component
function pdfPageViewer() {
    return {
        pageStatus: '{{ $pdfPage->status }}',
        marriageDate: '{{ date('Y-m-d') }}',
        regDate: '{{ date('Y-m-d') }}',
        certificateSerial: '',
        selectedSubCounty: '',
        wards: [],
        loadingWards: false,
        wardError: false,
        wardErrorMessage: '',
        entryMode: 'full',
        showActionModal: false,
        currentAction: '',
        debouncedLoadWards: null,
        
        init() {
            console.log('PDF Page Viewer initialized');
            this.syncDates();
            this.initDatePickers();
            
            this.debouncedLoadWards = debounce(async (value) => {
                await this.loadWards(value);
            }, 300);
            
            this.$watch('selectedSubCounty', (value) => {
                this.debouncedLoadWards(value);
            });
        },
        
        syncDates() {
            const marriageInput = document.getElementById('marriage_date');
            const regInput = document.getElementById('reg_date');
            
            if (marriageInput && regInput) {
                if (!marriageInput.value) marriageInput.value = this.marriageDate;
                if (!regInput.value) regInput.value = this.regDate;
                
                const today = new Date().toISOString().split('T')[0];
                marriageInput.max = today;
                regInput.max = today;
                
                if (this.syncDateChange) {
                    marriageInput.removeEventListener('change', this.syncDateChange);
                    regInput.removeEventListener('change', this.syncDateChange);
                }
                
                this.syncDateChange = (e) => {
                    if (e.target.id === 'marriage_date') {
                        this.marriageDate = e.target.value;
                        const regInput = document.getElementById('reg_date');
                        if (regInput) regInput.value = e.target.value;
                        this.regDate = e.target.value;
                    } else if (e.target.id === 'reg_date') {
                        this.regDate = e.target.value;
                        const marriageInput = document.getElementById('marriage_date');
                        if (marriageInput) marriageInput.value = e.target.value;
                        this.marriageDate = e.target.value;
                    }
                };
                
                marriageInput.addEventListener('change', this.syncDateChange);
                regInput.addEventListener('change', this.syncDateChange);
            }
        },
        
        initDatePickers() {
            console.log('Date pickers initialized');
        },
        
        async loadWards(subCounty) {
            console.log('Loading wards for sub county:', subCounty);
            
            this.wards = [];
            this.wardError = false;
            this.wardErrorMessage = '';
            
            if (!subCounty) {
                return;
            }
            
            this.loadingWards = true;
            
            try {
                const response = await fetch(`{{ route('pdf-uploads.wards') }}?constituency=${encodeURIComponent(subCounty)}&county_code={{ $constants['county_code'] }}`);
                
                if (!response.ok) {
                    throw new Error('Failed to fetch wards');
                }
                
                const data = await response.json();
                console.log('Wards loaded:', data);
                this.wards = data;
                
            } catch (error) {
                console.error('Error loading wards:', error);
                this.wardError = true;
                this.wardErrorMessage = 'Failed to load wards. Please try again.';
            } finally {
                this.loadingWards = false;
            }
        },
        
        formatStatus(status) {
            if (!status) return 'Unknown';
            
            const statusMap = {
                'completed': 'Completed',
                'pending': 'Pending',
                'in_progress': 'In Progress',
                'assigned': 'Assigned',
                'review_needed': 'Review Needed',
                'skipped': 'Skipped',
                'published': 'Published'
            };
            
            return statusMap[status] || status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ');
        },
        
        getStatusClass(status) {
            if (!status) return 'bg-gray-100 text-gray-800';
            
            switch(status) {
                case 'completed': return 'bg-green-100 text-green-800';
                case 'pending': return 'bg-yellow-100 text-yellow-800';
                case 'in_progress': return 'bg-blue-100 text-blue-800';
                case 'assigned': return 'bg-indigo-100 text-indigo-800';
                case 'review_needed': return 'bg-orange-100 text-orange-800';
                case 'skipped': return 'bg-red-100 text-red-800';
                case 'published': return 'bg-purple-100 text-purple-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        },
        
        printPage() {
            const pdfFrame = document.getElementById('pdf-frame');
            if (pdfFrame && pdfFrame.contentWindow) {
                pdfFrame.contentWindow.focus();
                pdfFrame.contentWindow.print();
            }
        },
        
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
    }
}

// Make Alpine component available
document.addEventListener('alpine:init', () => {
    console.log('Alpine.js initialized');
    window.Alpine.data('pdfPageViewer', pdfPageViewer);
});

// Helper to safely remove loading div
function removeLoadingDiv(loadingDiv) {
    if (loadingDiv && document.body.contains(loadingDiv)) {
        document.body.removeChild(loadingDiv);
    }
}

// Global function to confirm and execute page actions using custom modals
window.confirmPageAction = function(action, confirmMessage, successTitle, customUrl = null) {
    const alertModalEl = document.querySelector('[x-data="alertModal()"]');
    if (alertModalEl && alertModalEl.__x) {
        alertModalEl.__x.$data.open({
            type: 'warning',
            title: 'Confirm Action',
            message: confirmMessage,
            confirmButtonText: 'Yes, Proceed',
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            onConfirm: () => {
                executePageAction(action, successTitle, customUrl);
            }
        });
    } else {
        if (confirm(confirmMessage)) {
            executePageAction(action, successTitle, customUrl);
        }
    }
};

// Get CSRF token safely
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

// Execute the actual page action
async function executePageAction(action, successTitle, customUrl = null) {
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'fixed inset-0 bg-black/50 z-[99999] flex items-center justify-center';
    loadingDiv.innerHTML = '<div class="bg-white rounded-lg p-6 text-center"><i class="fas fa-spinner fa-spin text-4xl text-purple-600"></i><p class="mt-2">Processing...</p></div>';
    document.body.appendChild(loadingDiv);
    
    const csrfToken = getCsrfToken();
    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', action);
    
    if (action === 'complete_page') {
        const notes = document.getElementById('completion_notes')?.value;
        if (notes) {
            formData.append('notes', notes);
        }
    }
    
    let url = '{{ route("pdf-pages.update-status", $pdfPage) }}';
    if (action === 'complete_page') {
        url = customUrl || '{{ route("pdf-pages.complete", $pdfPage) }}';
    }
    if (action === 'assign_to_me') {
        url = customUrl || '{{ route("pdf-pages.assign", $pdfPage) }}';
    }
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        });
        
        const result = await response.json();
        removeLoadingDiv(loadingDiv);
        
        if (result.success) {
            let details = '';
            if (action === 'publish') {
                details = `<div class="space-y-1 text-left"><div><strong>Record has been published!</strong></div><div>The marriage record is now publicly accessible.</div><div class="mt-2 text-green-600">✓ Status: Published</div></div>`;
            } else if (action === 'approve_completed') {
                details = `<div class="space-y-1 text-left"><div><strong>Marriage Record Approved!</strong></div><div>The record has been marked as completed.</div><div class="mt-2 text-green-600">✓ Ready for registrar review</div></div>`;
            } else if (action === 'complete_page') {
                details = `<div class="space-y-1 text-left"><div><strong>Page Completed!</strong></div><div>The page has been marked as completed.</div><div class="mt-2 text-green-600">✓ Ready for review</div></div>`;
            } else {
                details = `<div class="text-left">${escapeHtml(result.message)}</div>`;
            }
            
            const successModalEl = document.querySelector('[x-data="successModal()"]');
            if (successModalEl && successModalEl.__x) {
                successModalEl.__x.$data.open({
                    title: successTitle,
                    message: result.message,
                    details: details,
                    timer: 3
                });
            } else {
                alert(result.message);
            }
            
            setTimeout(() => {
                if (result.redirect_url) {
                    window.location.href = result.redirect_url;
                } else {
                    window.location.reload();
                }
            }, 3500);
        } else {
            const alertModalEl = document.querySelector('[x-data="alertModal()"]');
            if (alertModalEl && alertModalEl.__x) {
                alertModalEl.__x.$data.open({
                    type: 'error',
                    title: 'Error',
                    message: result.message || 'An error occurred',
                    autoClose: false
                });
            } else {
                alert('Error: ' + (result.message || 'Unknown error'));
            }
        }
    } catch (error) {
        removeLoadingDiv(loadingDiv);
        console.error('Error:', error);
        
        const alertModalEl = document.querySelector('[x-data="alertModal()"]');
        if (alertModalEl && alertModalEl.__x) {
            alertModalEl.__x.$data.open({
                type: 'error',
                title: 'Network Error',
                message: 'An error occurred: ' + error.message,
                autoClose: false
            });
        } else {
            alert('An error occurred: ' + error.message);
        }
    }
}

// Simple HTML escape to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Ensure DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = '{{ csrf_token() }}';
        document.head.appendChild(meta);
    }
});

// Toggle inline edit form
function toggleInlineEdit() {
    const editForm = document.getElementById('inlineEditForm');
    const viewContent = document.getElementById('viewModeContent');
    const editBtn = document.getElementById('editRecordBtn');
    
    if (editForm.style.display === 'none' || editForm.style.display === '') {
        editForm.style.display = 'block';
        if (viewContent) viewContent.style.display = 'none';
        editBtn.innerHTML = '<i class="fas fa-times mr-2"></i> Cancel Edit';
        editBtn.classList.remove('bg-white', 'hover:bg-gray-50');
        editBtn.classList.add('bg-red-50', 'hover:bg-red-100', 'text-red-700');
    } else {
        editForm.style.display = 'none';
        if (viewContent) viewContent.style.display = 'block';
        editBtn.innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Record';
        editBtn.classList.remove('bg-red-50', 'hover:bg-red-100', 'text-red-700');
        editBtn.classList.add('bg-white', 'hover:bg-gray-50');
    }
}

function cancelInlineEdit() {
    const editForm = document.getElementById('inlineEditForm');
    const viewContent = document.getElementById('viewModeContent');
    const editBtn = document.getElementById('editRecordBtn');
    
    editForm.style.display = 'none';
    if (viewContent) viewContent.style.display = 'block';
    editBtn.innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Record';
    editBtn.classList.remove('bg-red-50', 'hover:bg-red-100', 'text-red-700');
    editBtn.classList.add('bg-white', 'hover:bg-gray-50');
}

// Handle validation bypass toggle
document.addEventListener('DOMContentLoaded', function() {
    const trueRadio = document.getElementById('has_errors_true');
    const falseRadio = document.getElementById('has_errors_false');
    const bypassWarning = document.getElementById('bypassWarning');
    const validateInfo = document.getElementById('validateInfo');
    const skipValidationInput = document.getElementById('skip_validation');
    
    if (trueRadio && falseRadio) {
        trueRadio.addEventListener('change', function() {
            if (this.checked) {
                if (bypassWarning) bypassWarning.style.display = 'block';
                if (validateInfo) validateInfo.style.display = 'none';
                if (skipValidationInput) skipValidationInput.value = '1';
                const container = document.getElementById('skipValidationContainer');
                if (container) {
                    container.classList.add('border-red-300', 'bg-red-50');
                    container.classList.remove('border-gray-200', 'bg-gray-50');
                }
            }
        });
        
        falseRadio.addEventListener('change', function() {
            if (this.checked) {
                if (bypassWarning) bypassWarning.style.display = 'none';
                if (validateInfo) validateInfo.style.display = 'block';
                if (skipValidationInput) skipValidationInput.value = '0';
                const container = document.getElementById('skipValidationContainer');
                if (container) {
                    container.classList.remove('border-red-300', 'bg-red-50');
                    container.classList.add('border-gray-200', 'bg-gray-50');
                }
            }
        });
    }
    
    // Handle form submission
    const editForm = document.getElementById('marriageEditForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Update failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    }
});
</script>

<style>
    [x-cloak] { display: none !important; }
    .uppercase-field {
        text-transform: uppercase;
    }
    .uppercase-field::placeholder {
        text-transform: none;
    }
</style>

@push('scripts')
<script>
if (typeof Alpine === 'undefined') {
    console.warn('Alpine not loaded yet, waiting...');
    window.addEventListener('alpine:init', function() {
        console.log('Alpine initialized event fired');
    });
}
</script>
@endpush

@endsection