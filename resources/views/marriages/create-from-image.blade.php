@extends('layouts.app')

@section('content')
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-6 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
  <div class="flex flex-col gap-2 mb-6 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Create Marriage Record from Image</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Create a new marriage record using certificate image
      </p>
    </div>
    
    <a href="{{ route('marriages.index') }}" 
       class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      Back to Marriages
    </a>
  </div>

  <!-- Progress Tracker -->
  <div class="mb-6">
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Form Completion</span>
      <span id="completionPercentage" class="text-sm font-semibold text-primary-600 dark:text-primary-400">0%</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
      <div id="completionBar" class="bg-primary-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-8 xl:grid-cols-12">
    <!-- Image Preview - Takes 7/12 of the width (3/4) -->
    <div class="xl:col-span-7">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50 h-full">
            <div class="flex flex-col h-full">
                <!-- Image Header with Controls -->
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Certificate Image Viewer</h4>
                    <div class="flex items-center gap-2">
                        <!-- Zoom Controls -->
                        <div class="flex items-center gap-1 bg-white rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 p-1">
                            <button type="button" id="zoomOut" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            <span id="zoomLevel" class="text-xs font-medium text-gray-600 dark:text-gray-400 px-2">100%</span>
                            <button type="button" id="zoomIn" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                            <button type="button" id="resetZoom" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-600 text-xs text-gray-600 dark:text-gray-400">
                                Reset
                            </button>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Ready to Link
                        </span>
                    </div>
                </div>
                
                <!-- Enhanced Image Display with Zoom and Pan -->
                <div class="flex-1 flex items-center justify-center min-h-[600px] bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-900 p-4 overflow-hidden">
                    @if($image->image_path && Storage::disk('public')->exists($image->image_path))
                        <div id="imageContainer" class="relative w-full h-full overflow-hidden cursor-grab active:cursor-grabbing">
                            <img src="{{ Storage::url($image->image_path) }}" 
                                alt="Certificate Image" 
                                id="certificateImage"
                                class="max-w-none transition-transform duration-200 origin-center"
                                style="transform: scale(1) translate(0px, 0px);">
                        </div>
                    @else
                        <div class="text-center text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-lg font-medium">Image Not Found</p>
                            <p class="text-sm mt-2">The certificate image could not be loaded</p>
                        </div>
                    @endif
                </div>
                
                <!-- Image Controls Info -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                </svg>
                                <span>Scroll to zoom • Drag to pan</span>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500">
                            A4 Certificate (595×842px)
                        </div>
                    </div>
                </div>

                <!-- Image Information -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Image ID:</span>
                            <span class="text-gray-900 dark:text-white ml-2">{{ $image->id }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">File Size:</span>
                            <span class="text-gray-900 dark:text-white ml-2">{{ $image->file_size }} KB</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Uploaded By:</span>
                            <span class="text-gray-900 dark:text-white ml-2">{{ $image->uploader->name ?? 'Unknown' }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Upload Date:</span>
                            <span class="text-gray-900 dark:text-white ml-2">{{ $image->created_at->format('M d, Y') }}</span>
                        </div>
                        @if($image->certificate_serial)
                        <div class="col-span-2">
                            <span class="font-medium text-gray-500 dark:text-gray-400">Detected Serial:</span>
                            <span class="text-gray-900 dark:text-white font-mono ml-2">{{ $image->certificate_serial }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Marriage Form - Takes 5/12 of the width (1/4) -->
    <div class="xl:col-span-5 space-y-6">
      <form method="POST" action="{{ route('marriages.store-from-image', $image->id) }}" class="space-y-6" id="marriageForm">
        @csrf

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
                     class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('certificate_serial') border-red-300 dark:border-red-500 @enderror" 
                     id="certificate_serial" 
                     name="certificate_serial" 
                     value="{{ old('certificate_serial', $image->certificate_serial ?? '') }}"
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
                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('venue') border-red-300 dark:border-red-500 @enderror" 
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
          
          <div class="section-content p-4 space-y-6">
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
              
              <div class="subsection-content p-4 space-y-4">
                <!-- Husband Basic Info -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="husband_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="husband_name" 
                          name="husband_name" 
                          value="{{ old('husband_name') }}"
                          placeholder="Husband's full name"
                          required>
                  </div>

                  <div>
                    <label for="husband_occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Occupation
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="husband_occupation" 
                          name="husband_occupation" 
                          value="{{ old('husband_occupation') }}"
                          placeholder="Occupation">
                  </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="husband_id_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      ID Type
                    </label>
                    <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                            id="husband_id_type" 
                            name="husband_id_type">
                      <option value="">Select ID Type</option>
                      @foreach($categories->where('type', 'id_type') as $category)
                        <option value="{{ $category->id }}" {{ old('husband_id_type') == $category->id ? 'selected' : '' }}>
                          {{ $category->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div>
                    <label for="husband_id_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      ID Number
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="husband_id_number" 
                          name="husband_id_number" 
                          value="{{ old('husband_id_number') }}"
                          placeholder="ID number">
                  </div>
                </div>

                <!-- Husband Address -->
                <div>
                  <label for="husband_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Address
                  </label>
                  <textarea 
                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                      id="husband_address" 
                      name="husband_address" 
                      placeholder="Full address"
                      rows="2">{{ old('husband_address') }}</textarea>
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
                    
                    <div class="parent-subsection-content p-3 space-y-3 bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800/50">
                      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                          <label for="husband_father_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Father's Name
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
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
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
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
                        <textarea 
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                            id="husband_father_residence" 
                            name="husband_father_residence" 
                            placeholder="Father's residence address"
                            rows="2">{{ old('husband_father_residence') }}</textarea>
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
                    
                    <div class="parent-subsection-content p-3 space-y-3 bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800/50">
                      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                          <label for="husband_mother_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mother's Name
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
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
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
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
                        <textarea 
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                            id="husband_mother_residence" 
                            name="husband_mother_residence" 
                            placeholder="Mother's residence address"
                            rows="2">{{ old('husband_mother_residence') }}</textarea>
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
              
              <div class="subsection-content p-4 space-y-4">
                <!-- Wife Basic Info -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="wife_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="wife_name" 
                          name="wife_name" 
                          value="{{ old('wife_name') }}"
                          placeholder="Wife's full name"
                          required>
                  </div>

                  <div>
                    <label for="wife_occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Occupation
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="wife_occupation" 
                          name="wife_occupation" 
                          value="{{ old('wife_occupation') }}"
                          placeholder="Occupation">
                  </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="wife_id_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      ID Type
                    </label>
                    <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                            id="wife_id_type" 
                            name="wife_id_type">
                      <option value="">Select ID Type</option>
                      @foreach($categories->where('type', 'id_type') as $category)
                        <option value="{{ $category->id }}" {{ old('wife_id_type') == $category->id ? 'selected' : '' }}>
                          {{ $category->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div>
                    <label for="wife_id_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      ID Number
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="wife_id_number" 
                          name="wife_id_number" 
                          value="{{ old('wife_id_number') }}"
                          placeholder="ID number">
                  </div>
                </div>

                <!-- Wife Address -->
                <div>
                  <label for="wife_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Address
                  </label>
                  <textarea 
                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                      id="wife_address" 
                      name="wife_address" 
                      placeholder="Full address"
                      rows="2">{{ old('wife_address') }}</textarea>
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
                    
                    <div class="parent-subsection-content p-3 space-y-3 bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800/50">
                      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                          <label for="wife_father_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Father's Name
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
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
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
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
                        <textarea 
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                            id="wife_father_residence" 
                            name="wife_father_residence" 
                            placeholder="Father's residence address"
                            rows="2">{{ old('wife_father_residence') }}</textarea>
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
                    
                    <div class="parent-subsection-content p-3 space-y-3 bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800/50">
                      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                          <label for="wife_mother_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mother's Name
                          </label>
                          <input type="text" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
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
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
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
                        <textarea 
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                            id="wife_mother_residence" 
                            name="wife_mother_residence" 
                            placeholder="Mother's residence address"
                            rows="2">{{ old('wife_mother_residence') }}</textarea>
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
          
          <div class="section-content p-4 space-y-6">
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
              
              <div class="subsection-content p-4 space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="witness1_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="witness1_name" 
                          name="witness1_name" 
                          value="{{ old('witness1_name') }}"
                          placeholder="Witness 1 full name"
                          required>
                  </div>

                  <div>
                    <label for="witness1_occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Occupation
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="witness1_occupation" 
                          name="witness1_occupation" 
                          value="{{ old('witness1_occupation') }}"
                          placeholder="Occupation">
                  </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="witness1_id_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      ID Type
                    </label>
                    <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                            id="witness1_id_type" 
                            name="witness1_id_type">
                      <option value="">Select ID Type</option>
                      @foreach($categories->where('type', 'id_type') as $category)
                        <option value="{{ $category->id }}" {{ old('witness1_id_type') == $category->id ? 'selected' : '' }}>
                          {{ $category->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div>
                    <label for="witness1_id_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      ID Number
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="witness1_id_number" 
                          name="witness1_id_number" 
                          value="{{ old('witness1_id_number') }}"
                          placeholder="ID number">
                  </div>
                </div>

                <!-- Witness 1 Address -->
                <div>
                  <label for="witness1_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Address
                  </label>
                  <textarea 
                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                      id="witness1_address" 
                      name="witness1_address" 
                      placeholder="Full address"
                      rows="2">{{ old('witness1_address') }}</textarea>
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
              
              <div class="subsection-content p-4 space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="witness2_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="witness2_name" 
                          name="witness2_name" 
                          value="{{ old('witness2_name') }}"
                          placeholder="Witness 2 full name"
                          required>
                  </div>

                  <div>
                    <label for="witness2_occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Occupation
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="witness2_occupation" 
                          name="witness2_occupation" 
                          value="{{ old('witness2_occupation') }}"
                          placeholder="Occupation">
                  </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="witness2_id_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      ID Type
                    </label>
                    <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                            id="witness2_id_type" 
                            name="witness2_id_type">
                      <option value="">Select ID Type</option>
                      @foreach($categories->where('type', 'id_type') as $category)
                        <option value="{{ $category->id }}" {{ old('witness2_id_type') == $category->id ? 'selected' : '' }}>
                          {{ $category->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div>
                    <label for="witness2_id_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      ID Number
                    </label>
                    <input type="text" 
                          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                          id="witness2_id_number" 
                          name="witness2_id_number" 
                          value="{{ old('witness2_id_number') }}"
                          placeholder="ID number">
                  </div>
                </div>

                <!-- Witness 2 Address -->
                <div>
                  <label for="witness2_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Address
                  </label>
                  <textarea 
                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800" 
                      id="witness2_address" 
                      name="witness2_address" 
                      placeholder="Full address"
                      rows="2">{{ old('witness2_address') }}</textarea>
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
          
          <div class="section-content p-4 space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <!-- County Selection -->
              <div>
                <label for="county" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  County <span class="text-red-500">*</span>
                </label>
                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('county') border-red-300 dark:border-red-500 @enderror" 
                        id="county" 
                        name="county" 
                        required>
                  <option value="">Select County</option>
                  <!-- Counties will be populated by JavaScript -->
                </select>
                @error('county')
                  <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>

              <!-- Sub County/Constituency Selection -->
              <div>
                <label for="sub_county" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Constituency
                </label>
                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('sub_county') border-red-300 dark:border-red-500 @enderror" 
                        id="sub_county" 
                        name="sub_county">
                  <option value="">Select Constituency</option>
                  <!-- Constituencies will be populated by JavaScript based on county selection -->
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

        <!-- Record Period -->
        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50">
          <div class="section-header cursor-pointer p-4 border-b border-gray-200 dark:border-gray-700" data-section="period">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Record Period</h4>
                <span class="completion-badge" data-section="period">0%</span>
              </div>
              <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
          
          <div class="section-content p-4 space-y-4 hidden">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <!-- Year -->
              <div>
                <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Year
                </label>
                <input type="number" 
                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('year') border-red-300 dark:border-red-500 @enderror" 
                       id="year" 
                       name="year" 
                       min="2000" 
                       max="2030"
                       value="{{ old('year', $image->year ?? date('Y')) }}">
                @error('year')
                  <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>

              <!-- Month -->
              <div>
                <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Month
                </label>
                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('month') border-red-300 dark:border-red-500 @enderror" 
                        id="month" 
                        name="month">
                  @php
                    $selectedMonth = old('month', $image->month ?? date('n'));
                    $months = [
                      1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                      5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                      9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ];
                  @endphp
                  @foreach($months as $key => $monthName)
                    <option value="{{ $key }}" {{ $selectedMonth == $key ? 'selected' : '' }}>
                      {{ $monthName }}
                    </option>
                  @endforeach
                </select>
                @error('month')
                  <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col gap-3 pt-4">
          <button type="submit" 
                  class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-theme-xs hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:bg-primary-500 dark:hover:bg-primary-400"
                  id="submitButton">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Create Marriage Record
          </button>
          
          <a href="{{ route('marriages.index') }}" 
             class="w-full inline-flex justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- The JavaScript remains the same as in the previous version -->

<script>
document.addEventListener('DOMContentLoaded', function() {
  const marriageForm = document.getElementById('marriageForm');
  const submitButton = document.getElementById('submitButton');
  const marriageTypeSelect = document.getElementById('marriage_type_id');
  const extensionsSection = document.getElementById('marriageExtensionsSection');
  const extensionDescription = document.getElementById('extensionDescription');
  const extensionFields = document.getElementById('extensionFields');
  const countySelect = document.getElementById('county');
  const constituencySelect = document.getElementById('sub_county');
  const certificateImage = document.getElementById('certificateImage');
  const imageContainer = document.getElementById('imageContainer');
  const zoomLevel = document.getElementById('zoomLevel');
  const zoomInBtn = document.getElementById('zoomIn');
  const zoomOutBtn = document.getElementById('zoomOut');
  const resetZoomBtn = document.getElementById('resetZoom');

  // Image zoom and pan variables
  let scale = 1;
  let isDragging = false;
  let startX, startY, translateX = 0, translateY = 0;

  // Get counties with constituencies data from Laravel
  const countiesData = @json($countiesWithConstituencies);

  // Initialize image zoom and pan
  function initImageZoom() {
    if (!certificateImage) return;

    // Zoom functionality
    zoomInBtn.addEventListener('click', () => {
      scale = Math.min(scale + 0.1, 3);
      updateImageTransform();
    });

    zoomOutBtn.addEventListener('click', () => {
      scale = Math.max(scale - 0.1, 0.5);
      updateImageTransform();
    });

    resetZoomBtn.addEventListener('click', () => {
      scale = 1;
      translateX = 0;
      translateY = 0;
      updateImageTransform();
    });

    // Pan functionality
    imageContainer.addEventListener('mousedown', startDrag);
    imageContainer.addEventListener('touchstart', startDrag);
    document.addEventListener('mousemove', drag);
    document.addEventListener('touchmove', drag);
    document.addEventListener('mouseup', endDrag);
    document.addEventListener('touchend', endDrag);

    // Prevent default touch behavior
    imageContainer.addEventListener('touchmove', (e) => {
      e.preventDefault();
    }, { passive: false });
  }

  function startDrag(e) {
    isDragging = true;
    const clientX = e.clientX || e.touches[0].clientX;
    const clientY = e.clientY || e.touches[0].clientY;
    startX = clientX - translateX;
    startY = clientY - translateY;
    imageContainer.style.cursor = 'grabbing';
  }

  function drag(e) {
    if (!isDragging) return;
    e.preventDefault();
    
    const clientX = e.clientX || e.touches[0].clientX;
    const clientY = e.clientY || e.touches[0].clientY;
    
    translateX = clientX - startX;
    translateY = clientY - startY;
    
    updateImageTransform();
  }

  function endDrag() {
    isDragging = false;
    imageContainer.style.cursor = 'grab';
  }

  function updateImageTransform() {
    certificateImage.style.transform = `scale(${scale}) translate(${translateX}px, ${translateY}px)`;
    zoomLevel.textContent = `${Math.round(scale * 100)}%`;
  }

  // Populate counties dropdown
  function populateCounties() {
    countySelect.innerHTML = '<option value="">Select County</option>';
    Object.keys(countiesData).sort().forEach(county => {
      const option = document.createElement('option');
      option.value = county;
      option.textContent = county;
      countySelect.appendChild(option);
    });
  }

  // Update constituencies based on selected county
  function updateConstituencies() {
    const selectedCounty = countySelect.value;
    constituencySelect.innerHTML = '<option value="">Select Constituency</option>';
    
    if (selectedCounty && countiesData[selectedCounty]) {
      // Sort constituencies alphabetically
      const sortedConstituencies = countiesData[selectedCounty].sort();
      
      sortedConstituencies.forEach(constituency => {
        const option = document.createElement('option');
        option.value = constituency;
        option.textContent = constituency;
        constituencySelect.appendChild(option);
      });
      
      // Enable constituency dropdown
      constituencySelect.disabled = false;
    } else {
      // Disable constituency dropdown if no county selected
      constituencySelect.disabled = true;
    }
  }

  // Enhanced collapsible sections functionality
  function initCollapsibleSections() {
    // Main section toggle
    document.querySelectorAll('.section-header').forEach(header => {
      header.addEventListener('click', function() {
        const content = this.parentElement.querySelector('.section-content');
        const icon = this.querySelector('svg');
        
        content.classList.toggle('hidden');
        icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
      });
    });

    // Subsection toggle
    document.querySelectorAll('.subsection-header').forEach(header => {
      header.addEventListener('click', function() {
        const content = this.parentElement.querySelector('.subsection-content');
        const icon = this.querySelector('svg');
        
        content.classList.toggle('hidden');
        icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
      });
    });

    // Parent subsection toggle
    document.querySelectorAll('.parent-subsection-header').forEach(header => {
      header.addEventListener('click', function() {
        const content = this.parentElement.querySelector('.parent-subsection-content');
        const icon = this.querySelector('svg');
        
        content.classList.toggle('hidden');
        icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
      });
    });

    // Open first main section by default
    const firstSection = document.querySelector('.section-header');
    if (firstSection) {
      const firstContent = firstSection.parentElement.querySelector('.section-content');
      const firstIcon = firstSection.querySelector('svg');
      if (firstContent && firstContent.classList.contains('hidden')) {
        firstContent.classList.remove('hidden');
        firstIcon.style.transform = 'rotate(180deg)';
      }
    }
  }

  // Completion tracking functionality
  function initCompletionTracking() {
    const form = document.getElementById('marriageForm');
    const completionBar = document.getElementById('completionBar');
    const completionPercentage = document.getElementById('completionPercentage');
    
    // Field configurations for completion tracking
    const fieldConfigs = {
      'basic': ['certificate_serial', 'marriage_type_id', 'marriage_date', 'venue'],
      'spouses': ['husband_name', 'wife_name'],
      'witnesses': ['witness1_name', 'witness2_name'],
      'location': ['county'],
      'extensions': [], // Dynamic based on marriage type
      'period': ['year', 'month']
    };

    // Person field configurations
    const personFieldConfigs = {
      'husband': ['husband_name', 'husband_occupation', 'husband_address', 'husband_father_name', 'husband_mother_name'],
      'wife': ['wife_name', 'wife_occupation', 'wife_address', 'wife_father_name', 'wife_mother_name'],
      'witness1': ['witness1_name', 'witness1_occupation', 'witness1_address'],
      'witness2': ['witness2_name', 'witness2_occupation', 'witness2_address']
    };

    function calculateCompletion() {
      let totalFields = 0;
      let completedFields = 0;
      const sectionCompletions = {};

      // Calculate section completions
      Object.keys(fieldConfigs).forEach(section => {
        const fields = fieldConfigs[section];
        let sectionCompleted = 0;
        
        fields.forEach(fieldName => {
          const field = form.querySelector(`[name="${fieldName}"]`);
          if (field) {
            totalFields++;
            if (field.value && field.value.trim() !== '') {
              completedFields++;
              sectionCompleted++;
            }
          }
        });

        // Calculate section percentage
        const sectionPercentage = fields.length > 0 ? Math.round((sectionCompleted / fields.length) * 100) : 0;
        sectionCompletions[section] = sectionPercentage;

        // Update section badge
        const badge = document.querySelector(`.completion-badge[data-section="${section}"]`);
        if (badge) {
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

        const personPercentage = Math.round((personCompleted / fields.length) * 100);
        
        // Update person badge
        const badge = document.querySelector(`.person-completion-badge[data-person="${person}"]`);
        if (badge) {
          badge.textContent = `${personPercentage}%`;
          badge.className = `person-completion-badge ${getBadgeColor(personPercentage)}`;
        }
      });

      // Calculate overall percentage
      const overallPercentage = totalFields > 0 ? Math.round((completedFields / totalFields) * 100) : 0;
      
      // Update progress bar and percentage
      completionBar.style.width = `${overallPercentage}%`;
      completionPercentage.textContent = `${overallPercentage}%`;

      return overallPercentage;
    }

    function getBadgeColor(percentage) {
      if (percentage >= 80) return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
      if (percentage >= 50) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
      return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
    }

    // Listen to all form input changes
    form.addEventListener('input', calculateCompletion);
    form.addEventListener('change', calculateCompletion);

    // Initial calculation
    setTimeout(calculateCompletion, 100);
  }

  // Marriage type configurations
  const marriageTypeConfigs = {
    'christian': {
      description: 'Christian marriage requires church and pastor information',
      fields: [
        {
          name: 'church_org',
          label: 'Church/Organization',
          type: 'text',
          required: true,
          placeholder: 'Enter church or religious organization name'
        },
        {
          name: 'pastor_name',
          label: 'Pastor/Officiant Name',
          type: 'text',
          required: true,
          placeholder: 'Enter pastor or marriage officiant name'
        },
        {
          name: 'entry_no',
          label: 'Church Entry Number',
          type: 'text',
          required: false,
          placeholder: 'Church registry entry number (optional)'
        }
      ]
    },
    'muslim': {
      description: 'Islamic marriage requires Mahr information and Muslim officer details',
      fields: [
        {
          name: 'mahr_agreed',
          label: 'Mahr Agreed Amount',
          type: 'text',
          required: true,
          placeholder: 'Enter agreed Mahr amount'
        },
        {
          name: 'mahr_paid',
          label: 'Mahr Paid Amount',
          type: 'text',
          required: false,
          placeholder: 'Enter paid Mahr amount (optional)'
        },
        {
          name: 'mahr_deferred',
          label: 'Mahr Deferred Amount',
          type: 'text',
          required: false,
          placeholder: 'Enter deferred Mahr amount (optional)'
        },
        {
          name: 'muslim_officer',
          label: 'Muslim Marriage Officer',
          type: 'text',
          required: true,
          placeholder: 'Enter Muslim marriage officer name'
        },
        {
          name: 'gifts',
          label: 'Additional Gifts',
          type: 'text',
          required: false,
          placeholder: 'Enter any additional gifts (optional)'
        }
      ]
    },
    'hindu': {
      description: 'Hindu marriage requires temple and dowry information',
      fields: [
        {
          name: 'temple',
          label: 'Temple Name',
          type: 'text',
          required: true,
          placeholder: 'Enter temple name where marriage was conducted'
        },
        {
          name: 'dowry',
          label: 'Dowry Details',
          type: 'text',
          required: false,
          placeholder: 'Enter dowry details (optional)'
        },
        {
          name: 'gifts',
          label: 'Wedding Gifts',
          type: 'text',
          required: false,
          placeholder: 'Enter wedding gifts exchanged (optional)'
        }
      ]
    },
    'civil': {
      description: 'Civil marriage requires basic registration information',
      fields: [
        {
          name: 'entry_no',
          label: 'Registration Entry Number',
          type: 'text',
          required: false,
          placeholder: 'Civil registration entry number (optional)'
        }
      ]
    }
  };

  // Function to show/hide extension fields based on marriage type
  function updateExtensionFields() {
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
        
        let input;
        if (field.type === 'select') {
          input = document.createElement('select');
          input.className = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800';
          if (field.options) {
            field.options.forEach(option => {
              const optionElement = document.createElement('option');
              optionElement.value = option.value;
              optionElement.textContent = option.label;
              input.appendChild(optionElement);
            });
          }
        } else {
          input = document.createElement('input');
          input.type = field.type;
          input.className = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800';
          input.placeholder = field.placeholder || '';
        }
        
        input.id = field.name;
        input.name = field.name;
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

  // Initialize everything
  function init() {
    populateCounties();
    updateConstituencies(); // Initialize with empty constituencies
    initCollapsibleSections();
    initCompletionTracking();
    initImageZoom();
    updateExtensionFields();
    
    // Event listeners
    countySelect.addEventListener('change', updateConstituencies);
    marriageTypeSelect.addEventListener('change', updateExtensionFields);
    
    marriageForm.addEventListener('submit', function() {
      submitButton.disabled = true;
      submitButton.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Creating Record...
      `;
    });
    
    document.getElementById('certificate_serial')?.focus();
    
    const marriageDateField = document.getElementById('marriage_date');
    if (marriageDateField && !marriageDateField.value) {
      marriageDateField.value = new Date().toISOString().split('T')[0];
    }
  }

  // Start the initialization
  init();
});
</script>
<style>
.completion-badge, .person-completion-badge {
  @apply inline-flex items-center px-2 py-1 rounded-full text-xs font-medium;
}

.min-h-\[600px\] {
  min-height: 600px;
}

.rotate-180 {
  transform: rotate(180deg);
}

@media (max-width: 1280px) {
  .min-h-\[600px\] {
    min-height: 500px;
  }
}

@media (max-width: 1024px) {
  .min-h-\[600px\] {
    min-height: 400px;
  }
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
</style>
@endsection