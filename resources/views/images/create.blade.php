@extends('layouts.app')

@section('content')
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-6 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
  <div class="flex flex-col gap-2 mb-6 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Upload New Image</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Add a new image to your collection with A4 background
      </p>
    </div>
    
    <a href="{{ route('images.index') }}" 
       class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      Back to Images
    </a>
  </div>

  <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
    <!-- Image Preview & Rotation - Takes 2/3 of the width -->
    <div class="lg:col-span-2">
      <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50 h-full">
        <div class="flex flex-col h-full">
          <!-- Image Header -->
          <div class="flex items-center justify-between mb-4">
            <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Image Preview & Rotation</h4>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
                Rotate Before Upload
              </span>
            </div>
          </div>
          
          <!-- Main Image Display -->
          <div class="flex-1 flex flex-col items-center justify-center min-h-[500px] bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-900 p-4">
            <div class="relative w-full h-full flex items-center justify-center">
              <!-- Preview Placeholder -->
              <div id="previewPlaceholder" class="text-center text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-lg font-medium">No Image Selected</p>
                <p class="text-sm mt-2">Upload an image to see preview and rotation options</p>
              </div>
              
              <!-- Preview Image -->
              <img id="previewImage" 
                   class="max-w-full max-h-full object-contain transition-transform duration-300 hidden"
                   alt="Image preview"
                   style="transform: rotate(0deg)">
            </div>
            
            <!-- Rotation Controls -->
            <div id="rotationControls" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 hidden w-full">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Rotate Image</span>
                <div class="flex items-center gap-2">
                  <button type="button" 
                          id="rotateLeft" 
                          class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    90° Left
                  </button>
                  
                  <button type="button" 
                          id="rotateRight" 
                          class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" transform="rotate(180 12 12)" />
                    </svg>
                    90° Right
                  </button>
                </div>
              </div>
              <div class="mt-2 flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                <span>Current rotation: <span id="rotationValue" class="font-mono">0°</span></span>
                <span class="text-xs text-gray-500">Rotation will be applied when you upload</span>
              </div>
            </div>
          </div>
          
          <!-- Processing Info -->
          <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
              <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                  <h4 class="text-sm font-medium text-blue-800 dark:text-blue-400">Image Processing Information</h4>
                  <ul class="mt-2 text-sm text-blue-700 dark:text-blue-300 space-y-1">
                    <li>• Images are converted to A4 size (595x842 pixels)</li>
                    <li>• Compressed to ~50KB file size</li>
                    <li>• Saved as PNG format with white background</li>
                    <li>• Filename format: year_month_certificate_serial.png</li>
                    <li>• Rotation is applied during processing</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Form - Takes 1/3 of the width -->
    <div class="space-y-6">
      <form method="POST" action="{{ route('images.store') }}" enctype="multipart/form-data" class="space-y-6" id="uploadForm">
        @csrf

        <!-- Hidden rotation field -->
        <input type="hidden" name="rotation_angle" id="rotationAngle" value="0">

        <!-- Image Name -->
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800/50">
          <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Image Name <span class="text-red-500">*</span>
          </label>
          <input type="text" 
                 class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('name') border-red-300 dark:border-red-500 @enderror" 
                 id="name" 
                 name="name" 
                 value="{{ old('name') }}" 
                 placeholder="Enter image name"
                 required>
          @error('name')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
          @enderror
        </div>

        <!-- Image File Upload -->
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800/50">
          <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Image File <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input type="file" 
                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:file:bg-primary-900/20 dark:file:text-primary-400 dark:hover:file:bg-primary-900/30 @error('image') border-red-300 dark:border-red-500 @enderror" 
                   id="image" 
                   name="image" 
                   accept="image/*"
                   required>
          </div>
          @error('image')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
          @enderror
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Supported formats: JPG, PNG, GIF, WEBP. Max size: 10MB.
          </p>
        </div>
<!-- Fit Type -->
<div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800/50">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
        Image Fit Type <span class="text-red-500">*</span>
    </label>
    
    <div class="space-y-3">
        <!-- Stretch to Touch Edges Option -->
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="radio" name="fit_type" value="stretch" {{ old('fit_type', $image->fit_type ?? 'stretch') == 'stretch' ? 'checked' : '' }} 
                   class="text-primary-600 focus:ring-primary-500">
            <div>
                <span class="text-sm font-medium text-gray-900 dark:text-white/90">Stretch to Touch Edges</span>
                <p class="text-xs text-gray-500 dark:text-gray-400">Maintains aspect ratio, touches all edges (recommended for certificates)</p>
            </div>
        </label>

        <!-- True Stretch Option -->
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="radio" name="fit_type" value="true_stretch" {{ old('fit_type', $image->fit_type ?? 'stretch') == 'true_stretch' ? 'checked' : '' }} 
                   class="text-primary-600 focus:ring-primary-500">
            <div>
                <span class="text-sm font-medium text-gray-900 dark:text-white/90">True Stretch</span>
                <p class="text-xs text-gray-500 dark:text-gray-400">Forces exact A4 dimensions (may distort image)</p>
            </div>
        </label>

        <!-- Fit Within Option -->
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="radio" name="fit_type" value="fit" {{ old('fit_type', $image->fit_type ?? 'stretch') == 'fit' ? 'checked' : '' }} 
                   class="text-primary-600 focus:ring-primary-500">
            <div>
                <span class="text-sm font-medium text-gray-900 dark:text-white/90">Fit Within</span>
                <p class="text-xs text-gray-500 dark:text-gray-400">Maintains aspect ratio within A4 boundaries</p>
            </div>
        </label>

        <!-- Original Size Option -->
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="radio" name="fit_type" value="original" {{ old('fit_type', $image->fit_type ?? 'stretch') == 'original' ? 'checked' : '' }} 
                   class="text-primary-600 focus:ring-primary-500">
            <div>
                <span class="text-sm font-medium text-gray-900 dark:text-white/90">Original Size</span>
                <p class="text-xs text-gray-500 dark:text-gray-400">Keeps original dimensions, centered on A4</p>
            </div>
        </label>
    </div>

    @error('fit_type')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

        <!-- Certificate Serial -->
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800/50">
          <label for="certificate_serial" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Certificate Serial
          </label>
          <input type="text" 
                 class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('certificate_serial') border-red-300 dark:border-red-500 @enderror" 
                 id="certificate_serial" 
                 name="certificate_serial" 
                 value="{{ old('certificate_serial') }}"
                 placeholder="Enter certificate serial number">
          @error('certificate_serial')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
          @enderror
        </div>

        <!-- Year and Month -->
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800/50">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- Year -->
            <div>
              <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Year <span class="text-red-500">*</span>
              </label>
              <input type="number" 
                     class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-400 hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-500 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('year') border-red-300 dark:border-red-500 @enderror" 
                     id="year" 
                     name="year" 
                     min="2000" 
                     max="2030"
                     value="{{ old('year', date('Y')) }}" 
                     required>
              @error('year')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
              @enderror
            </div>

            <!-- Month -->
            <div>
              <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Month <span class="text-red-500">*</span>
              </label>
              <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:focus:border-primary-500 dark:focus:ring-primary-800 @error('month') border-red-300 dark:border-red-500 @enderror" 
                      id="month" 
                      name="month" 
                      required>
                @php
                  $currentMonth = date('n');
                  $selectedMonth = old('month', $currentMonth);
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

        <!-- Form Actions -->
        <div class="flex flex-col gap-3 pt-4">
          <button type="submit" 
                  class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-theme-xs hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:bg-primary-500 dark:hover:bg-primary-400"
                  id="submitButton">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Upload Image with A4 Background
          </button>
          
          <a href="{{ route('images.index') }}" 
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

<!-- Image Preview & Rotation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const imageInput = document.getElementById('image');
  const previewImage = document.getElementById('previewImage');
  const previewPlaceholder = document.getElementById('previewPlaceholder');
  const rotationControls = document.getElementById('rotationControls');
  const rotationValue = document.getElementById('rotationValue');
  const rotationAngle = document.getElementById('rotationAngle');
  const rotateLeft = document.getElementById('rotateLeft');
  const rotateRight = document.getElementById('rotateRight');
  const submitButton = document.getElementById('submitButton');
  const uploadForm = document.getElementById('uploadForm');
  
  let currentRotation = 0;

  // Handle file selection
  imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        previewImage.src = e.target.result;
        previewImage.classList.remove('hidden');
        previewPlaceholder.classList.add('hidden');
        rotationControls.classList.remove('hidden');
        
        // Reset rotation when new image is selected
        currentRotation = 0;
        updateRotation(0);
      };
      
      reader.readAsDataURL(file);
    } else {
      previewImage.classList.add('hidden');
      previewPlaceholder.classList.remove('hidden');
      rotationControls.classList.add('hidden');
    }
  });
  
  // Rotation functionality
  function updateRotation(degrees) {
    currentRotation = degrees;
    previewImage.style.transform = `rotate(${degrees}deg)`;
    rotationValue.textContent = `${degrees}°`;
    rotationAngle.value = degrees;
  }

  rotateLeft.addEventListener('click', function() {
    currentRotation -= 90;
    updateRotation(currentRotation);
  });

  rotateRight.addEventListener('click', function() {
    currentRotation += 90;
    updateRotation(currentRotation);
  });

  // Keyboard shortcuts for rotation
  document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
      switch(e.key) {
        case 'ArrowLeft':
          e.preventDefault();
          rotateLeft.click();
          break;
        case 'ArrowRight':
          e.preventDefault();
          rotateRight.click();
          break;
      }
    }
  });
  
  // Form submission handling
  uploadForm.addEventListener('submit', function() {
    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = `
      <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      Processing Image...
    `;
  });
  
  // Auto-focus on name field
  document.getElementById('name')?.focus();
});
</script>

<style>
#previewImage {
  transition: transform 0.3s ease-in-out;
  max-height: 400px;
  max-width: 100%;
}

@media (max-width: 1024px) {
  .min-h-\[500px\] {
    min-height: 400px;
  }
}

@media (max-width: 768px) {
  .min-h-\[500px\] {
    min-height: 300px;
  }
}
</style>
@endsection