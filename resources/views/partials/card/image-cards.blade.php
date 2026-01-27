<!-- Statistics Cards -->
<div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
  <!-- Total Images -->
  <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]">
    <div class="flex items-center gap-4">
      <div class="rounded-xl bg-primary-50 p-3 dark:bg-primary-900/20">
        <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Images</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white/90">{{ $stats['total_images'] ?? 0 }}</p>
      </div>
    </div>
  </div>

  <!-- Storage Used -->
  <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]">
    <div class="flex items-center gap-4">
      <div class="rounded-xl bg-green-50 p-3 dark:bg-green-900/20">
        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Storage Used</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white/90">
          {{ number_format(($stats['storage_used'] ?? 0) / 1024, 1) }} MB
        </p>
      </div>
    </div>
  </div>

  <!-- This Month -->
  <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]">
    <div class="flex items-center gap-4">
      <div class="rounded-xl bg-blue-50 p-3 dark:bg-blue-900/20">
        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Month</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white/90">{{ $stats['this_month'] ?? 0 }}</p>
      </div>
    </div>
  </div>

  <!-- Recent (7 days) -->
  <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]">
    <div class="flex items-center gap-4">
      <div class="rounded-xl bg-orange-50 p-3 dark:bg-orange-900/20">
        <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Recent (7 days)</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white/90">{{ $stats['recent_images'] ?? 0 }}</p>
      </div>
    </div>
  </div>
</div>