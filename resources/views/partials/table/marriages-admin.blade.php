<!-- Admin Marriage List -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">All Marriage Records</h2>
    </div>
    <div class="p-6">
        @if($marriages->count() > 0)
            <div class="space-y-4">
                @foreach($marriages as $marriage)
                    <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
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
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('marriages.show', $marriage) }}" 
                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 px-3 py-1 rounded border border-blue-200 dark:border-blue-800">
                                View
                            </a>
                            
                            <a href="{{ route('marriages.edit', $marriage) }}" 
                               class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 px-3 py-1 rounded border border-green-200 dark:border-green-800">
                                Edit
                            </a>
                            
                            <form action="{{ route('marriages.destroy', $marriage) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this marriage record?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 px-3 py-1 rounded border border-red-200 dark:border-red-800">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $marriages->links() }}
            </div>
        @else
            @include('partials.table.empty', ['message' => 'No marriage records found.'])
        @endif
    </div>
</div>