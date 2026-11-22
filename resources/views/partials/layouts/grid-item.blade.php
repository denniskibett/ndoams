<!-- resources/views/images/partials/grid-item.blade.php -->
<div class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white transition-all duration-200 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800/50">
    <!-- Image -->
    <div class="aspect-[3/4] overflow-hidden bg-gray-100 dark:bg-gray-700">
        @if($image->fileExists())
            <img src="{{ $image->image_url }}" 
                 alt="{{ $image->name }}"
                 class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center bg-red-50 dark:bg-red-900/20">
                <div class="text-center">
                    <svg class="mx-auto h-8 w-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">File Missing</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Image Info -->
    <div class="p-4">
        <h4 class="font-medium text-gray-800 dark:text-white/90 truncate" title="{{ $image->name }}">{{ $image->name }}</h4>
        
        <div class="mt-2 space-y-1">
            @if($image->certificate_serial)
                <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                    Serial: {{ $image->certificate_serial }}
                </p>
            @endif
            
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ $image->fit_type_display }}
                </span>
                <span>{{ $image->formatted_file_size }}</span>
            </div>
            
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>{{ $image->created_at->format('M j, Y') }}</span>
                <span>{{ $image->year }}/{{ str_pad($image->month, 2, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
    </div>

    <!-- Action Overlay -->
    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 transition-all duration-200 group-hover:bg-opacity-30 opacity-0 group-hover:opacity-100">
        <div class="flex gap-2">
            <a href="{{ route('images.show', $image) }}" 
               class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View
            </a>
            <a href="{{ route('images.edit', $image) }}" 
               class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        </div>
    </div>
</div>