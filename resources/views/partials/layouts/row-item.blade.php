<!-- resources/views/images/partials/row-item.blade.php -->
<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
    <!-- Image Preview -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
            <div class="h-16 w-12 flex-shrink-0 overflow-hidden rounded border border-gray-200 dark:border-gray-600">
                @if($image->fileExists())
                    <img class="h-full w-full object-cover" src="{{ $image->image_url }}" alt="{{ $image->name }}">
                @else
                    <div class="flex h-full items-center justify-center bg-red-50 dark:bg-red-900/20">
                        <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                @endif
            </div>
        </div>
    </td>

    <!-- Details -->
    <td class="px-6 py-4">
        <div class="text-sm font-medium text-gray-900 dark:text-white/90">{{ $image->name }}</div>
        @if($image->certificate_serial)
            <div class="text-sm text-gray-500 dark:text-gray-400">Serial: {{ $image->certificate_serial }}</div>
        @endif
        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $image->filename }}</div>
    </td>

    <!-- File Info -->
    <td class="px-6 py-4">
        <div class="text-sm text-gray-900 dark:text-white/90">
            <span class="inline-flex items-center gap-1">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ $image->fit_type_display }}
            </span>
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $image->formatted_file_size }}</div>
        <div class="text-xs text-gray-400 dark:text-gray-500">{{ $image->year }}/{{ str_pad($image->month, 2, '0', STR_PAD_LEFT) }}</div>
    </td>

    <!-- Date -->
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        {{ $image->created_at->format('M j, Y') }}
    </td>

    <!-- Actions -->
    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
        <div class="flex justify-end gap-2">
            <a href="{{ route('images.show', $image) }}" 
               class="inline-flex items-center gap-1 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-medium text-white shadow-theme-xs hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View
            </a>
            <a href="{{ route('images.edit', $image) }}" 
               class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        </div>
    </td>
</tr>