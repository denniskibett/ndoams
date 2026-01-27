<!-- PDF Viewer -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-900">
            <i class="far fa-file-pdf text-red-500 mr-2"></i> PDF Viewer
        </h2>
        <div class="text-sm text-gray-500" id="page-info">
            Page <span id="current-page">1</span> of {{ $pdfUpload->total_pages }}
        </div>
    </div>
    
    <!-- PDF Display -->
    <div class="relative" style="height: 600px;">
        <iframe 
            id="pdf-iframe"
            src="{{ route('pdf-uploads.preview', $pdfUpload) }}#page=1"
            style="width: 100%; height: 100%; border: none;"
            allowfullscreen
            title="PDF Viewer"
        ></iframe>
        
        <!-- Fallback if iframe doesn't load -->
        <div id="pdf-fallback" class="hidden absolute inset-0 flex items-center justify-center bg-gray-100">
            <div class="text-center p-8">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">PDF Cannot Be Displayed</h3>
                <p class="text-gray-600 mb-4">Your browser may be blocking PDF display.</p>
                <a href="{{ route('pdf-uploads.download', $pdfUpload) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-download mr-2"></i> Download PDF
                </a>
            </div>
        </div>
    </div>
    
    <!-- PDF Controls -->
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center space-x-2">
                <button id="prev-page" class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left mr-1.5"></i> Previous
                </button>
                <div class="flex items-center space-x-2">
                    <input type="number" id="page-number" min="1" max="{{ $pdfUpload->total_pages }}" 
                           value="1"
                           class="w-16 px-2 py-1.5 border border-gray-300 rounded text-center text-sm">
                    <span class="text-sm font-medium text-gray-700">of {{ $pdfUpload->total_pages }}</span>
                </div>
                <button id="next-page" class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    Next <i class="fas fa-chevron-right ml-1.5"></i>
                </button>
            </div>
            
            <div class="flex items-center space-x-3">
                <a href="{{ route('pdf-uploads.download', $pdfUpload) }}" 
                   class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                    <i class="fas fa-download mr-1.5"></i> Download
                </a>
                <button onclick="printPDF()" 
                        class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                    <i class="fas fa-print mr-1.5"></i> Print
                </button>
                <button onclick="toggleFullscreen()" 
                        class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                    <i class="fas fa-expand mr-1.5"></i> Fullscreen
                </button>
            </div>
        </div>
    </div>
</div>