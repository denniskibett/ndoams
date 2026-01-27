<!-- Marriage Teller Marriage List -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Team Marriage Records</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage and complete marriage details for your team</p>
    </div>
    <div class="p-6">
        @if($marriages->count() > 0)
            <div class="space-y-4">
                @foreach($marriages as $marriage)
                    <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex-1">
                            <div class="flex items-start gap-4">
                                <!-- Image Thumbnail -->
                                @if($marriage->image_main)
                                    <div class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                        <img src="{{ asset('storage/' . $marriage->image_main) }}" 
                                             alt="Marriage certificate" 
                                             class="w-full h-full object-cover cursor-pointer"
                                             onclick="openImageModal('{{ asset('storage/' . $marriage->image_main) }}')">
                                    </div>
                                @else
                                    <div class="flex-shrink-0 w-16 h-16 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif

                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900 dark:text-white">
                                        @if($marriage->certificate_serial)
                                            Certificate: {{ $marriage->certificate_serial }}
                                        @else
                                            <span class="text-gray-400">Certificate: Not Entered Yet</span>
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Date: 
                                        @if($marriage->marriage_date)
                                            {{ \Carbon\Carbon::parse($marriage->marriage_date)->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">Date not set</span>
                                        @endif
                                        | 
                                        Spouses: {{ $marriage->spouses->count() }} |
                                        Witnesses: {{ $marriage->witnesses->count() }} |
                                        Created by: {{ $marriage->createdBy->name ?? 'System' }}
                                    </p>
                                    
                                    <!-- Completion Progress -->
                                    <div class="mt-2">
                                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            <span>Completion</span>
                                            <span>{{ $marriage->completion_percentage ?? 0 }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                                 style="width: {{ $marriage->completion_percentage ?? 0 }}%"></div>
                                        </div>
                                    </div>

                                    <div class="flex space-x-2 mt-2">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($marriage->system_status === 'Completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                            @elseif($marriage->system_status === 'Pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                            @elseif($marriage->system_status === 'Approved') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300 @endif">
                                            {{ $marriage->system_status }}
                                        </span>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($marriage->verification_status === 'Verified') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                            @elseif($marriage->verification_status === 'Unverified') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @endif">
                                            {{ $marriage->verification_status }}
                                        </span>
                                        @if($marriage->updated_at != $marriage->created_at)
                                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                                                Updated
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <a href="{{ route('marriages.show', $marriage) }}" 
                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 px-3 py-2 rounded border border-blue-200 dark:border-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900 transition-colors">
                                View Details
                            </a>
                            
                            @if($marriage->system_status === 'Pending')
                            <a href="{{ route('marriages.edit', $marriage) }}" 
                               class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300 px-3 py-2 rounded border border-orange-200 dark:border-orange-800 hover:bg-orange-50 dark:hover:bg-orange-900 transition-colors">
                                Complete Details
                            </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $marriages->links() }}
            </div>
        @else
            @include('partials.table.empty', ['message' => 'No marriage records found for your team.'])
        @endif
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="max-w-4xl max-h-full p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Marriage Certificate Image</h3>
                <button onclick="closeImageModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <img id="modalImage" src="" alt="Marriage certificate" class="max-w-full max-h-96 object-contain">
        </div>
    </div>
</div>

<script>
function openImageModal(imageUrl) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageModal();
    }
});

// Close modal on background click
document.getElementById('imageModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeImageModal();
    }
});
</script>