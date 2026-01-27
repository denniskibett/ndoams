<!-- resources/views/marriages/create-from-pdf.blade.php -->
@extends('layouts.app')

@section('content')
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-6 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
  <!-- Page Header -->
  <div class="flex flex-col gap-2 mb-6 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90" id="pageTitle">Create Marriage Record from PDF</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" id="pageDescription">
        Create a new marriage record using certificate PDF
      </p>
    </div>
    
    <div class="flex items-center gap-2">
      <!-- Edit/Update Button -->
      <button type="button" id="editModeToggle" class="hidden inline-flex items-center gap-2 rounded-lg border border-blue-300 bg-blue-50 px-4 py-2.5 text-sm font-medium text-blue-700 shadow-theme-xs hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Edit Mode
      </button>
      
      <a href="{{ route('marriages.index') }}" 
         class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m-7 7h18" />
        </svg>
        Back to Marriages
      </a>
    </div>
  </div>

  <!-- Edit Mode Alert -->
  <div id="editModeAlert" class="hidden mb-6">
    <div class="rounded-xl border border-blue-500 bg-blue-50 p-4 dark:border-blue-500/30 dark:bg-blue-500/15">
      <div class="flex items-start gap-3">
        <div class="-mt-0.5 text-blue-500">
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div class="flex-1">
          <h4 class="mb-1 text-sm font-semibold text-gray-800 dark:text-white/90">Editing Existing Record</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            You are editing an existing marriage record linked to this PDF. 
            Changes will update the existing record. <span class="font-semibold">Marriage ID: <span id="existingMarriageId"></span></span>
          </p>
        </div>
        <button type="button" onclick="toggleEditMode(false)" class="text-blue-500 hover:text-blue-700">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Alert for Successful Save -->
  <div id="successAlert" class="hidden mb-6">
    <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
      <div class="flex items-center gap-3">
        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <div class="flex-1">
          <h4 class="text-sm font-semibold text-green-800 dark:text-green-300">Data Saved Successfully!</h4>
          <p id="savedFields" class="text-sm text-green-700 dark:text-green-400 mt-1"></p>
        </div>
        <button type="button" onclick="document.getElementById('successAlert').classList.add('hidden')" class="text-green-500 hover:text-green-700">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Form Completion with Color -->
  <div class="mb-6" id="completionContainer">
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Form Completion</span>
      <span id="completionPercentage" class="text-sm font-semibold text-primary-600 dark:text-primary-400">0%</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
      <div id="completionBar" class="h-2 rounded-full transition-all duration-300" style="width: 0%; background-color: #ef4444"></div>
    </div>
    <!-- Completion Alert -->
    <div id="completionAlert" class="mt-3 hidden">
      <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-3 dark:border-yellow-800 dark:bg-yellow-900/20">
        <div class="flex items-center gap-2">
          <svg class="h-4 w-4 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
          </svg>
          <p class="text-sm text-yellow-700 dark:text-yellow-300">
            <span id="alertMessage"></span>
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-8 xl:grid-cols-12">
    <!-- PDF Preview - 7/12 width -->
    <div class="xl:col-span-7">
      <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50 h-full">
        <div class="flex flex-col h-full">
          <!-- PDF Header -->
          <div class="flex items-center justify-between mb-4">
            <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Certificate PDF Viewer</h4>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-sm font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                </svg>
                PDF Document
              </span>
              <span id="pdfStatusBadge" class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                Loading...
              </span>
            </div>
          </div>
          
          <!-- PDF Display - Scrollable iframe -->
          <div class="flex-1 flex flex-col bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-900 overflow-hidden">
            @if($pdf->storage_path && Storage::disk('public')->exists($pdf->storage_path))
              <div class="w-full h-full flex flex-col">
                <!-- PDF Preview - Scrollable container -->
                <div class="flex-1 overflow-hidden">
                  <div class="text-sm text-gray-600 dark:text-gray-400 mb-2 px-4 pt-4">PDF Preview:</div>
                  <div class="h-[calc(100%-2rem)] overflow-y-auto px-4 pb-4">
                    <iframe 
                      src="{{ route('pdf-uploads.preview', $pdf) }}#toolbar=0&view=FitH&navpanes=0" 
                      class="w-full min-h-[800px] rounded border border-gray-300 dark:border-gray-600"
                      frameborder="0"
                      id="pdfPreview"
                      style="object-fit: contain;"
                    >
                    </iframe>
                  </div>
                </div>
                
                <!-- PDF Information -->
                <div class="mt-auto border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-4 py-3">
                  <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <span class="font-medium text-gray-500 dark:text-gray-400">PDF ID:</span>
                      <span class="text-gray-900 dark:text-white ml-2">{{ $pdf->id }}</span>
                    </div>
                    <div>
                      <span class="font-medium text-gray-500 dark:text-gray-400">File Size:</span>
                      <span class="text-gray-900 dark:text-white ml-2">{{ number_format(floatval($pdf->file_size), 2) }} KB</span>
                    </div>
                    <div>
                      <span class="font-medium text-gray-500 dark:text-gray-400">Uploaded By:</span>
                      <span class="text-gray-900 dark:text-white ml-2">{{ $pdf->uploader->name ?? 'Unknown' }}</span>
                    </div>
                    <div>
                      <span class="font-medium text-gray-500 dark:text-gray-400">Upload Date:</span>
                      <span class="text-gray-900 dark:text-white ml-2">{{ $pdf->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($pdf->total_pages)
                    <div>
                      <span class="font-medium text-gray-500 dark:text-gray-400">Total Pages:</span>
                      <span class="text-gray-900 dark:text-white ml-2">{{ $pdf->total_pages }}</span>
                    </div>
                    @endif
                    <div>
                      <span class="font-medium text-gray-500 dark:text-gray-400">County:</span>
                      <span class="text-gray-900 dark:text-white ml-2">
                        {{ $pdf->county->name ?? $pdf->county_code ?? 'Nairobi' }}
                      </span>
                    </div>
                  </div>
                  <!-- Linked Marriage Info -->
                  <div id="linkedMarriageInfo" class="hidden mt-4 pt-4 border-t border-gray-300 dark:border-gray-700">
                    <h5 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Linked Marriage Record</h5>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <span class="font-medium text-gray-500 dark:text-gray-400">Marriage ID:</span>
                        <span class="text-gray-900 dark:text-white ml-2" id="linkedMarriageId"></span>
                      </div>
                      <div>
                        <span class="font-medium text-gray-500 dark:text-gray-400">Certificate Serial:</span>
                        <span class="text-gray-900 dark:text-white ml-2" id="linkedCertificateSerial"></span>
                      </div>
                      <div>
                        <span class="font-medium text-gray-500 dark:text-gray-400">Status:</span>
                        <span class="text-gray-900 dark:text-white ml-2" id="linkedStatus"></span>
                      </div>
                      <div>
                        <span class="font-medium text-gray-500 dark:text-gray-400">Created:</span>
                        <span class="text-gray-900 dark:text-white ml-2" id="linkedCreatedAt"></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="flex flex-col items-center justify-center h-full text-center text-gray-500 dark:text-gray-400 p-8">
                <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h4 class="text-lg font-medium mb-2">PDF Not Found</h4>
                <p class="text-sm">The certificate PDF could not be loaded from storage.</p>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Marriage Form - 5/12 width -->
    <div class="xl:col-span-5 space-y-6">
      <!-- Highlighted Locked Information Card -->
      <div class="rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-700 dark:bg-blue-900/20 p-4">
        <div class="flex items-center gap-2 mb-3">
          <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
          </svg>
          <h4 class="text-md font-semibold text-blue-800 dark:text-blue-300">Record Information (Locked)</h4>
        </div>
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">County</label>
            <input type="text" 
                   class="w-full rounded-lg border border-blue-200 bg-blue-100 px-4 py-2 text-sm font-medium text-blue-800 dark:border-blue-700 dark:bg-blue-900 dark:text-blue-300 cursor-not-allowed" 
                   value="{{ $pdf->county->name ?? $pdf->county_code ?? 'Nairobi' }}" 
                   readonly>
          </div>
          <div>
            <label class="block text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">Year</label>
            <input type="text" 
                   class="w-full rounded-lg border border-blue-200 bg-blue-100 px-4 py-2 text-sm font-medium text-blue-800 dark:border-blue-700 dark:bg-blue-900 dark:text-blue-300 cursor-not-allowed" 
                   value="{{ $pdf->year ?? date('Y') }}" 
                   readonly>
          </div>
          <div>
            <label class="block text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">Month</label>
            <input type="text" 
                   class="w-full rounded-lg border border-blue-200 bg-blue-100 px-4 py-2 text-sm font-medium text-blue-800 dark:border-blue-700 dark:bg-blue-900 dark:text-blue-300 cursor-not-allowed" 
                   value="{{ $pdf->month ? \Carbon\Carbon::create()->month($pdf->month)->format('F') : date('F') }}" 
                   readonly>
          </div>
        </div>
        <input type="hidden" id="county" name="county" value="{{ $pdf->county->name ?? $pdf->county_code ?? 'Nairobi' }}">
        <input type="hidden" id="year" name="year" value="{{ $pdf->year ?? date('Y') }}">
        <input type="hidden" id="month" name="month" value="{{ $pdf->month ?? date('n') }}">
      </div>

      <!-- Debug Alert Placeholder -->
      <div id="debugAlert" class="hidden">
        <div class="rounded-xl border border-blue-500 bg-blue-50 p-4 dark:border-blue-500/30 dark:bg-blue-500/15 mb-4">
          <div class="flex items-start gap-3">
            <div class="-mt-0.5 text-blue-500">
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="flex-1">
              <h4 class="mb-1 text-sm font-semibold text-gray-800 dark:text-white/90">Form Data Preview</h4>
              <pre id="debugData" class="text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 p-2 rounded overflow-auto max-h-40"></pre>
            </div>
          </div>
        </div>
      </div>

      <form method="POST" action="{{ route('marriages.store-from-pdf', $pdf->id) }}" class="space-y-6" id="marriageForm">
        @csrf
        <input type="hidden" id="marriage_id" name="marriage_id" value="">
        <input type="hidden" id="pdf_id" name="pdf_id" value="{{ $pdf->id }}">
        <input type="hidden" name="image_id" value="">
        <input type="hidden" id="is_edit_mode" name="is_edit_mode" value="0">
        <input type="hidden" name="system_status" value="Pending">
        <input type="hidden" name="verification_status" value="Unverified">
        <input type="hidden" name="created_by" value="{{ Auth::id() }}">
        <input type="hidden" name="verified_by" value="{{ Auth::id() }}">
        <input type="hidden" name="year" id="year_hidden" value="{{ $pdf->year ?? date('Y') }}">
        <input type="hidden" name="month" id="month_hidden" value="{{ $pdf->month ?? date('n') }}">

        <!-- Basic Information Section -->
        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50">
          <div class="section-header cursor-pointer p-4 border-b border-gray-200 dark:border-gray-700" data-section="basic">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Basic Information</h4>
                <span class="completion-badge" data-section="basic">0%</span>
              </div>
              <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
          
          <div class="section-content p-4 space-y-4">
            <!-- Certificate Serial -->
            <div>
              <label for="certificate_serial" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Certificate Serial Number <span class="text-red-500">*</span>
              </label>
              <input type="text" 
                     class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field @error('certificate_serial') border-red-300 dark:border-red-500 @enderror" 
                     id="certificate_serial" 
                     name="certificate_serial" 
                     value="{{ old('certificate_serial', $pdf->certificate_serial ?? '') }}"
                     placeholder="Enter certificate serial number"
                     required>
              @error('certificate_serial')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
              @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <!-- Marriage Type -->
              <div>
                <label for="marriage_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Marriage Type <span class="text-red-500">*</span>
                </label>
                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('marriage_type_id') border-red-300 dark:border-red-500 @enderror" 
                        id="marriage_type_id" 
                        name="marriage_type_id" 
                        required>
                  <option value="">Select Marriage Type</option>
                  @foreach($categories->where('type', 'marriage_type') as $category)
                    <option value="{{ $category->id }}" 
                            data-name="{{ strtolower($category->name) }}"
                            {{ old('marriage_type_id') == $category->id ? 'selected' : '' }}>
                      {{ $category->name }}
                    </option>
                  @endforeach
                </select>
                @error('marriage_type_id')
                  <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>

              <!-- Marriage Date -->
              <div>
                <label for="marriage_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Marriage Date <span class="text-red-500">*</span>
                </label>
                <input type="date" 
                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('marriage_date') border-red-300 dark:border-red-500 @enderror" 
                       id="marriage_date" 
                       name="marriage_date" 
                       value="{{ old('marriage_date') }}"
                       required>
                @error('marriage_date')
                  <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <!-- Registration Date -->
              <div>
                <label for="reg_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Registration Date
                </label>
                <input type="date" 
                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('reg_date') border-red-300 dark:border-red-500 @enderror" 
                       id="reg_date" 
                       name="reg_date" 
                       value="{{ old('reg_date') }}"
                       placeholder="Optional registration date">
                @error('reg_date')
                  <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>

              <!-- Venue -->
              <div>
                <label for="venue" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Venue <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field @error('venue') border-red-300 dark:border-red-500 @enderror" 
                       id="venue" 
                       name="venue" 
                       value="{{ old('venue') }}"
                       placeholder="Enter marriage venue"
                       required>
                @error('venue')
                  <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <!-- Marriage Status -->
              <div>
                <label for="marriage_status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Marriage Status
                </label>
                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                        id="marriage_status_id" 
                        name="marriage_status_id">
                  <option value="">Select Marriage Status</option>
                  @foreach($categories->where('type', 'marriage_status') as $category)
                    <option value="{{ $category->id }}" {{ old('marriage_status_id') == $category->id ? 'selected' : '' }}>
                      {{ $category->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <!-- Verification Status -->
              <div>
                <label for="verification_status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Verification Status
                </label>
                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                        id="verification_status_id" 
                        name="verification_status_id">
                  <option value="">Select Verification Status</option>
                  @foreach($categories->where('type', 'verification_status') as $category)
                    <option value="{{ $category->id }}" {{ old('verification_status_id') == $category->id ? 'selected' : '' }}>
                      {{ $category->name }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Spouses Information Section -->
        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50">
          <div class="section-header cursor-pointer p-4 border-b border-gray-200 dark:border-gray-700" data-section="spouses">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Spouses Information</h4>
                <span class="completion-badge" data-section="spouses">0%</span>
              </div>
              <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
          
          <div class="section-content p-4 space-y-6 hidden">
            <!-- Husband Section -->
            <div class="rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="subsection-header cursor-pointer p-3 border-b border-gray-200 dark:border-gray-700" data-subsection="husband">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Husband Details</h5>
                    <span class="person-completion-badge" data-person="husband">0%</span>
                  </div>
                  <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
              
              <div class="subsection-content p-4 space-y-4 hidden">
                <!-- Husband Basic Info -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="husband_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                          id="husband_name" 
                          name="husband_name" 
                          value="{{ old('husband_name') }}"
                          placeholder="Husband's full name"
                          required>
                  </div>

                  <div>
                    <label for="husband_age" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Age
                    </label>
                    <input type="number" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="husband_age" 
                          name="husband_age" 
                          value="{{ old('husband_age') }}"
                          placeholder="Age"
                          min="18"
                          max="120">
                  </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="husband_occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Occupation
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                          id="husband_occupation" 
                          name="husband_occupation" 
                          value="{{ old('husband_occupation') }}"
                          placeholder="Occupation">
                  </div>
                
                </div>

                <!-- Hidden ID Type and Number Fields -->
                <div class="hidden">
                  <input type="hidden" id="husband_id_type" name="husband_id_type" value="">
                  <input type="hidden" id="husband_id_number" name="husband_id_number" value="">
                  <input type="hidden" id="husband_spouse_type" name="husband_spouse_type" value="husband">
                  <input type="hidden" name="husband_created_by" value="{{ Auth::id() }}">
                  <input type="hidden" name="husband_updated_by" value="{{ Auth::id() }}">
                  <input type="hidden" name="husband_verified_by" value="{{ Auth::id() }}">
                </div>
                

                <!-- Husband Residence Field -->
                  <div>
                    <label for="husband_residence" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Residence/Ward
                    </label>
                    <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select" 
                            id="husband_residence" 
                            name="husband_residence">
                      <option value="">Select or type ward name</option>
                      @foreach($wards ?? [] as $ward)
                        <option value="{{ $ward }}" {{ old('husband_residence') == $ward ? 'selected' : '' }}>{{ $ward }}</option>
                      @endforeach
                    </select>
                  </div>

                <!-- Husband Parents Information -->
                <div class="border-t pt-4 mt-4">
                  <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Parents Information</h6>
                  
                  <!-- Father's Information -->
                  <div class="mb-4">
                    <div class="parent-subsection-header cursor-pointer p-2 bg-gray-50 rounded-lg dark:bg-gray-700/50 mb-2" data-parent="husband_father">
                      <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Father's Information</span>
                        <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                      </div>
                    </div>
                    
                    <div class="parent-subsection-content p-3 space-y-3 bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800/50 hidden">
                      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                          <label for="husband_father_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Father's Name
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                id="husband_father_name" 
                                name="husband_father_name" 
                                value="{{ old('husband_father_name') }}"
                                placeholder="Father's full name">
                        </div>
                        <div>
                          <label for="husband_father_occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Father's Occupation
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                id="husband_father_occupation" 
                                name="husband_father_occupation" 
                                value="{{ old('husband_father_occupation') }}"
                                placeholder="Father's occupation">
                        </div>
                      </div>
                      <div>
                        <label for="husband_father_residence" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                          Father's Residence
                        </label>
                        <select class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select" 
                                id="husband_father_residence" 
                                name="husband_father_residence">
                          <option value="">Select or type ward name</option>
                          @foreach($wards ?? [] as $ward)
                            <option value="{{ $ward }}" {{ old('husband_father_residence') == $ward ? 'selected' : '' }}>{{ $ward }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>

                  <!-- Mother's Information -->
                  <div>
                    <div class="parent-subsection-header cursor-pointer p-2 bg-gray-50 rounded-lg dark:bg-gray-700/50 mb-2" data-parent="husband_mother">
                      <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Mother's Information</span>
                        <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                      </div>
                    </div>
                    
                    <div class="parent-subsection-content p-3 space-y-3 bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800/50 hidden">
                      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                          <label for="husband_mother_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mother's Name
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                id="husband_mother_name" 
                                name="husband_mother_name" 
                                value="{{ old('husband_mother_name') }}"
                                placeholder="Mother's full name">
                        </div>
                        <div>
                          <label for="husband_mother_occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mother's Occupation
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                id="husband_mother_occupation" 
                                name="husband_mother_occupation" 
                                value="{{ old('husband_mother_occupation') }}"
                                placeholder="Mother's occupation">
                        </div>
                      </div>
                      <div>
                        <label for="husband_mother_residence" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                          Mother's Residence
                        </label>
                        <select class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select" 
                                id="husband_mother_residence" 
                                name="husband_mother_residence">
                          <option value="">Select or type ward name</option>
                          @foreach($wards ?? [] as $ward)
                            <option value="{{ $ward }}" {{ old('husband_mother_residence') == $ward ? 'selected' : '' }}>{{ $ward }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Wife Section -->
            <div class="rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="subsection-header cursor-pointer p-3 border-b border-gray-200 dark:border-gray-700" data-subsection="wife">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Wife Details</h5>
                    <span class="person-completion-badge" data-person="wife">0%</span>
                  </div>
                  <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
              
              <div class="subsection-content p-4 space-y-4 hidden">
                <!-- Wife Basic Info -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="wife_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                          id="wife_name" 
                          name="wife_name" 
                          value="{{ old('wife_name') }}"
                          placeholder="Wife's full name"
                          required>
                  </div>

                  <div>
                    <label for="wife_age" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Age
                    </label>
                    <input type="number" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="wife_age" 
                          name="wife_age" 
                          value="{{ old('wife_age') }}"
                          placeholder="Age"
                          min="18"
                          max="120">
                  </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="wife_occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Occupation
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                          id="wife_occupation" 
                          name="wife_occupation" 
                          value="{{ old('wife_occupation') }}"
                          placeholder="Occupation">
                  </div>

                  
                </div>

                  <!-- Wife Residence Field -->
                  <div>
                    <label for="wife_residence" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Residence/Ward
                    </label>
                    <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select" 
                            id="wife_residence" 
                            name="wife_residence">
                      <option value="">Select or type ward name</option>
                      @foreach($wards ?? [] as $ward)
                        <option value="{{ $ward }}" {{ old('wife_residence') == $ward ? 'selected' : '' }}>{{ $ward }}</option>
                      @endforeach
                    </select>
                  </div>

                <!-- Hidden ID Type and Number Fields -->
                <div class="hidden">
                  <input type="hidden" id="wife_id_type" name="wife_id_type" value="">
                  <input type="hidden" id="wife_id_number" name="wife_id_number" value="">
                  <input type="hidden" id="wife_spouse_type" name="wife_spouse_type" value="wife">
                  <input type="hidden" name="wife_created_by" value="{{ Auth::id() }}">
                  <input type="hidden" name="wife_updated_by" value="{{ Auth::id() }}">
                  <input type="hidden" name="wife_verified_by" value="{{ Auth::id() }}">
                </div>

                <!-- Wife Parents Information -->
                <div class="border-t pt-4 mt-4">
                  <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Parents Information</h6>
                  
                  <!-- Father's Information -->
                  <div class="mb-4">
                    <div class="parent-subsection-header cursor-pointer p-2 bg-gray-50 rounded-lg dark:bg-gray-700/50 mb-2" data-parent="wife_father">
                      <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Father's Information</span>
                        <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                      </div>
                    </div>
                    
                    <div class="parent-subsection-content p-3 space-y-3 bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800/50 hidden">
                      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                          <label for="wife_father_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Father's Name
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                id="wife_father_name" 
                                name="wife_father_name" 
                                value="{{ old('wife_father_name') }}"
                                placeholder="Father's full name">
                        </div>
                        <div>
                          <label for="wife_father_occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Father's Occupation
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                id="wife_father_occupation" 
                                name="wife_father_occupation" 
                                value="{{ old('wife_father_occupation') }}"
                                placeholder="Father's occupation">
                        </div>
                      </div>
                      <div>
                        <label for="wife_father_residence" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                          Father's Residence
                        </label>
                        <select class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select" 
                                id="wife_father_residence" 
                                name="wife_father_residence">
                          <option value="">Select or type ward name</option>
                          @foreach($wards ?? [] as $ward)
                            <option value="{{ $ward }}" {{ old('wife_father_residence') == $ward ? 'selected' : '' }}>{{ $ward }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>

                  <!-- Mother's Information -->
                  <div>
                    <div class="parent-subsection-header cursor-pointer p-2 bg-gray-50 rounded-lg dark:bg-gray-700/50 mb-2" data-parent="wife_mother">
                      <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Mother's Information</span>
                        <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                      </div>
                    </div>
                    
                    <div class="parent-subsection-content p-3 space-y-3 bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800/50 hidden">
                      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                          <label for="wife_mother_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mother's Name
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                id="wife_mother_name" 
                                name="wife_mother_name" 
                                value="{{ old('wife_mother_name') }}"
                                placeholder="Mother's full name">
                        </div>
                        <div>
                          <label for="wife_mother_occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mother's Occupation
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                id="wife_mother_occupation" 
                                name="wife_mother_occupation" 
                                value="{{ old('wife_mother_occupation') }}"
                                placeholder="Mother's occupation">
                        </div>
                      </div>
                      <div>
                        <label for="wife_mother_residence" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                          Mother's Residence
                        </label>
                        <select class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 residence-select" 
                                id="wife_mother_residence" 
                                name="wife_mother_residence">
                          <option value="">Select or type ward name</option>
                          @foreach($wards ?? [] as $ward)
                            <option value="{{ $ward }}" {{ old('wife_mother_residence') == $ward ? 'selected' : '' }}>{{ $ward }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Witnesses Information Section -->
        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50">
            <div class="section-header cursor-pointer p-4 border-b border-gray-200 dark:border-gray-700" data-section="witnesses">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Witnesses Information</h4>
                        <span class="completion-badge" data-section="witnesses">0%</span>
                    </div>
                    <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
            
            <div class="section-content p-4 space-y-6 hidden">
                <!-- Witness 1 Section -->
                <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="subsection-header cursor-pointer p-3 border-b border-gray-200 dark:border-gray-700" data-subsection="witness1">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Witness 1 Details</h5>
                                <span class="person-completion-badge" data-person="witness1">0%</span>
                            </div>
                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    
                    <div class="subsection-content p-4 space-y-4 hidden">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="witness1_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                      id="witness1_name" 
                                      name="witness1_name" 
                                      value="{{ old('witness1_name') }}"
                                      placeholder="Witness 1 full name"
                                      required>
                            </div>

                            <div>
                                <label for="witness1_side" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Witness Side
                                </label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                                        id="witness1_side" 
                                        name="witness1_side">
                                    <option value="">Select Side</option>
                                    <option value="husband" {{ old('witness1_side') == 'husband' ? 'selected' : '' }}>Husband's Side</option>
                                    <option value="wife" {{ old('witness1_side') == 'wife' ? 'selected' : '' }}>Wife's Side</option>
                                    <option value="both" {{ old('witness1_side') == 'both' ? 'selected' : '' }}>Both Sides</option>
                                </select>
                            </div>
                        </div>

                        <!-- Hidden ID Type and Number Fields -->
                        <div class="hidden">
                            <input type="hidden" name="witness1_created_by" value="{{ Auth::id() }}">
                            <input type="hidden" name="witness1_updated_by" value="{{ Auth::id() }}">
                            <input type="hidden" name="witness1_verified_by" value="{{ Auth::id() }}">
                        </div>

                        
                    </div>
                </div>

                <!-- Witness 2 Section -->
                <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="subsection-header cursor-pointer p-3 border-b border-gray-200 dark:border-gray-700" data-subsection="witness2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Witness 2 Details</h5>
                                <span class="person-completion-badge" data-person="witness2">0%</span>
                            </div>
                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    
                    <div class="subsection-content p-4 space-y-4 hidden">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="witness2_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field" 
                                      id="witness2_name" 
                                      name="witness2_name" 
                                      value="{{ old('witness2_name') }}"
                                      placeholder="Witness 2 full name"
                                      required>
                            </div>

                            <div>
                                <label for="witness2_side" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Witness Side
                                </label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                                        id="witness2_side" 
                                        name="witness2_side">
                                    <option value="">Select Side</option>
                                    <option value="husband" {{ old('witness2_side') == 'husband' ? 'selected' : '' }}>Husband's Side</option>
                                    <option value="wife" {{ old('witness2_side') == 'wife' ? 'selected' : '' }}>Wife's Side</option>
                                    <option value="both" {{ old('witness2_side') == 'both' ? 'selected' : '' }}>Both Sides</option>
                                </select>
                            </div>
                        </div>

                        <!-- Hidden ID Type and Number Fields -->
                        <div class="hidden">
                            <input type="hidden" name="witness2_created_by" value="{{ Auth::id() }}">
                            <input type="hidden" name="witness2_updated_by" value="{{ Auth::id() }}">
                            <input type="hidden" name="witness2_verified_by" value="{{ Auth::id() }}">
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50">
          <div class="section-header cursor-pointer p-4 border-b border-gray-200 dark:border-gray-700" data-section="location">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Location Information</h4>
                <span class="completion-badge" data-section="location">0%</span>
              </div>
              <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
          
          <div class="section-content p-4 space-y-4 hidden">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <!-- County Selection -->
              <div>
                <label for="county_display" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  County <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 cursor-not-allowed" 
                       id="county_display"
                       value="{{ $pdf->county->name ?? $pdf->county_code ?? 'Nairobi' }}" 
                       readonly>
                <input type="hidden" id="county" name="county" value="{{ $pdf->county->name ?? $pdf->county_code ?? 'Nairobi' }}">
              </div>

              <!-- Constituency Selection -->
              <div>
                <label for="sub_county" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Constituency
                </label>
                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('sub_county') border-red-300 dark:border-red-500 @enderror constituency-select" 
                        id="sub_county" 
                        name="sub_county">
                  <option value="">Select Constituency</option>
                  @foreach($constituencies ?? [] as $constituency)
                    <option value="{{ $constituency }}" {{ old('sub_county') == $constituency ? 'selected' : '' }}>{{ $constituency }}</option>
                  @endforeach
                </select>
                @error('sub_county')
                  <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <!-- Marriage Type Extensions Section (Dynamic) -->
        <div id="marriageExtensionsSection" class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50 hidden">
          <div class="section-header cursor-pointer p-4 border-b border-gray-200 dark:border-gray-700" data-section="extensions">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Additional Information</h4>
                <span class="completion-badge" data-section="extensions">0%</span>
              </div>
              <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
          
          <div class="section-content p-4 space-y-4 hidden">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4" id="extensionDescription">
              <!-- Description will be populated by JavaScript -->
            </p>
            
            <div id="extensionFields" class="space-y-4">
              <!-- Fields will be populated by JavaScript based on marriage type -->
            </div>
          </div>
        </div>

        <!-- Debug Button -->
        <button type="button" onclick="debugForm()" class="w-full inline-flex justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z" />
          </svg>
          Debug Form Data
        </button>

        <!-- Save Progress Button -->
        <button type="button" onclick="saveProgress()" class="w-full inline-flex justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
          </svg>
          Save Progress
        </button>

        <!-- Form Actions -->
        <div class="flex flex-row gap-4 pt-4">
          <!-- Submit Button -->
          <button type="submit" class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]" id="submitButton">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Marriage Record
          </button>

          <!-- Cancel Link -->
          <a href="{{ route('marriages.index') }}" class="w-full inline-flex justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m-7 7h18"/>
            </svg>
            Back
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const existingMarriageData = @json($existingMarriageData);
const pdfData = @json($pdfData);

document.addEventListener('DOMContentLoaded', function() {
  // State variables
  let isEditMode = false;
  let existingMarriageId = null;
  let currentMarriageData = null;

  // ==================== INITIALIZATION FUNCTIONS ====================

  // Initialize uppercase fields
  function initUppercaseFields() {
    const uppercaseFields = document.querySelectorAll('.uppercase-field');
    uppercaseFields.forEach(field => {
      field.addEventListener('input', function(e) {
        const start = this.selectionStart;
        const end = this.selectionEnd;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(start, end);
      });
      
      field.addEventListener('paste', function(e) {
        e.preventDefault();
        const text = e.clipboardData.getData('text').toUpperCase();
        document.execCommand('insertText', false, text);
      });
    });
  }

  // Initialize Select2
  function initSelect2() {
    // Initialize Select2 for constituency
    const constituencySelect = document.getElementById('sub_county');
    if (constituencySelect) {
      $(constituencySelect).select2({
        placeholder: "Select Constituency",
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5'
      });
    }

    // Initialize Select2 for residence fields
    $('.residence-select').select2({
      placeholder: "Select or type ward name",
      allowClear: true,
      width: '100%',
      theme: 'bootstrap-5',
      tags: true,
      createTag: function (params) {
        const term = $.trim(params.term);
        if (term === '') return null;
        return {
          id: term,
          text: term,
          newTag: true
        };
      }
    });
  }

  // Initialize collapsible sections - FIXED VERSION
  function initCollapsibleSections() {
    // Main sections
    document.querySelectorAll('.section-header').forEach(header => {
      header.addEventListener('click', function() {
        const content = this.nextElementSibling;
        const icon = this.querySelector('svg');
        
        if (content && content.classList.contains('section-content')) {
          content.classList.toggle('hidden');
          icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
      });
    });

    // Subsections (husband, wife, witnesses)
    document.querySelectorAll('.subsection-header').forEach(header => {
      header.addEventListener('click', function() {
        const content = this.nextElementSibling;
        const icon = this.querySelector('svg');
        
        if (content && content.classList.contains('subsection-content')) {
          content.classList.toggle('hidden');
          icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
      });
    });

    // Parent subsections (father/mother info)
    document.querySelectorAll('.parent-subsection-header').forEach(header => {
      header.addEventListener('click', function() {
        const content = this.nextElementSibling;
        const icon = this.querySelector('svg');
        
        if (content && content.classList.contains('parent-subsection-content')) {
          content.classList.toggle('hidden');
          icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
      });
    });

    // Open first main section by default
    const firstSectionHeader = document.querySelector('.section-header');
    if (firstSectionHeader) {
      const firstContent = firstSectionHeader.nextElementSibling;
      const firstIcon = firstSectionHeader.querySelector('svg');
      if (firstContent && firstContent.classList.contains('section-content') && firstContent.classList.contains('hidden')) {
        firstContent.classList.remove('hidden');
        if (firstIcon) {
          firstIcon.style.transform = 'rotate(180deg)';
        }
      }
    }
  }

  // ==================== FORM COMPLETION TRACKING ====================

  function initCompletionTracking() {
    const form = document.getElementById('marriageForm');
    
    // Field configurations for completion tracking
    const fieldConfigs = {
      'basic': ['certificate_serial', 'marriage_type_id', 'marriage_date', 'venue'],
      'spouses': ['husband_name', 'wife_name'],
      'witnesses': ['witness1_name', 'witness2_name'],
      'location': ['county'],
      'extensions': []
    };

    // Person field configurations
    const personFieldConfigs = {
      'husband': ['husband_name', 'husband_age', 'husband_occupation', 'husband_residence', 'husband_address'],
      'wife': ['wife_name', 'wife_age', 'wife_occupation', 'wife_residence', 'wife_address'],
      'witness1': ['witness1_name'],
      'witness2': ['witness2_name']
    };

    function calculateCompletion() {
      let totalFields = 0;
      let completedFields = 0;
      const missingRequiredFields = [];

      // Calculate section completions
      Object.keys(fieldConfigs).forEach(section => {
        const fields = fieldConfigs[section];
        let sectionCompleted = 0;
        
        fields.forEach(fieldName => {
          const field = form.querySelector(`[name="${fieldName}"]`);
          if (field) {
            totalFields++;
            const fieldValue = field.value ? field.value.trim() : '';
            
            if (field.hasAttribute('required') && fieldValue === '') {
              missingRequiredFields.push(fieldName);
            }
            
            if (fieldValue !== '') {
              completedFields++;
              sectionCompleted++;
            }
          }
        });

        // Update section badge
        const badge = document.querySelector(`.completion-badge[data-section="${section}"]`);
        if (badge) {
          const sectionPercentage = fields.length > 0 ? Math.round((sectionCompleted / fields.length) * 100) : 0;
          badge.textContent = `${sectionPercentage}%`;
          badge.className = `completion-badge ${getBadgeColor(sectionPercentage)}`;
        }
      });

      // Calculate person completions
      Object.keys(personFieldConfigs).forEach(person => {
        const fields = personFieldConfigs[person];
        let personCompleted = 0;
        
        fields.forEach(fieldName => {
          const field = form.querySelector(`[name="${fieldName}"]`);
          if (field && field.value && field.value.trim() !== '') {
            personCompleted++;
          }
        });

        const personPercentage = fields.length > 0 ? Math.round((personCompleted / fields.length) * 100) : 0;
        
        // Update person badge
        const badge = document.querySelector(`.person-completion-badge[data-person="${person}"]`);
        if (badge) {
          badge.textContent = `${personPercentage}%`;
          badge.className = `person-completion-badge ${getBadgeColor(personPercentage)}`;
        }
      });

      // Calculate overall percentage
      const overallPercentage = totalFields > 0 ? Math.round((completedFields / totalFields) * 100) : 0;
      
      // Update progress bar
      const completionBar = document.getElementById('completionBar');
      const completionPercentage = document.getElementById('completionPercentage');
      if (completionBar && completionPercentage) {
        completionBar.style.width = `${overallPercentage}%`;
        completionPercentage.textContent = `${overallPercentage}%`;
        updateCompletionBarColor(overallPercentage);
      }
      
      // Update alert
      updateCompletionAlert(overallPercentage, missingRequiredFields);

      return {
        percentage: overallPercentage,
        missingFields: missingRequiredFields
      };
    }

    function getBadgeColor(percentage) {
      if (percentage >= 80) return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
      if (percentage >= 50) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
      return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
    }

    // Listen to form changes
    form.addEventListener('input', calculateCompletion);
    form.addEventListener('change', calculateCompletion);

    // Initial calculation
    setTimeout(calculateCompletion, 100);
    
    return { calculateCompletion };
  }

  function updateCompletionBarColor(percentage) {
    const completionBar = document.getElementById('completionBar');
    if (!completionBar) return;
    
    if (percentage < 30) {
      completionBar.style.backgroundColor = '#ef4444';
    } else if (percentage < 70) {
      completionBar.style.backgroundColor = '#f59e0b';
    } else {
      completionBar.style.backgroundColor = '#10b981';
    }
  }

  function updateCompletionAlert(percentage, missingFields) {
    const completionAlert = document.getElementById('completionAlert');
    const alertMessage = document.getElementById('alertMessage');
    
    if (!completionAlert || !alertMessage) return;
    
    if (percentage < 100 && percentage > 0) {
      if (missingFields.length > 0) {
        const fieldLabels = {
          'certificate_serial': 'Certificate Serial Number',
          'marriage_type_id': 'Marriage Type',
          'marriage_date': 'Marriage Date',
          'venue': 'Venue',
          'county': 'County',
          'husband_name': 'Husband\'s Full Name',
          'wife_name': 'Wife\'s Full Name',
          'witness1_name': 'Witness 1 Full Name',
          'witness2_name': 'Witness 2 Full Name'
        };
        
        const fieldNames = missingFields.map(field => 
          fieldLabels[field] || field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
        );
        
        alertMessage.textContent = `${missingFields.length} required field(s) missing: ${fieldNames.join(', ')}`;
      } else {
        alertMessage.textContent = `Form is ${percentage}% complete. You can save your progress.`;
      }
      completionAlert.classList.remove('hidden');
    } else if (percentage === 100) {
      alertMessage.textContent = 'All required fields are completed! Ready to submit.';
      completionAlert.classList.remove('hidden');
      completionAlert.querySelector('div').className = 'rounded-lg border border-green-200 bg-green-50 p-3 dark:border-green-800 dark:bg-green-900/20';
      completionAlert.querySelector('svg').className = 'h-4 w-4 text-green-600 dark:text-green-400';
    } else {
      completionAlert.classList.add('hidden');
    }
  }

  // ==================== MARRIAGE TYPE EXTENSIONS ====================

  const marriageTypeConfigs = {
    'christian': {
      description: 'Christian marriage requires church and pastor information',
      fields: [
        { name: 'church_org', label: 'Church/Organization', type: 'text', required: true, placeholder: 'Enter church or religious organization name' },
        { name: 'pastor_name', label: 'Pastor/Officiant Name', type: 'text', required: true, placeholder: 'Enter pastor or marriage officiant name' },
        { name: 'entry_no', label: 'Church Entry Number', type: 'text', required: false, placeholder: 'Church registry entry number (optional)' }
      ]
    },
    'muslim': {
      description: 'Islamic marriage requires Mahr information and Muslim officer details',
      fields: [
        { name: 'mahr_agreed', label: 'Mahr Agreed Amount', type: 'text', required: true, placeholder: 'Enter agreed Mahr amount' },
        { name: 'mahr_paid', label: 'Mahr Paid Amount', type: 'text', required: false, placeholder: 'Enter paid Mahr amount (optional)' },
        { name: 'mahr_deferred', label: 'Mahr Deferred Amount', type: 'text', required: false, placeholder: 'Enter deferred Mahr amount (optional)' },
        { name: 'muslim_officer', label: 'Muslim Marriage Officer', type: 'text', required: true, placeholder: 'Enter Muslim marriage officer name' },
        { name: 'gifts', label: 'Additional Gifts', type: 'text', required: false, placeholder: 'Enter any additional gifts (optional)' }
      ]
    },
    'hindu': {
      description: 'Hindu marriage requires temple and dowry information',
      fields: [
        { name: 'temple', label: 'Temple Name', type: 'text', required: true, placeholder: 'Enter temple name where marriage was conducted' },
        { name: 'dowry', label: 'Dowry Details', type: 'text', required: false, placeholder: 'Enter dowry details (optional)' },
        { name: 'gifts', label: 'Wedding Gifts', type: 'text', required: false, placeholder: 'Enter wedding gifts exchanged (optional)' }
      ]
    },
    'civil': {
      description: 'Civil marriage requires basic registration information',
      fields: [
        { name: 'entry_no', label: 'Registration Entry Number', type: 'text', required: false, placeholder: 'Civil registration entry number (optional)' }
      ]
    }
  };

  function updateExtensionFields() {
    const marriageTypeSelect = document.getElementById('marriage_type_id');
    const extensionsSection = document.getElementById('marriageExtensionsSection');
    const extensionDescription = document.getElementById('extensionDescription');
    const extensionFields = document.getElementById('extensionFields');
    
    if (!marriageTypeSelect || !extensionsSection || !extensionDescription || !extensionFields) return;
    
    const selectedOption = marriageTypeSelect.options[marriageTypeSelect.selectedIndex];
    const marriageTypeName = selectedOption.getAttribute('data-name')?.toLowerCase();
    
    // Clear previous fields
    extensionFields.innerHTML = '';
    
    if (marriageTypeName && marriageTypeConfigs[marriageTypeName]) {
      const config = marriageTypeConfigs[marriageTypeName];
      
      // Update description
      extensionDescription.textContent = config.description;
      
      // Create fields
      config.fields.forEach(field => {
        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'space-y-2';
        
        const label = document.createElement('label');
        label.className = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        label.htmlFor = field.name;
        label.textContent = field.label;
        
        if (field.required) {
          const requiredSpan = document.createElement('span');
          requiredSpan.className = 'text-red-500';
          requiredSpan.textContent = ' *';
          label.appendChild(requiredSpan);
        }
        
        const input = document.createElement('input');
        input.type = field.type;
        input.className = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 uppercase-field';
        input.id = field.name;
        input.name = field.name;
        input.placeholder = field.placeholder || '';
        
        if (field.required) {
          input.required = true;
        }
        
        fieldDiv.appendChild(label);
        fieldDiv.appendChild(input);
        extensionFields.appendChild(fieldDiv);
      });
      
      // Show the section
      extensionsSection.classList.remove('hidden');
    } else {
      // Hide the section if no specific configuration
      extensionsSection.classList.add('hidden');
    }
  }

  // ==================== EDIT MODE FUNCTIONS ====================

  function loadExistingMarriageData() {
    if (existingMarriageData) {
      existingMarriageId = existingMarriageData.id;
      currentMarriageData = existingMarriageData;
      
      updateUIForEditMode();
      showEditModeAlert();
      populateFormWithMarriageData(existingMarriageData);
      
      // Update PDF status badge
      const pdfStatusBadge = document.getElementById('pdfStatusBadge');
      if (pdfStatusBadge) {
        pdfStatusBadge.innerHTML = `
          <svg class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
          </svg>
          Linked to Marriage #${existingMarriageData.id}
        `;
      }
      
      showLinkedMarriageInfo(existingMarriageData);
    } else {
      const pdfStatusBadge = document.getElementById('pdfStatusBadge');
      if (pdfStatusBadge) {
        pdfStatusBadge.innerHTML = `
          <svg class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          No existing marriage found
        `;
      }
      
      // Load PDF data
      if (pdfData) {
        if (pdfData.certificate_serial) {
          document.getElementById('certificate_serial').value = pdfData.certificate_serial;
        }
        
        const yearField = document.getElementById('year');
        const monthField = document.getElementById('month');
        if (pdfData.year && yearField) yearField.value = pdfData.year;
        if (pdfData.month && monthField) monthField.value = pdfData.month;
        
        const countyField = document.getElementById('county');
        const countyDisplay = document.getElementById('county_display');
        if (countyField && pdfData.county) {
          countyField.value = pdfData.county;
          if (countyDisplay) countyDisplay.value = pdfData.county;
        }
        
        // Update completion tracking
        if (window.completionTracker && typeof window.completionTracker.calculateCompletion === 'function') {
          window.completionTracker.calculateCompletion();
        }
      }
    }
  }

  function populateFormWithMarriageData(marriageData) {
    const form = document.getElementById('marriageForm');
    if (!form) return;
    
    // Set hidden fields 
    document.getElementById('marriage_id').value = marriageData.id;
    document.getElementById('is_edit_mode').value = '1';
    
    // Basic fields mapping
    const fieldMap = {
      'certificate_serial': marriageData.certificate_serial,
      'marriage_date': marriageData.marriage_date,
      'reg_date': marriageData.reg_date,
      'venue': marriageData.venue,
      'county': marriageData.county,
      'sub_county': marriageData.sub_county,
      'marriage_type_id': marriageData.marriage_type_id,
      'marriage_status_id': marriageData.marriage_status_id,
      'verification_status_id': marriageData.verification_status_id,
      'year': marriageData.year,
      'month': marriageData.month,
    };

    // Populate basic form fields
    Object.keys(fieldMap).forEach(key => {
      const value = fieldMap[key];
      if (value !== undefined && value !== null) {
        const element = form.querySelector(`[name="${key}"]`);
        if (element) {
          if (element.tagName === 'SELECT') {
            element.value = value;
            if ($(element).hasClass('select2-hidden-accessible')) {
              $(element).val(value).trigger('change');
            }
          } else {
            element.value = value;
          }
        }
      }
    });

    // Populate spouse data
    if (marriageData.spouses && Array.isArray(marriageData.spouses)) {
      const husband = marriageData.spouses.find(s => s.gender === 'male');
      const wife = marriageData.spouses.find(s => s.gender === 'female');
      
      if (husband) {
        const husbandFields = {
          'husband_name': husband.name,
          'husband_age': husband.age,
          'husband_id_type': husband.id_type,
          'husband_id_number': husband.id_number,
          'husband_father_name': husband.father_name,
          'husband_father_occupation': husband.father_occupation,
          'husband_father_residence': husband.father_residence,
          'husband_mother_name': husband.mother_name,
          'husband_mother_occupation': husband.mother_occupation,
          'husband_mother_residence': husband.mother_residence,
          'husband_occupation': husband.occupation,
          'husband_residence': husband.residence,
          'husband_address': husband.address,
          'husband_spouse_type': 'husband'
        };
        
        Object.keys(husbandFields).forEach(key => {
          const value = husbandFields[key];
          if (value !== undefined && value !== null) {
            const element = form.querySelector(`[name="${key}"]`);
            if (element) element.value = value;
          }
        });
      }
      
      if (wife) {
        const wifeFields = {
          'wife_name': wife.name,
          'wife_age': wife.age,
          'wife_id_type': wife.id_type,
          'wife_id_number': wife.id_number,
          'wife_father_name': wife.father_name,
          'wife_father_occupation': wife.father_occupation,
          'wife_father_residence': wife.father_residence,
          'wife_mother_name': wife.mother_name,
          'wife_mother_occupation': wife.mother_occupation,
          'wife_mother_residence': wife.mother_residence,
          'wife_occupation': wife.occupation,
          'wife_residence': wife.residence,
          'wife_address': wife.address,
          'wife_spouse_type': 'wife'
        };
        
        Object.keys(wifeFields).forEach(key => {
          const value = wifeFields[key];
          if (value !== undefined && value !== null) {
            const element = form.querySelector(`[name="${key}"]`);
            if (element) element.value = value;
          }
        });
      }
    }

    // Update extension fields based on marriage type
    if (marriageData.marriage_type_id) {
      setTimeout(updateExtensionFields, 100);
    }
    
    // Update completion tracking
    setTimeout(() => {
      if (window.completionTracker && typeof window.completionTracker.calculateCompletion === 'function') {
        window.completionTracker.calculateCompletion();
      }
    }, 500);
  }

  function showLinkedMarriageInfo(marriageData) {
    const linkedMarriageInfo = document.getElementById('linkedMarriageInfo');
    const linkedMarriageId = document.getElementById('linkedMarriageId');
    const linkedCertificateSerial = document.getElementById('linkedCertificateSerial');
    const linkedStatus = document.getElementById('linkedStatus');
    const linkedCreatedAt = document.getElementById('linkedCreatedAt');
    
    if (!linkedMarriageInfo || !linkedMarriageId || !linkedCertificateSerial || !linkedStatus || !linkedCreatedAt) return;
    
    linkedMarriageInfo.classList.remove('hidden');
    linkedMarriageId.textContent = marriageData.id;
    linkedCertificateSerial.textContent = marriageData.certificate_serial;
    linkedStatus.textContent = marriageData.system_status || 'Unknown';
    
    if (marriageData.created_at) {
      const date = new Date(marriageData.created_at);
      linkedCreatedAt.textContent = date.toLocaleDateString();
    } else {
      linkedCreatedAt.textContent = 'Unknown';
    }
  }

  function updateUIForEditMode() {
    isEditMode = true;
    
    const editModeToggle = document.getElementById('editModeToggle');
    const pageTitle = document.getElementById('pageTitle');
    const pageDescription = document.getElementById('pageDescription');
    const submitButton = document.getElementById('submitButton');
    const marriageForm = document.getElementById('marriageForm');
    
    if (editModeToggle) {
      editModeToggle.classList.remove('hidden');
      editModeToggle.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Edit Mode (Active)
      `;
      editModeToggle.classList.remove('border-blue-300', 'bg-blue-50', 'text-blue-700');
      editModeToggle.classList.add('border-green-300', 'bg-green-50', 'text-green-700');
    }
    
    if (pageTitle) pageTitle.textContent = 'Edit Marriage Record from PDF';
    if (pageDescription) pageDescription.textContent = 'Edit existing marriage record linked to this PDF';
    
    if (submitButton) {
      submitButton.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
        </svg>
        Update Marriage Record
      `;
    }
    
    if (marriageForm && existingMarriageId) {
        marriageForm.action = "{{ route('marriages.update-from-pdf', ':id') }}".replace(':id', existingMarriageId);
    }
  }

  function showEditModeAlert() {
    const editModeAlert = document.getElementById('editModeAlert');
    if (editModeAlert) {
      editModeAlert.classList.remove('hidden');
      const existingMarriageIdSpan = document.getElementById('existingMarriageId');
      if (existingMarriageIdSpan) {
        existingMarriageIdSpan.textContent = existingMarriageId;
      }
    }
  }

  window.toggleEditMode = function(enable) {
    if (enable === undefined) {
      enable = !isEditMode;
    }
    
    if (enable && existingMarriageId) {
      // Enable edit mode
      updateUIForEditMode();
      showEditModeAlert();
    } else {
      // Disable edit mode (create new)
      isEditMode = false;
      
      // Reset form
      document.getElementById('marriage_id').value = '';
      document.getElementById('is_edit_mode').value = '0';
      
      const editModeToggle = document.getElementById('editModeToggle');
      const editModeAlert = document.getElementById('editModeAlert');
      const pageTitle = document.getElementById('pageTitle');
      const pageDescription = document.getElementById('pageDescription');
      const submitButton = document.getElementById('submitButton');
      const marriageForm = document.getElementById('marriageForm');
      
      if (editModeToggle) {
        editModeToggle.innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Edit Mode
        `;
        editModeToggle.classList.remove('border-green-300', 'bg-green-50', 'text-green-700');
        editModeToggle.classList.add('border-blue-300', 'bg-blue-50', 'text-blue-700');
      }
      
      if (editModeAlert) editModeAlert.classList.add('hidden');
      if (pageTitle) pageTitle.textContent = 'Create Marriage Record from PDF';
      if (pageDescription) pageDescription.textContent = 'Create a new marriage record using certificate PDF';
      
      if (submitButton) {
        submitButton.innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Create Marriage Record
        `;
      }
      
      if (marriageForm) {
        marriageForm.action = "{{ route('marriages.store-from-pdf', $pdf->id) }}";
      }
      
      clearFormExceptPDFData();
    }
  };

  function clearFormExceptPDFData() {
    const form = document.getElementById('marriageForm');
    if (!form) return;
    
    const fieldsToClear = [
      'certificate_serial', 'marriage_date', 'reg_date', 'venue', 'sub_county',
      'marriage_type_id', 'marriage_status_id', 'verification_status_id',
      'husband_name', 'husband_age', 'husband_occupation', 'husband_residence', 
      'husband_father_name', 'husband_father_occupation', 'husband_father_residence',
      'husband_mother_name', 'husband_mother_occupation', 'husband_mother_residence',
      'wife_name', 'wife_age', 'wife_occupation', 'wife_residence', 
      'wife_father_name', 'wife_father_occupation', 'wife_father_residence',
      'wife_mother_name', 'wife_mother_occupation', 'wife_mother_residence',
      'witness1_name', 'witness1_side',
      'witness2_name', 'witness2_side',
    ];
    
    fieldsToClear.forEach(fieldName => {
      const element = form.querySelector(`[name="${fieldName}"]`);
      if (element) {
        if (element.tagName === 'SELECT') {
          element.value = '';
          if ($(element).hasClass('select2-hidden-accessible')) {
            $(element).val('').trigger('change');
          }
        } else {
          element.value = '';
        }
      }
    });
    
    const extensionFields = document.getElementById('extensionFields');
    const extensionsSection = document.getElementById('marriageExtensionsSection');
    if (extensionFields) extensionFields.innerHTML = '';
    if (extensionsSection) extensionsSection.classList.add('hidden');
    
    if (window.completionTracker && typeof window.completionTracker.calculateCompletion === 'function') {
      window.completionTracker.calculateCompletion();
    }
  }

  // ==================== UTILITY FUNCTIONS ====================

  window.saveProgress = function() {
    const form = document.getElementById('marriageForm');
    if (!form) return;
    
    const data = {};
    const filledFields = [];
    
    // Get all form elements
    const allElements = form.querySelectorAll('input, select, textarea');
    
    allElements.forEach(element => {
      const name = element.name;
      let value;
      
      if (element.type === 'checkbox' || element.type === 'radio') {
        value = element.checked;
      } else if (element.tagName === 'SELECT') {
        value = element.options[element.selectedIndex]?.value || '';
      } else {
        value = element.value || '';
      }
      
      if (name && value && value.toString().trim() !== '') {
        data[name] = value;
        filledFields.push(name);
      }
    });
    
    // Save to localStorage
    localStorage.setItem('marriageFormProgress', JSON.stringify(data));
    
    // Show success alert
    const successAlert = document.getElementById('successAlert');
    const savedFields = document.getElementById('savedFields');
    if (successAlert && savedFields) {
      const fieldLabels = {
        'certificate_serial': 'Certificate Serial',
        'marriage_type_id': 'Marriage Type',
        'marriage_date': 'Marriage Date',
        'venue': 'Venue',
        'husband_name': 'Husband\'s Name',
        'wife_name': 'Wife\'s Name',
        'witness1_name': 'Witness 1 Name',
        'witness2_name': 'Witness 2 Name',
        'sub_county': 'Constituency'
      };
      
      const fieldList = filledFields.map(field => 
        fieldLabels[field] || field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
      ).join(', ');
      
      savedFields.textContent = `Saved fields: ${fieldList}`;
      successAlert.classList.remove('hidden');
      successAlert.scrollIntoView({ behavior: 'smooth' });
      
      setTimeout(() => {
        successAlert.classList.add('hidden');
      }, 5000);
    }
    
    console.log('Progress saved:', data);
  };

  function loadProgress() {
    const savedData = localStorage.getItem('marriageFormProgress');
    if (savedData) {
      const data = JSON.parse(savedData);
      const form = document.getElementById('marriageForm');
      if (!form) return;
      
      Object.keys(data).forEach(key => {
        const element = form.querySelector(`[name="${key}"]`);
        if (element) {
          if (element.tagName === 'SELECT') {
            element.value = data[key];
            if ($(element).hasClass('select2-hidden-accessible')) {
              $(element).val(data[key]).trigger('change');
            }
          } else {
            element.value = data[key];
          }
        }
      });
      
      console.log('Progress loaded from localStorage');
    }
  }

  window.debugForm = function() {
    const form = document.getElementById('marriageForm');
    const debugAlert = document.getElementById('debugAlert');
    const debugData = document.getElementById('debugData');
    
    if (!form || !debugAlert || !debugData) return;
    
    const data = {};
    
    // Get all form elements including hidden ones
    const allElements = form.querySelectorAll('input, select, textarea');
    
    allElements.forEach(element => {
      const name = element.name;
      let value;
      
      if (element.type === 'checkbox' || element.type === 'radio') {
        value = element.checked;
      } else if (element.tagName === 'SELECT') {
        value = element.options[element.selectedIndex]?.value || '';
      } else {
        value = element.value || '';
      }
      
      if (name) {
        data[name] = value;
      }
    });
    
    // Show debug data
    debugData.textContent = JSON.stringify(data, null, 2);
    debugAlert.classList.remove('hidden');
    debugAlert.scrollIntoView({ behavior: 'smooth' });
    
    console.log('Form Data:', data);
  };

  // ==================== INITIALIZATION ====================

  function init() {
    initUppercaseFields();
    initSelect2();
    initCollapsibleSections();
    window.completionTracker = initCompletionTracking();
    updateExtensionFields();
    loadProgress();
    
    // Event listeners
    const marriageTypeSelect = document.getElementById('marriage_type_id');
    if (marriageTypeSelect) {
      marriageTypeSelect.addEventListener('change', updateExtensionFields);
    }
    
    const editModeToggle = document.getElementById('editModeToggle');
    if (editModeToggle) {
      editModeToggle.addEventListener('click', function() {
        toggleEditMode();
      });
    }
    
    const marriageForm = document.getElementById('marriageForm');
    if (marriageForm) {
      marriageForm.addEventListener('submit', function(e) {
        // Calculate completion
        const completion = window.completionTracker.calculateCompletion();
        
        if (completion.percentage < 100) {
          const shouldSubmit = confirm(`Form is only ${completion.percentage}% complete. Are you sure you want to ${isEditMode ? 'update' : 'create'}?`);
          if (!shouldSubmit) {
            e.preventDefault();
            return;
          }
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = `
          <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700 dark:text-gray-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          ${isEditMode ? 'Updating Record...' : 'Creating Record...'}
        `;
        submitBtn.disabled = true;
        
        // Clear localStorage on successful submission
        localStorage.removeItem('marriageFormProgress');
        
        // Debug before submission
        console.log('Submitting form data...');
        debugForm();
      });
    }
    
    // Auto-focus on certificate serial
    const certificateSerial = document.getElementById('certificate_serial');
    if (certificateSerial) certificateSerial.focus();
    
    // Set default marriage date to today if not already set
    const marriageDateField = document.getElementById('marriage_date');
    if (marriageDateField && !marriageDateField.value) {
      marriageDateField.value = new Date().toISOString().split('T')[0];
    }
    
    // Load existing marriage data immediately
    setTimeout(loadExistingMarriageData, 100);
  }

  // Start the initialization
  init();
});
</script>

<style>
.completion-badge, .person-completion-badge {
  @apply inline-flex items-center px-2 py-1 rounded-full text-xs font-medium;
}

.rotate-180 {
  transform: rotate(180deg);
}

.section-header:hover, .subsection-header:hover, .parent-subsection-header:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.dark .section-header:hover, .dark .subsection-header:hover, .dark .parent-subsection-header:hover {
  background-color: rgba(255, 255, 255, 0.02);
}

.parent-subsection-content {
  transition: all 0.3s ease;
}

.parent-subsection-content.hidden {
  display: none;
}

/* Make PDF iframe scrollable */
#pdfPreview {
  object-fit: contain;
  min-height: 800px;
}

/* Ensure form sections are properly spaced */
.section-content:not(.hidden) {
  display: block !important;
}

/* Select2 customization */
.select2-container--bootstrap-5 .select2-selection {
  border: 1px solid #d1d5db !important;
  border-radius: 0.5rem !important;
  min-height: 44px !important;
}

.select2-container--bootstrap-5 .select2-selection--single {
  padding: 0.625rem 1rem !important;
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
  padding-left: 0 !important;
}

/* Dark mode support for Select2 */
.dark .select2-container--bootstrap-5 .select2-selection {
  background-color: #1f2937 !important;
  border-color: #374151 !important;
  color: #d1d5db !important;
}

.dark .select2-container--bootstrap-5 .select2-dropdown {
  background-color: #1f2937 !important;
  border-color: #374151 !important;
}

.dark .select2-container--bootstrap-5 .select2-search__field {
  background-color: #111827 !important;
  color: #d1d5db !important;
  border-color: #374151 !important;
}

.dark .select2-container--bootstrap-5 .select2-results__option {
  color: #d1d5db !important;
}

.dark .select2-container--bootstrap-5 .select2-results__option--highlighted {
  background-color: #374151 !important;
}
</style>

<!-- Include Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<!-- Include Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection