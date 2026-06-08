{{-- resources/views/clerk-management/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <a href="{{ route('clerk-management.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Back to Clerk Management
        </a>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $clerkManagement->dataClerk->name }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">{{ $clerkManagement->dataClerk->email }}</p>
                    
                    <div class="mt-4 flex space-x-4">
                        <div>
                            <span class="text-sm text-gray-500">Status:</span>
                            <span class="ml-2 px-2 py-1 text-xs rounded-full 
                                @if($clerkManagement->status === 'active') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($clerkManagement->status) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Rating:</span>
                            <span class="ml-2 font-medium">
                                @if($clerkManagement->rating)
                                    {{ $clerkManagement->rating }}/5
                                @else
                                    Not rated
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Progress:</span>
                            <span class="ml-2 font-medium">
                                {{ $clerkManagement->progress_percentage }}% 
                                ({{ $clerkManagement->filled_count }}/{{ $clerkManagement->target_count }} pages)
                            </span>
                        </div>
                    </div>
                    
                    @if($clerkManagement->notes)
                        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <strong>Notes:</strong> {{ $clerkManagement->notes }}
                            </p>
                        </div>
                    @endif
                </div>
                
                <div class="flex space-x-2">
                    <form action="{{ route('clerk-management.update', $clerkManagement) }}" method="POST" class="inline">
                        @csrf
                        @method('PUT')
                        <select name="rating" onchange="this.form.submit()" class="border rounded px-2 py-1 text-sm">
                            <option value="">Set Rating</option>
                            <option value="1" {{ $clerkManagement->rating == 1 ? 'selected' : '' }}>⭐ 1 - Poor</option>
                            <option value="2" {{ $clerkManagement->rating == 2 ? 'selected' : '' }}>⭐⭐ 2 - Fair</option>
                            <option value="3" {{ $clerkManagement->rating == 3 ? 'selected' : '' }}>⭐⭐⭐ 3 - Good</option>
                            <option value="4" {{ $clerkManagement->rating == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ 4 - Very Good</option>
                            <option value="5" {{ $clerkManagement->rating == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ 5 - Excellent</option>
                        </select>
                    </form>
                    
                    <form action="{{ route('clerk-management.update', $clerkManagement) }}" method="POST" class="inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="{{ $clerkManagement->status === 'active' ? 'inactive' : 'active' }}">
                        <button type="submit" class="px-3 py-1 text-sm rounded {{ $clerkManagement->status === 'active' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                            {{ $clerkManagement->status === 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Assigned PDF Pages</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-4">All PDF pages assigned to this clerk for data entry</p>
        
        @include('partials.table.pdf-pages-table', ['pdfPages' => $pdfPages])
    </div>
</div>
@endsection