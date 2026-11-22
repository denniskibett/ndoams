@forelse($groupedImages as $year => $months)
<div class="mb-8">
  <!-- Year Header -->
  <div class="mb-4 border-b border-gray-200 pb-2 dark:border-gray-700">
    <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">{{ $year }}</h2>
  </div>

  <!-- Months Grid -->
  @foreach($months as $month => $images)
    <div class="mb-6">
      <!-- Month Header -->
      <h3 class="text-lg font-semibold text-gray-700 dark:text-white/80 mb-4">
        {{ \DateTime::createFromFormat('!m', $month)->format('F') }}
        <span class="text-sm font-normal text-gray-500">({{ $images->count() }} images)</span>
      </h3>

      <!-- Images Grid -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($images as $image)
          <!-- Grid item content from original index.blade.php -->
          @include('partials.layouts.grid-item', ['image' => $image])
        @endforeach
      </div>
    </div>
  @endforeach
</div>
@empty
<!-- Empty state -->
@endforelse