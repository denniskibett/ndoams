<!-- Marriage Sidebar -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-heart text-pink-500 mr-2"></i> Marriage Record
        </h2>
        <span id="selected-page-info" class="text-sm text-gray-500">Select a page</span>
    </div>
    
    <div class="p-6" id="marriage-content">
        <!-- Initial state -->
        <div class="text-center py-8" id="marriage-empty-state">
            <i class="fas fa-mouse-pointer text-gray-400 text-3xl mb-3"></i>
            <p class="text-gray-500">Click on a page to view or add marriage record</p>
        </div>
        
        <!-- Loading state -->
        <div class="text-center py-8 hidden" id="marriage-loading">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Loading marriage data...</p>
        </div>
        
        <!-- Marriage form -->
        <div id="marriage-form-container" class="hidden">
            <form id="marriage-form" method="POST" action="{{ route('marriages.store') }}">
                @csrf
                <input type="hidden" name="pdf_upload_id" id="pdf_upload_id" value="{{ $pdfUpload->id }}">
                <input type="hidden" name="pdf_page_id" id="pdf_page_id">
                <input type="hidden" name="page_number" id="form_page_number">
                <input type="hidden" name="county_code" value="{{ $pdfUpload->county_code }}">
                <input type="hidden" name="year" value="{{ $pdfUpload->year }}">
                <input type="hidden" name="month" value="{{ $pdfUpload->month }}">
                
                <!-- Form fields will be loaded via JavaScript -->
                <div id="marriage-form-fields"></div>
                
                <div class="mt-6">
                    <button type="submit" 
                            class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i> Save Marriage Record
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Existing marriage display -->
        <div id="marriage-display-container" class="hidden">
            <div id="marriage-display-content"></div>
            <div class="mt-4">
                <a id="edit-marriage-btn" 
                   class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-edit mr-2"></i> Edit Marriage Record
                </a>
            </div>
        </div>
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
        <div class="space-y-3">
            <button id="assign-page-btn" onclick="assignCurrentPage()" 
                    class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <i class="fas fa-user-check mr-2"></i> Assign This Page to Me
            </button>
            
            <button id="complete-page-btn" onclick="completeCurrentPage()" 
                    class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <i class="fas fa-check-circle mr-2"></i> Mark Page as Complete
            </button>
        </div>
        
        <!-- Constants Display -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Locked Constants</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">County:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $pdfUpload->county->name ?? $pdfUpload->county_code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Year:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $pdfUpload->year }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Month:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $pdfUpload->month }}</span>
                </div>
            </div>
        </div>
    </div>
</div>