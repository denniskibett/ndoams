<!-- resources/views/images/debug-create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-6 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
  <div class="flex flex-col gap-2 mb-6 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Debug: Create Image Data</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Preview of data that would be inserted into database
      </p>
    </div>
    
    <div class="flex gap-2">
      <a href="{{ route('images.create') }}" 
         class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Create
      </a>
      
      <form method="POST" action="{{ route('images.store') }}" enctype="multipart/form-data" class="inline">
        @csrf
        @foreach($validated as $key => $value)
          @if($key !== 'image')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
          @endif
        @endforeach
        <input type="hidden" name="image" value="{{ $fileInfo['original_name'] }}">
        <button type="submit" 
                class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-green-500 dark:bg-green-500 dark:hover:bg-green-400">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Proceed with Insert
        </button>
      </form>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
    <!-- Validation Data -->
    <div class="space-y-6">
      <div class="rounded-lg border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-900/20">
        <h4 class="text-md font-semibold text-blue-800 dark:text-blue-400 mb-4">Validated Form Data</h4>
        <div class="space-y-3">
          @foreach($validated as $key => $value)
            <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-blue-700">
              <span class="text-sm font-medium text-blue-700 dark:text-blue-300 capitalize">{{ str_replace('_', ' ', $key) }}</span>
              <span class="text-sm font-mono bg-white px-2 py-1 rounded border border-blue-300 dark:bg-blue-800 dark:border-blue-600">
                {{ $value ?? 'NULL' }}
              </span>
            </div>
          @endforeach
        </div>
      </div>

      <!-- File Information -->
      <div class="rounded-lg border border-purple-200 bg-purple-50 p-5 dark:border-purple-800 dark:bg-purple-900/20">
        <h4 class="text-md font-semibold text-purple-800 dark:text-purple-400 mb-4">File Information</h4>
        <div class="space-y-3">
          @foreach($fileInfo as $key => $value)
            <div class="flex justify-between items-center py-2 border-b border-purple-200 dark:border-purple-700">
              <span class="text-sm font-medium text-purple-700 dark:text-purple-300 capitalize">{{ str_replace('_', ' ', $key) }}</span>
              <span class="text-sm font-mono bg-white px-2 py-1 rounded border border-purple-300 dark:bg-purple-800 dark:border-purple-600">
                {{ $value }}
              </span>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- Database Insert Data -->
    <div class="space-y-6">
      <div class="rounded-lg border border-green-200 bg-green-50 p-5 dark:border-green-800 dark:bg-green-900/20">
        <h4 class="text-md font-semibold text-green-800 dark:text-green-400 mb-4">Database Insert Data</h4>
        <div class="space-y-3">
          @foreach($insertData as $key => $value)
            <div class="flex justify-between items-center py-2 border-b border-green-200 dark:border-green-700">
              <span class="text-sm font-medium text-green-700 dark:text-green-300 capitalize">{{ str_replace('_', ' ', $key) }}</span>
              <span class="text-sm font-mono bg-white px-2 py-1 rounded border border-green-300 dark:bg-green-800 dark:border-green-600 break-all">
                {{ $value ?? 'NULL' }}
              </span>
            </div>
          @endforeach
        </div>
      </div>

      <!-- Storage Information -->
      <div class="rounded-lg border border-orange-200 bg-orange-50 p-5 dark:border-orange-800 dark:bg-orange-900/20">
        <h4 class="text-md font-semibold text-orange-800 dark:text-orange-400 mb-4">Storage Information</h4>
        <div class="space-y-3">
          <div class="flex justify-between items-center py-2 border-b border-orange-200 dark:border-orange-700">
            <span class="text-sm font-medium text-orange-700 dark:text-orange-300">Storage Path</span>
            <span class="text-sm font-mono bg-white px-2 py-1 rounded border border-orange-300 dark:bg-orange-800 dark:border-orange-600 break-all">
              {{ $storagePath }}
            </span>
          </div>
          <div class="flex justify-between items-center py-2 border-b border-orange-200 dark:border-orange-700">
            <span class="text-sm font-medium text-orange-700 dark:text-orange-300">Directory Exists</span>
            <span class="text-sm font-mono px-2 py-1 rounded {{ $directoryExists ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-yellow-100 text-yellow-800 border border-yellow-300' }}">
              {{ $directoryExists ? 'YES' : 'NO (will be created)' }}
            </span>
          </div>
          <div class="flex justify-between items-center py-2 border-b border-orange-200 dark:border-orange-700">
            <span class="text-sm font-medium text-orange-700 dark:text-orange-300">File Would Overwrite</span>
            <span class="text-sm font-mono px-2 py-1 rounded {{ $fileWouldOverwrite ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-green-100 text-green-800 border border-green-300' }}">
              {{ $fileWouldOverwrite ? 'YES' : 'NO' }}
            </span>
          </div>
          <div class="flex justify-between items-center py-2">
            <span class="text-sm font-medium text-orange-700 dark:text-orange-300">Full Storage Path</span>
            <span class="text-sm font-mono bg-white px-2 py-1 rounded border border-orange-300 dark:bg-orange-800 dark:border-orange-600 break-all">
              storage/app/public/{{ $storagePath }}
            </span>
          </div>
        </div>
      </div>

      <!-- User Information -->
      <div class="rounded-lg border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800/50">
        <h4 class="text-md font-semibold text-gray-800 dark:text-white/90 mb-4">User Information</h4>
        <div class="space-y-3">
          <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">User ID</span>
            <span class="text-sm font-mono bg-white px-2 py-1 rounded border border-gray-300 dark:bg-gray-800 dark:border-gray-600">
              {{ Auth::id() }}
            </span>
          </div>
          <div class="flex justify-between items-center py-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">User Name</span>
            <span class="text-sm font-mono bg-white px-2 py-1 rounded border border-gray-300 dark:bg-gray-800 dark:border-gray-600">
              {{ Auth::user()->name }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('images.create') }}" 
       class="inline-flex justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
      Cancel and Go Back
    </a>
    
    <form method="POST" action="{{ route('images.store') }}" enctype="multipart/form-data" class="inline">
      @csrf
      @foreach($validated as $key => $value)
        @if($key !== 'image')
          <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
      @endforeach
      <!-- We can't preserve the file in hidden field, so user needs to re-select -->
      <button type="submit" 
              class="inline-flex justify-center items-center gap-2 rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-theme-xs hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 dark:bg-green-500 dark:hover:bg-green-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Proceed with Actual Insert (Re-select File)
      </button>
    </form>
  </div>

  <!-- Important Note -->
  <div class="mt-6 rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
    <div class="flex items-start gap-3">
      <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
      </svg>
      <div>
        <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-400">Important Note</h4>
        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
          This is a debug preview. When you proceed with the actual insert, you'll need to re-select the image file 
          since files cannot be preserved in hidden form fields. All other form data will be preserved.
        </p>
      </div>
    </div>
  </div>
</div>
@endsection