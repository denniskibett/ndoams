@extends('layouts.app')

@section('content')
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-6 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
  <div class="flex flex-col gap-2 mb-6 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Image Details</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        View and manage image information
      </p>
    </div>
    
    <div class="flex items-center gap-2">
      <a href="{{ route('images.index') }}" 
         class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Images
      </a>
      
      <a href="{{ route('images.edit', $image) }}" 
         class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Edit
      </a>
      
      <form action="{{ route('images.destroy', $image) }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" 
                class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 shadow-theme-xs hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30"
                onclick="return confirm('Are you sure you want to delete this image?')">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Delete
        </button>
      </form>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
    <!-- Image Preview - Takes 2/3 of the width -->
    <div class="lg:col-span-2">
      <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50 h-full">
        <div class="flex flex-col h-full">
          <!-- Image Header -->
          <div class="flex items-center justify-between mb-4">
            <h4 class="text-md font-semibold text-gray-800 dark:text-white/90">Image Preview</h4>
            <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-3 py-1 text-sm font-medium text-primary-700 dark:bg-primary-900/20 dark:text-primary-400">
              {{ $image->year }}/{{ str_pad($image->month, 2, '0', STR_PAD_LEFT) }}
            </span>
          </div>
          
          <!-- Main Image Display -->
          <div class="flex-1 flex items-center justify-center min-h-[500px]">
            <img src="{{ $image->image_url }}" 
                 alt="{{ $image->name }}" 
                 class="max-w-full max-h-full rounded-lg object-contain shadow-lg"
                 id="mainImage">
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
          </div>
        </div>
      </div>
    </div>

    <!-- Image Details - Takes 1/3 of the width -->
    <div class="space-y-6">
      <!-- Basic Information -->
      <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800/50">
        <h4 class="text-md font-semibold text-gray-800 dark:text-white/90 mb-4">Basic Information</h4>
        <div class="space-y-4">
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mb-1">Name</span>
            <span class="text-sm text-gray-800 dark:text-white/90">{{ $image->name }}</span>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mb-1">Filename</span>
            <span class="text-sm text-gray-800 dark:text-white/90 font-mono break-all">{{ $image->filename }}</span>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mb-1">Certificate Serial</span>
            <span class="text-sm text-gray-800 dark:text-white/90">{{ $image->certificate_serial ?? 'N/A' }}</span>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mb-1">Period</span>
            <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 dark:bg-primary-900/20 dark:text-primary-400">
              {{ $image->year }}/{{ str_pad($image->month, 2, '0', STR_PAD_LEFT) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Metadata -->
      <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800/50">
        <h4 class="text-md font-semibold text-gray-800 dark:text-white/90 mb-4">Metadata</h4>
        <div class="space-y-4">
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mb-1">Uploaded By</span>
            <span class="text-sm text-gray-800 dark:text-white/90">{{ $image->uploader->name }}</span>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mb-1">Uploaded At</span>
            <span class="text-sm text-gray-800 dark:text-white/90">{{ $image->created_at->format('M j, Y H:i') }}</span>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mb-1">Last Updated By</span>
            <span class="text-sm text-gray-800 dark:text-white/90">{{ $image->updater->name ?? 'N/A' }}</span>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mb-1">Last Updated</span>
            <span class="text-sm text-gray-800 dark:text-white/90">{{ $image->updated_at->format('M j, Y H:i') }}</span>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800/50">
        <h4 class="text-md font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h4>
        <div class="space-y-3">
          <a href="{{ route('images.edit', $image) }}" 
             class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 text-sm font-medium text-primary-700 shadow-theme-xs hover:bg-primary-100 dark:border-primary-800 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Image Details
          </a>
          
          <a href="{{ $image->image_url }}" 
             download="{{ $image->filename }}"
             class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download Image
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Ensure the image container maintains proper dimensions */
#mainImage {
  transition: transform 0.2s ease-in-out;
}

#mainImage:hover {
  transform: scale(1.02);
}

/* Responsive adjustments */
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