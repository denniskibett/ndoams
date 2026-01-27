@extends('layouts.app')

@section('content')
<main>
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'Page {{ $page->page_number }} - {{ Str::limit($pdfUpload->name, 20) }}' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-6 bg-gray-50 min-h-screen">
        
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <nav class="flex mb-4" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                            <li class="inline-flex items-center">
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                    <i class="fas fa-home mr-2"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                                    <a href="{{ route('pdf-uploads.index') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">
                                        PDF Uploads
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                                    <a href="{{ route('pdf-uploads.show', $pdfUpload) }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">
                                        {{ Str::limit($pdfUpload->name, 20) }}
                                    </a>
                                </div>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                                    <span class="text-sm font-medium text-gray-500 truncate">Page {{ $page->page_number }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">
                            Page {{ $page->page_number }} - {{ $pdfUpload->name }}
                        </h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            {{ $page->status === 'completed' ? 'bg-green-100 text-green-800' : 
                               ($page->status === 'processing' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($page->status) }}
                        </span>
                    </div>
                    
                    <p class="text-sm text-gray-600 mt-2">
                        <i class="far fa-calendar mr-1"></i> 
                        County: {{ $constants['county_name'] }} | 
                        Period: {{ $constants['month'] }}/{{ $constants['year'] }} |
                        PDF: {{ $pdfUpload->total_pages }} total pages
                    </p>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('pdf-uploads.show', $pdfUpload) }}" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Back to PDF
                    </a>
                    
                    @if($page->status === 'pending' && auth()->user()->can('update', $page))
                    <form action="{{ route('pdf-uploads.page.assign', [$pdfUpload, $page->page_number]) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 shadow-sm">
                            <i class="fas fa-user-check mr-2"></i> Assign to Me
                        </button>
                    </form>
                    @endif
                    
                    @if($page->status === 'processing' && $page->assigned_to === auth()->id() && !$marriage)
                    <a href="{{ route('marriages.create') }}?pdf_upload_id={{ $constants['pdf_upload_id'] }}&pdf_page_id={{ $constants['pdf_page_id'] }}&page_number={{ $constants['page_number'] }}&county_code={{ $constants['county_code'] }}&year={{ $constants['year'] }}&month={{ $constants['month'] }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 shadow-sm">
                        <i class="fas fa-plus mr-2"></i> Add Marriage Record
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - PDF Viewer -->
            <div class="lg:col-span-2 space-y-6">
                <!-- PDF Viewer Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900">
                            <i class="far fa-file-pdf text-red-500 mr-2"></i> Page {{ $page->page_number }}
                        </h2>
                        <div class="text-sm text-gray-500">
                            Page {{ $page->page_number }} of {{ $pdfUpload->total_pages }}
                        </div>
                    </div>
                    
                    <!-- PDF Viewer Container -->
                    <div class="relative" style="height: 700px;">
                        @if($pageContent)
                            @if(strpos($pageContent, 'data:image/png;base64,') === 0)
                                <!-- PNG Image Display -->
                                <div class="p-4 flex justify-center items-center h-full bg-gray-100">
                                    <img src="{{ $pageContent }}" 
                                         alt="Page {{ $page->page_number }}" 
                                         class="max-w-full max-h-full shadow-lg border border-gray-300"
                                         id="page-image">
                                </div>
                            @elseif(strpos($pageContent, 'data:application/pdf;base64,') === 0)
                                <!-- PDF Display -->
                                @if ($pdfUpload->total_pages == 1)
                                    <!-- Single-page PDF: natural view -->
                                    <div style="width: 100%; height: 100vh; margin: 0; padding: 0; overflow: auto;">
                                        <iframe 
                                            src="{{ $pageContent }}"
                                            style="width: 100%; min-width: 800px; height: 100%; border: none;"
                                            allowfullscreen
                                            title="Page {{ $page->page_number }}"
                                            id="pdf-frame">
                                        </iframe>
                                    </div>
                                @else
                                    <!-- Multi-page PDF: scaled to page width -->
                                    <div style="width: 100%; height: 100vh; margin: 0; padding: 0; overflow: hidden;">
                                        <iframe 
                                            src="{{ $"
                                            style="width: 100%; height: 100%; border: none;"
                                            allowfullscreen
                                            title="Page {{ $page->page_number }}"
                                            id="pdf-frame">
                                        </iframe>
                                    </div>
                                @endif
                            @else
                                <!-- Fallback display -->
                                <div class="p-8 flex flex-col items-center justify-center h-full bg-gray-100">
                                    <i class="far fa-file-pdf text-gray-400 text-5xl mb-4"></i>
                                    <p class="text-gray-600">Page content loaded</p>
                                    <p class="text-sm text-gray-500 mt-2">Page {{ $page->page_number }}</p>
                                </div>
                            @endif
                        @else
                            <!-- No content available -->
                            <div class="p-8 flex flex-col items-center justify-center h-full bg-gray-100">
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
                                @if($page->page_number > 1)
                                <a href="{{ route('pdf-uploads.page.show', [$pdfUpload, $page->page_number - 1]) }}" 
                                   class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                                    <i class="fas fa-chevron-left mr-1.5"></i> Previous Page
                                </a>
                                @endif
                                
                                <div class="text-sm font-medium text-gray-700 px-3">
                                    Page {{ $page->page_number }} of {{ $pdfUpload->total_pages }}
                                </div>
                                
                                @if($page->page_number < $pdfUpload->total_pages)
                                <a href="{{ route('pdf-uploads.page.show', [$pdfUpload, $page->page_number + 1]) }}" 
                                   class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                                    Next Page <i class="fas fa-chevron-right ml-1.5"></i>
                                </a>
                                @endif
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('pdf-uploads.download', $pdfUpload) }}" 
                                   class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                                    <i class="fas fa-download mr-1.5"></i> Download PDF
                                </a>
                                
                                @if($pageContent && strpos($pageContent, 'data:application/pdf;base64,') === 0)
                                <button onclick="printPage()" 
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
                                            <span class="text-sm font-medium text-gray-900">{{ ucfirst($page->status) }}</span>
                                        </div>
                                        @if($page->assignedUser)
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Assigned To:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $page->assignedUser->name }}</span>
                                        </div>
                                        @endif
                                        @if($page->started_at)
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Started At:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $page->started_at->format('M d, Y h:i A') }}</span>
                                        </div>
                                        @endif
                                        @if($page->completed_at)
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Completed At:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $page->completed_at->format('M d, Y h:i A') }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($page->notes)
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 mb-2">Notes</h3>
                                    <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $page->notes }}</p>
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
                                            <span class="text-sm font-medium text-gray-900">{{ $constants['month'] }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Page Number:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $constants['page_number'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($page->status === 'processing' && $page->assigned_to === auth()->id())
                                <div class="pt-4 border-t border-gray-200">
                                    <form action="{{ route('pdf-uploads.page.complete', [$pdfUpload, $page->page_number]) }}" method="POST">
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
            </div>
            
            <!-- Right Column - Marriage Data & Actions -->
            <div class="space-y-6">
                <!-- Marriage Data Card -->
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
            
            @if($page->status === 'processing' && $page->assigned_to === auth()->id())
            <button onclick="toggleEditMode()" 
                    id="toggle-edit-btn"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-edit mr-1"></i> Quick Edit
            </button>
            @endif
        </div>
    </div>
    
    <div class="p-6" id="marriage-data-container">
        @if($marriage)
<!-- Relationship info -->
<div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
    <h4 class="text-sm font-medium text-blue-900 mb-2 flex items-center">
        <i class="fas fa-link mr-2"></i> PDF Linkage
    </h4>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-medium text-blue-700 mb-1">PDF Document</label>
            @if($marriage->pdf_id)
            <div class="flex items-center">
                <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                <a href="{{ route('pdf-uploads.show', $marriage->pdf_id) }}" 
                   class="text-sm text-blue-600 hover:text-blue-800 truncate">
                    {{ $marriage->pdfUpload->name ?? 'PDF #' . $marriage->pdf_id }}
                </a>
            </div>
            @else
            <span class="text-sm text-gray-500">Not linked to PDF</span>
            @endif
        </div>
        <div>
            <label class="block text-xs font-medium text-blue-700 mb-1">PDF Page</label>
            @if($marriage->pdf_page_id)
            <div class="flex items-center">
                <i class="fas fa-file-alt text-green-500 mr-2"></i>
                <span class="text-sm text-gray-900">
                    Page {{ $marriage->pdfPage->page_number ?? 'N/A' }}
                </span>
            </div>
            @else
            <span class="text-sm text-gray-500">Not linked to specific page</span>
            @endif
        </div>
    </div>
    
    <!-- Debug info (remove in production) -->
    <div class="mt-2 pt-2 border-t border-blue-200">
        <details class="text-xs">
            <summary class="cursor-pointer text-blue-600 hover:text-blue-800">Debug Info</summary>
            <div class="mt-1 p-2 bg-white rounded border border-blue-200">
                <div class="grid grid-cols-2 gap-2">
                    <div>Marriage ID: <code class="text-xs">{{ $marriage->id }}</code></div>
                    <div>PDF ID: <code class="text-xs">{{ $marriage->pdf_id ?? 'null' }}</code></div>
                    <div>PDF Page ID: <code class="text-xs">{{ $marriage->pdf_page_id ?? 'null' }}</code></div>
                    <div>Image ID: <code class="text-xs">{{ $marriage->image_id ?? 'null' }}</code></div>
                </div>
            </div>
        </details>
    </div>
</div>
@endif
        @if($marriage)
            <!-- View Mode for Existing Marriage -->
            <div id="marriage-view-mode">
                <div class="space-y-4">
                    <div>
                        <h3 class="font-medium text-gray-900">{{ $marriage->certificate_serial ?? 'No Marriage Number' }}</h3>
                        <p class="text-sm text-gray-600 mt-1">Marriage Record</p>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-blue-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-600">Completion</p>
                            <p class="text-sm font-bold text-blue-700">{{ $marriage->calculateCompletionRate() }}%</p>
                        </div>
                        <div class="bg-green-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-600">Status</p>
                            <p class="text-sm font-bold text-green-700">{{ $marriage->system_status }}</p>
                        </div>
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
                                <p class="text-sm text-gray-900">{{ $marriage->marriage_date->format('Y-m-d') }}</p>
                            </div>
                            @endif
                            
                            @if($marriage->venue)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Venue</label>
                                <p class="text-sm text-gray-900">{{ $marriage->venue }}</p>
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
                            @if($marriage->husband)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Husband</label>
                                <p class="text-sm text-gray-900">{{ $marriage->husband->full_name ?? $marriage->husband->name ?? 'N/A' }}</p>
                            </div>
                            @endif
                            
                            @if($marriage->wife)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Wife</label>
                                <p class="text-sm text-gray-900">{{ $marriage->wife->full_name ?? $marriage->wife->name ?? 'N/A' }}</p>
                            </div>
                            @endif
                            
                            @if($marriage->registration_date)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Registration Date</label>
                                <p class="text-sm text-gray-900">{{ $marriage->registration_date->format('Y-m-d') }}</p>
                            </div>
                            @endif
                            
                            @if($marriage->created_at)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Record Created</label>
                                <p class="text-sm text-gray-900">{{ $marriage->created_at->format('M d, Y \a\t h:i A') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200 flex space-x-3">
                        <a href="{{ route('marriages.edit', $marriage) }}" 
                           class="flex-1 inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-edit mr-2"></i> Full Edit
                        </a>
                        <button onclick="quickEditMarriage({{ $marriage->id }})" 
                                class="flex-1 inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-bolt mr-2"></i> Quick Edit
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Quick Edit Form (Hidden by default) -->
            <div id="marriage-quick-edit-form" class="hidden">
                <form id="quick-edit-marriage-form" data-marriage-id="{{ $marriage->id }}">
                    @csrf
                    <input type="hidden" name="pdf_page_id" value="{{ $page->id }}">
    <input type="hidden" name="pdf_upload_id" value="{{ $pdfUpload->id }}">
    <input type="hidden" name="page_number" value="{{ $page->page_number }}">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Certificate Serial *</label>
                                <input type="text" name="certificate_serial" value="{{ $marriage->certificate_serial }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Marriage Date *</label>
                                <input type="date" name="marriage_date" value="{{ optional($marriage->marriage_date)->format('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Venue *</label>
                                <input type="text" name="venue" value="{{ $marriage->venue }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">County *</label>
                                <input type="text" name="county" value="{{ $marriage->county }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                        </div>
                        
                        <!-- Spouse Information -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Spouse Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @php
                                    $husband = $marriage->spouses->firstWhere('gender', 'male');
                                    $wife = $marriage->spouses->firstWhere('gender', 'female');
                                @endphp
                                
                                <div class="space-y-3">
                                    <h5 class="text-xs font-medium text-gray-700">Husband</h5>
                                    <input type="text" name="husband_name" placeholder="Husband's Name" 
                                           value="{{ $husband->name ?? '' }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div class="space-y-3">
                                    <h5 class="text-xs font-medium text-gray-700">Wife</h5>
                                    <input type="text" name="wife_name" placeholder="Wife's Name" 
                                           value="{{ $wife->name ?? '' }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-200 flex space-x-3">
                            <button type="submit" 
                                    class="flex-1 inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                            <button type="button" onclick="toggleEditMode()"
                                    class="flex-1 inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
        @else
            <!-- No Marriage Record - Show Form -->
            <div id="marriage-create-form">
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-600">Total Pages</p>
                            <p class="text-lg font-bold text-blue-700">{{ $pdfUpload->total_pages }}</p>
                        </div>
                        <div class="bg-green-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-600">Linked Pages</p>
                            <p class="text-lg font-bold text-green-700">{{ $pdfUpload->pages()->whereHas('marriage')->count() }}</p>
                        </div>
                        <div class="bg-yellow-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-600">Pending Pages</p>
                            <p class="text-lg font-bold text-yellow-700">{{ $pdfUpload->pages()->where('status', 'pending')->count() }}</p>
                        </div>
                        <div class="bg-purple-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-600">Completion</p>
                            <p class="text-lg font-bold text-purple-700">{{ $pdfUpload->completionPercentage() }}%</p>
                        </div>
                    </div>
                    
                    <!-- Quick Create Form -->
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Quick Create Marriage Record</h4>
                        <form id="quick-create-form">
                            @csrf
                            <input type="hidden" name="pdf_page_id" value="{{ $page->id }}">
                            <input type="hidden" name="pdf_upload_id" value="{{ $pdfUpload->id }}">
                            <input type="hidden" name="page_number" value="{{ $page->page_number }}">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Certificate Serial *</label>
                                    <input type="text" name="certificate_serial" id="certificate_serial"
                                           value="{{ strtoupper(substr($constants['county_name'], 0, 3)) }}-{{ $constants['year'] }}-{{ str_pad($constants['month'], 2, '0', STR_PAD_LEFT) }}-P{{ str_pad($page->page_number, 3, '0', STR_PAD_LEFT) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Marriage Date *</label>
                                    <input type="date" name="marriage_date" value="{{ date('Y-m-d') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Venue *</label>
                                    <input type="text" name="venue" value="{{ $constants['county_name'] }} Registry"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">County *</label>
                                    <input type="text" name="county" value="{{ $constants['county_name'] }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                </div>
                            </div>
                            
                            <!-- Spouse Quick Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Husband's Name</label>
                                    <input type="text" name="husband_name" placeholder="Enter husband's name"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Wife's Name</label>
                                    <input type="text" name="wife_name" placeholder="Enter wife's name"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            
                            <div class="flex space-x-3">
                                <button type="submit" 
                                        class="flex-1 inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                    <i class="fas fa-save mr-2"></i> Quick Save
                                </button>
                                <a href="{{ route('marriages.create') }}?pdf_upload_id={{ $constants['pdf_upload_id'] }}&pdf_page_id={{ $constants['pdf_page_id'] }}&page_number={{ $constants['page_number'] }}&county_code={{ $constants['county_code'] }}&year={{ $constants['year'] }}&month={{ $constants['month'] }}" 
                                   class="flex-1 inline-flex justify-center items-center px-4 py-2.5 border border-blue-300 rounded-lg shadow-sm text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100">
                                    <i class="fas fa-external-link-alt mr-2"></i> Full Form
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Related Records -->
                    @php
                        $samePdfMarriages = \App\Models\Marriage::where('pdf_id', $pdfUpload->id)
                            ->with(['spouses'])
                            ->orderBy('created_at', 'desc')
                            ->limit(3)
                            ->get();
                    @endphp
                    
                    @if($samePdfMarriages->count() > 0)
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">
                            <i class="fas fa-file-pdf text-red-500 mr-2"></i> Other Records in This PDF
                        </h4>
                        <div class="space-y-2">
                            @foreach($samePdfMarriages as $relatedMarriage)
                            <a href="{{ route('marriages.show', $relatedMarriage) }}" 
                               class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-blue-50 group">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-heart text-blue-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600">
                                            {{ $relatedMarriage->certificate_serial }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ optional($relatedMarriage->marriage_date)->format('Y-m-d') ?? 'No date' }}
                                            • {{ $relatedMarriage->spouses->count() }} spouses
                                        </p>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600"></i>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
                
                <!-- Quick Actions Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('pdf-uploads.show', $pdfUpload) }}" 
                               class="inline-flex flex-col items-center justify-center p-4 border border-gray-300 rounded-xl text-center hover:bg-gray-50 transition-colors">
                                <i class="fas fa-list text-blue-600 text-xl mb-2"></i>
                                <span class="text-sm font-medium text-gray-900">All Pages</span>
                                <span class="text-xs text-gray-500 mt-1">View PDF</span>
                            </a>
                            
                            <button onclick="copyPageLink()" 
                                    class="inline-flex flex-col items-center justify-center p-4 border border-gray-300 rounded-xl text-center hover:bg-gray-50 transition-colors">
                                <i class="fas fa-link text-green-600 text-xl mb-2"></i>
                                <span class="text-sm font-medium text-gray-900">Copy Link</span>
                                <span class="text-xs text-gray-500 mt-1">This Page</span>
                            </button>
                            
                            @if($pageContent && strpos($pageContent, 'data:application/pdf;base64,') === 0)
                            <button onclick="downloadPage()" 
                                    class="inline-flex flex-col items-center justify-center p-4 border border-gray-300 rounded-xl text-center hover:bg-gray-50 transition-colors">
                                <i class="fas fa-download text-purple-600 text-xl mb-2"></i>
                                <span class="text-sm font-medium text-gray-900">Download</span>
                                <span class="text-xs text-gray-500 mt-1">This Page</span>
                            </button>
                            @endif
                            
                            <a href="#page-information" 
                               class="inline-flex flex-col items-center justify-center p-4 border border-gray-300 rounded-xl text-center hover:bg-gray-50 transition-colors">
                                <i class="fas fa-info-circle text-gray-600 text-xl mb-2"></i>
                                <span class="text-sm font-medium text-gray-900">Details</span>
                                <span class="text-xs text-gray-500 mt-1">Page Info</span>
                            </a>
                        </div>
                        
                        <!-- Navigation Help -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Navigation</h3>
                            <div class="flex items-center justify-between">
                                @if($page->page_number > 1)
                                <a href="{{ route('pdf-uploads.page.show', [$pdfUpload, $page->page_number - 1]) }}" 
                                   class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                    <i class="fas fa-arrow-left mr-1"></i> Page {{ $page->page_number - 1 }}
                                </a>
                                @else
                                <span class="text-sm text-gray-400">First Page</span>
                                @endif
                                
                                <span class="text-sm font-medium text-gray-700">Current: {{ $page->page_number }}</span>
                                
                                @if($page->page_number < $pdfUpload->total_pages)
                                <a href="{{ route('pdf-uploads.page.show', [$pdfUpload, $page->page_number + 1]) }}" 
                                   class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                    Page {{ $page->page_number + 1 }} <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                                @else
                                <span class="text-sm text-gray-400">Last Page</span>
                                @endif
                            </div>
                        </div>
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
                            @php
                                $nearbyPages = $pdfUpload->pages()
                                    ->whereBetween('page_number', [max(1, $page->page_number - 3), min($pdfUpload->total_pages, $page->page_number + 3)])
                                    ->where('id', '!=', $page->id)
                                    ->orderBy('page_number')
                                    ->get();
                            @endphp
                            
                            @if($nearbyPages->count() > 0)
                                @foreach($nearbyPages as $nearbyPage)
                                <a href="{{ route('pdf-uploads.page.show', [$pdfUpload, $nearbyPage->page_number]) }}" 
                                   class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors group">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3
                                            {{ $nearbyPage->status === 'completed' ? 'bg-green-100 text-green-700' : 
                                               ($nearbyPage->status === 'processing' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                                            {{ $nearbyPage->page_number }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600">
                                                Page {{ $nearbyPage->page_number }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ ucfirst($nearbyPage->status) }}
                                                @if($nearbyPage->marriage)
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
    </div>
</main>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any page-specific JavaScript here
        
        // Handle image zoom if it's an image
        const pageImage = document.getElementById('page-image');
        if (pageImage) {
            pageImage.addEventListener('click', function() {
                this.classList.toggle('cursor-zoom-in');
                if (this.style.transform === 'scale(1.5)') {
                    this.style.transform = 'scale(1)';
                    this.style.cursor = 'zoom-in';
                } else {
                    this.style.transform = 'scale(1.5)';
                    this.style.cursor = 'zoom-out';
                }
            });
        }
    });
    
    function printPage() {
        const pdfFrame = document.getElementById('pdf-frame');
        if (pdfFrame) {
            pdfFrame.contentWindow.print();
        }
    }
    
    function downloadPage() {
        // This would need server-side implementation to download single page
        alert('Page download feature would be implemented here');
    }
    
    function copyPageLink() {
        const pageUrl = window.location.href;
        navigator.clipboard.writeText(pageUrl).then(() => {
            showToast('Page link copied to clipboard!', 'success');
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }
    
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg text-white z-50 transition-all duration-300 ${
            type === 'success' ? 'bg-green-500' : 'bg-blue-500'
        }`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Left arrow for previous page
        if (e.key === 'ArrowLeft' && {{ $page->page_number }} > 1) {
            window.location.href = "{{ route('pdf-uploads.page.show', [$pdfUpload, $page->page_number - 1]) }}";
        }
        // Right arrow for next page
        if (e.key === 'ArrowRight' && {{ $page->page_number }} < {{ $pdfUpload->total_pages }}) {
            window.location.href = "{{ route('pdf-uploads.page.show', [$pdfUpload, $page->page_number + 1]) }}";
        }
        // Escape to go back to PDF
        if (e.key === 'Escape') {
            window.location.href = "{{ route('pdf-uploads.show', $pdfUpload) }}";
        }
    });
</script>

<script>
// Toggle between view and edit modes
function toggleEditMode() {
    const viewMode = document.getElementById('marriage-view-mode');
    const editForm = document.getElementById('marriage-quick-edit-form');
    const toggleBtn = document.getElementById('toggle-edit-btn');
    
    if (viewMode && editForm) {
        const isEditing = viewMode.classList.contains('hidden');
        
        if (isEditing) {
            // Switch to view mode
            viewMode.classList.remove('hidden');
            editForm.classList.add('hidden');
            toggleBtn.innerHTML = '<i class="fas fa-edit mr-1"></i> Quick Edit';
        } else {
            // Switch to edit mode
            viewMode.classList.add('hidden');
            editForm.classList.remove('hidden');
            toggleBtn.innerHTML = '<i class="fas fa-eye mr-1"></i> View Mode';
        }
    }
}

// Quick edit existing marriage
function quickEditMarriage(marriageId) {
    // Show the quick edit form
    const viewMode = document.getElementById('marriage-view-mode');
    const editForm = document.getElementById('marriage-quick-edit-form');
    const toggleBtn = document.getElementById('toggle-edit-btn');
    
    if (viewMode && editForm) {
        viewMode.classList.add('hidden');
        editForm.classList.remove('hidden');
        toggleBtn.innerHTML = '<i class="fas fa-eye mr-1"></i> View Mode';
    }
}

// Handle quick create form submission
document.addEventListener('DOMContentLoaded', function() {
    // Quick Create Form
    const quickCreateForm = document.getElementById('quick-create-form');
    if (quickCreateForm) {
        quickCreateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('{{ route("marriages.quick-create-from-page") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Marriage record created successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to create record.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while saving.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        });
    }
    
    // Quick Edit Form
    const quickEditForm = document.getElementById('quick-edit-marriage-form');
    if (quickEditForm) {
        quickEditForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const marriageId = this.getAttribute('data-marriage-id');
            const formData = new FormData(this);
            
            fetch(`/marriages/${marriageId}/quick-update`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Marriage record updated successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to update record.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while updating.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        });
    }
    
    // Auto-fill certificate serial on focus
    const certSerialInput = document.getElementById('certificate_serial');
    if (certSerialInput && !certSerialInput.value) {
        certSerialInput.addEventListener('focus', function() {
            if (!this.value) {
                const county = "{{ $constants['county_name'] }}".substring(0, 3).toUpperCase();
                const year = {{ $constants['year'] }};
                const month = "{{ $constants['month'] }}".padStart(2, '0');
                const pageNum = {{ $page->page_number }}.toString().padStart(3, '0');
                this.value = `${county}-${year}-${month}-P${pageNum}`;
            }
        });
    }
});

// Generate certificate serial
function generateCertificateSerial() {
    const county = "{{ $constants['county_name'] }}".substring(0, 3).toUpperCase();
    const year = {{ $constants['year'] }};
    const month = "{{ $constants['month'] }}".padStart(2, '0');
    const pageNum = {{ $page->page_number }}.toString().padStart(3, '0');
    return `${county}-${year}-${month}-P${pageNum}`;
}
</script>

<style>
    #page-image {
        transition: transform 0.3s ease;
        cursor: zoom-in;
    }
    
    #page-image:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush
@endsection