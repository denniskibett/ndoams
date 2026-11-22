@extends('layouts.app')

@section('content')
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-6 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
  
  <!-- Include Image Cards -->
  @include('partials.card.image-cards')
  
  <!-- Header with Layout Toggle -->
  <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">My Images Collection</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        All images are processed with A4 background and compressed to ~50KB
      </p>
    </div>
    
    <div class="flex items-center gap-3">
      <!-- Layout Toggle -->
      <div class="flex rounded-lg border border-gray-300 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
        <button type="button" 
                data-layout="grid"
                class="layout-toggle flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-all bg-primary-100 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
          Grid
        </button>
        <button type="button" 
                data-layout="rows"
                class="layout-toggle flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-all text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
          </svg>
          List
        </button>
      </div>

      <a href="{{ route('images.create') }}" 
         class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:bg-primary-500 dark:hover:bg-primary-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Upload New
      </a>
    </div>
  </div>

  <!-- Images Container -->
  <div id="images-container">
    <!-- Grid Layout (Default) -->
    <div id="grid-layout" class="layout-content">
      @include('partials.layouts.grid-layout')
    </div>

    <!-- Rows Layout (Hidden by default) -->
    <div id="rows-layout" class="layout-content hidden">
      @include('partials.layouts.rows-layout')
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const layoutToggles = document.querySelectorAll('.layout-toggle');
  const layoutContents = document.querySelectorAll('.layout-content');
  
  // Get saved layout or default to 'grid'
  const savedLayout = localStorage.getItem('image-layout') || 'grid';
  
  // Initialize layout
  switchLayout(savedLayout);
  
  // Add click events to layout toggles
  layoutToggles.forEach(toggle => {
    toggle.addEventListener('click', function() {
      const layout = this.dataset.layout;
      switchLayout(layout);
    });
  });
  
  function switchLayout(layout) {
    // Update active toggle
    layoutToggles.forEach(toggle => {
      if (toggle.dataset.layout === layout) {
        toggle.classList.add('bg-primary-100', 'text-primary-700', 'dark:bg-primary-900/20', 'dark:text-primary-400');
        toggle.classList.remove('text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-200');
      } else {
        toggle.classList.remove('bg-primary-100', 'text-primary-700', 'dark:bg-primary-900/20', 'dark:text-primary-400');
        toggle.classList.add('text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-200');
      }
    });
    
    // Show/hide layout contents
    layoutContents.forEach(content => {
      if (content.id === `${layout}-layout`) {
        content.classList.remove('hidden');
      } else {
        content.classList.add('hidden');
      }
    });
    
    // Save preference
    localStorage.setItem('image-layout', layout);
    
    // Update URL without reload
    const url = new URL(window.location);
    url.searchParams.set('layout', layout);
    window.history.replaceState({}, '', url);
  }
});
</script>
@endpush