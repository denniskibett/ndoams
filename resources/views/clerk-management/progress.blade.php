@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Assignment Progress</h1>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $clerkManagement->dataClerk->name }} - 
                {{ DateTime::createFromFormat('!m', $clerkManagement->month)->format('F') }} {{ $clerkManagement->year }}
            </p>
        </div>
        <a href="{{ route('clerk-management.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
            Back to Assignments
        </a>
    </div>

    <!-- Progress Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Target</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $clerkManagement->target_count }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Completed</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $clerkManagement->filled_count }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Progress</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $clerkManagement->progress_percentage }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full 
                    @if($clerkManagement->status === 'active') bg-green-100 dark:bg-green-900
                    @elseif($clerkManagement->status === 'completed') bg-blue-100 dark:bg-blue-900
                    @else bg-yellow-100 dark:bg-yellow-900 @endif">
                    <svg class="w-6 h-6 
                        @if($clerkManagement->status === 'active') text-green-600 dark:text-green-400
                        @elseif($clerkManagement->status === 'completed') text-blue-600 dark:text-blue-400
                        @else text-yellow-600 dark:text-yellow-400 @endif" 
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Status</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white capitalize">{{ $clerkManagement->status }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Overall Progress</h3>
        <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
            <div class="bg-blue-600 h-4 rounded-full transition-all duration-300" 
                 style="width: {{ $clerkManagement->progress_percentage > 100 ? 100 : $clerkManagement->progress_percentage }}%">
            </div>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 text-center">
            {{ $clerkManagement->filled_count }} of {{ $clerkManagement->target_count }} records completed
            ({{ $clerkManagement->progress_percentage }}%)
        </p>
    </div>

    <!-- Records List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Completed Records</h2>
        </div>
        <div class="p-6">
            @if($marriages->count() > 0)
                <div class="space-y-4">
                    @foreach($marriages as $marriage)
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white">
                                    Certificate: {{ $marriage->certificate_serial ?? 'N/A' }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Husband: {{ $marriage->husband_name ?? 'N/A' }} |
                                    Created: {{ $marriage->created_at->format('M d, Y H:i') }}
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($marriage->system_status === 'Completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                    @elseif($marriage->system_status === 'Pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300 @endif">
                                    {{ $marriage->system_status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $marriages->links() }}
                </div>
            @else
                <p class="text-gray-600 dark:text-gray-400 text-center py-8">No records completed yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection