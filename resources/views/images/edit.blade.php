@extends('layouts.app')

@section('content')
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-6 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
  <div class="flex flex-col gap-2 mb-6 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Edit Image</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Update image details and processing options - All images are processed with A4 background
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

  <!-- File Missing Warning -->
  @if(!$fileExists)
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
      <div class="flex items-center gap-3">
        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
        <div>
          <h4 class="text-sm font-medium text-red-800 dark:text-red-400">Original Image File Not Found</h4>
          <p class="text-sm text-red-600 dark:text-red-300 mt-1">
            The original image file was not found in storage. You can still update the image details or upload a new image file.
          </p>
        </div>
      </div>
    </div>
  @endif

  <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
    <!-- Image Preview & Rotation -->
    <div class="lg:col-span-2">
      <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50 h-full">
        <div class="flex flex-col h-full">
          <!-- Image Header -->
          <div class="flex items-center justify-between mb-4">
            <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">A4 Image Preview</h4>
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-3 py-1 text-sm font-medium text-primary-700 dark:bg-primary-900/20 dark:text-primary-400">
                {{ $image->year }}/{{ str_pad($image->month, 2, '0', STR_PAD_LEFT) }}
              </span>
              <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ $image->formatted_file_size }}
              </span>
              <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                {{ $image->fit_type_display }}
              </span>
            </div>
          </div>
          
          <!-- Main Image Display -->
          <div class="flex-1 flex flex-col items-center justify-center min-h-[500px] bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-900 p-4">
            @if($fileExists)
              <div class="relative w-full h-full flex items-center justify-center">
                <img src="{{ $image->image_url }}?t={{ time() }}" 
                     alt="{{ $image->name }}" 
                     class="max-w-full max-h-full object-contain transition-transform duration-300"
                     id="rotatableImage"
                     style="transform: rotate(0deg)">
              </div>
              
              <!-- Rotation Controls -->
              <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 w-full">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Rotate & Save</span>
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
                  <span class="text-xs text-gray-500">Rotation will be applied when you save</span>
                </div>
              </div>
            @else
              <div class="text-center text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-lg font-medium">Image File Not Available</p>
                <p class="text-sm mt-2">Upload a new image file to replace the missing one.</p>
              </div>
            @endif
          </div>
          
          <!-- Image Info Footer -->
          <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
              <span class="font-medium text-gray-800 dark:text-white/90">{{ $image->name }}</span>
              <span>{{ $image->filename }}</span>
            </div>
            @if($image->certificate_serial)
            <div class="mt-2 text-sm text-gray-500 dark:text-gray-500">
              Certificate Serial: <span class="font-mono">{{ $image->certificate_serial }}</span>
            </div>
            @endif
            <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
              Uploaded {{ $image->created_at->format('M j, Y g:i A') }} • 
              Last updated {{ $image->updated_at->format('M j, Y g:i A') }}
            </div>
          </div>
        </div>
      </div>
    </div>

    

    <!-- Edit Form -->
    <div class="space-y-6">
        
      <form method="POST" action="{{ route('images.update', $image) }}" enctype="multipart/form-data" class="space-y-6" id="editForm">
        @csrf
        @method('PUT')

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
                 value="{{ old('name', $image->name) }}" 
                 placeholder="Enter image name"
                 required>
          @error('name')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
          @enderror
        </div>

        <!-- New Image Upload -->
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800/50">
          <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Replace Image <span class="text-sm font-normal text-gray-500">(Optional)</span>
          </label>
          <div class="relative">
            <input type="file" 
                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:file:bg-primary-900/20 dark:file:text-primary-400 dark:hover:file:bg-primary-900/30 @error('image') border-red-300 dark:border-red-500 @enderror" 
                   id="image" 
                   name="image" 
                   accept="image/*">
          </div>
          @error('image')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
          @enderror
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            New images will be automatically converted to A4 PNG format and compressed to ~50KB
          </p>
          
          <!-- File Preview for New Upload -->
          <div id="newImagePreview" class="mt-4 hidden">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Image Preview</p>
            <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg dark:border-gray-700 bg-gray-50 dark:bg-gray-800/30">
              <img id="previewImage" 
                   class="h-20 w-20 object-cover rounded-lg border border-gray-200 dark:border-gray-600"
                   alt="New image preview">
              <div class="flex-1">
                <p id="fileName" class="text-sm text-gray-600 dark:text-gray-400"></p>
                <p id="fileSize" class="text-xs text-gray-500 dark:text-gray-500"></p>
              </div>
            </div>
          </div>
          
          @if($fileExists)
            <div class="mt-4 p-4 border border-gray-200 rounded-lg dark:border-gray-700 bg-gray-50 dark:bg-gray-800/30">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current A4 Image</p>
              <div class="flex items-center gap-4">
                <img src="{{ $image->image_url }}" alt="{{ $image->name }}" class="h-20 w-20 object-cover rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="flex-1">
                  <p class="text-sm text-gray-600 dark:text-gray-400">{{ $image->filename }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-500">
                    {{ $image->formatted_file_size }} • Uploaded {{ $image->created_at->format('M j, Y') }}
                  </p>
                </div>
              </div>
            </div>
          @endif
        </div>

        <!-- Fit Type -->
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800/50">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Image Fit Type <span class="text-red-500">*</span>
          </label>
          
          <div class="space-y-3">
            <!-- Stretch Option -->
            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700/50 transition-colors">
              <input type="radio" name="fit_type" value="stretch" {{ old('fit_type', $image->fit_type) == 'stretch' ? 'checked' : '' }} 
                     class="text-primary-600 focus:ring-primary-500">
              <div>
                <span class="text-sm font-medium text-gray-900 dark:text-white/90">Stretch to Fill</span>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Landscape touches sides, Portrait touches top/bottom</p>
              </div>
            </label>

            <!-- Fit Option -->
            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700/50 transition-colors">
              <input type="radio" name="fit_type" value="fit" {{ old('fit_type', $image->fit_type) == 'fit' ? 'checked' : '' }} 
                     class="text-primary-600 focus:ring-primary-500">
              <div>
                <span class="text-sm font-medium text-gray-900 dark:text-white/90">Fit Within</span>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maintains aspect ratio within A4 boundaries</p>
              </div>
            </label>

            <!-- Original Option -->
            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700/50 transition-colors">
              <input type="radio" name="fit_type" value="original" {{ old('fit_type', $image->fit_type) == 'original' ? 'checked' : '' }} 
                     class="text-primary-600 focus:ring-primary-500">
              <div>
                <span class="text-sm font-medium text-gray-900 dark:text-white/90">Original Size</span>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Keeps original dimensions, centered on A4</p>
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
                 value="{{ old('certificate_serial', $image->certificate_serial) }}"
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
              <input 
                type="number"
                id="year"
                name="year"
                value="{{ old('year', $image->year) }}"
                min="2000"
                max="2030"
                required
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 
                    text-sm font-medium text-gray-700 shadow-theme-xs 
                    focus:border-primary-500 focus:ring-2 focus:ring-primary-200 
                    dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 
                    dark:focus:border-primary-500 dark:focus:ring-primary-800
                    @error('year') border-red-300 dark:border-red-500 @enderror" 
              />
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
                  $selectedMonth = old('month', $image->month);
                @endphp
                @foreach(range(1, 12) as $m)
                  <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Save Changes
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

<!-- Image Rotation Script -->
@if($fileExists)
<script>
document.addEventListener('DOMContentLoaded', function() {
  const rotatableImage = document.getElementById('rotatableImage');
  const rotationValue = document.getElementById('rotationValue');
  const rotationAngle = document.getElementById('rotationAngle');
  const rotateLeft = document.getElementById('rotateLeft');
  const rotateRight = document.getElementById('rotateRight');
  
  let currentRotation = 0;

  // Rotation functionality
  function updateRotation(degrees) {
    currentRotation = degrees;
    rotatableImage.style.transform = `rotate(${degrees}deg)`;
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
});
</script>
@endif

<!-- File Upload Preview Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const imageInput = document.getElementById('image');
  const newImagePreview = document.getElementById('newImagePreview');
  const previewImage = document.getElementById('previewImage');
  const fileName = document.getElementById('fileName');
  const fileSize = document.getElementById('fileSize');
  const submitButton = document.getElementById('submitButton');

  // Handle file selection for preview
  imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        previewImage.src = e.target.result;
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        newImagePreview.classList.remove('hidden');
      };
      
      reader.readAsDataURL(file);
    } else {
      newImagePreview.classList.add('hidden');
    }
  });

  // Format file size
  function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  // Form submission handling
  const editForm = document.getElementById('editForm');
  editForm.addEventListener('submit', function() {
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
});
</script>

<style>
#rotatableImage {
  transition: transform 0.3s ease-in-out;
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

/* Style for radio buttons */
input[type="radio"] {
  border-color: #d1d5db;
}

input[type="radio"]:checked {
  border-color: #2563eb;
  background-color: #2563eb;
}

.dark input[type="radio"] {
  border-color: #4b5563;
}

.dark input[type="radio"]:checked {
  border-color: #3b82f6;
  background-color: #3b82f6;
}
</style>
@endsection