@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Clerk Management</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage data clerk assignments and track progress</p>
        </div>
    </div>

    <!-- Create New Assignment -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create New Assignment</h2>
        <form action="{{ route('clerk-management.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Clerk</label>
                    <select name="data_clerk_id" required class="w-full border border-gray-300 rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select Clerk</option>
                        @foreach($dataClerks as $clerk)
                            <option value="{{ $clerk->id }}">{{ $clerk->name }} ({{ $clerk->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year</label>
                    <select name="year" required class="w-full border border-gray-300 rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Month</label>
                    <select name="month" required class="w-full border border-gray-300 rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}">{{ DateTime::createFromFormat('!m', $month)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target Count</label>
                    <input type="number" name="target_count" required min="1" 
                           class="w-full border border-gray-300 rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           placeholder="e.g., 100">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">
                        Create Assignment
                    </button>
                </div>
            </div>
            <div class="mt-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (Optional)</label>
                <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Any special instructions..."></textarea>
            </div>
        </form>
    </div>

    <!-- Current Assignments -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Current Assignments</h2>
        </div>
        <div class="p-6">
            @if($assignments->count() > 0)
                <div class="space-y-4">
                    @foreach($assignments as $assignment)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900 dark:text-white">
                                        {{ $assignment->dataClerk->name }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ DateTime::createFromFormat('!m', $assignment->month)->format('F') }} {{ $assignment->year }}
                                        • Target: {{ $assignment->target_count }} records
                                        • Completed: {{ $assignment->filled_count }} records
                                    </p>
                                    <div class="mt-2">
                                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                            <div class="bg-blue-600 h-2 rounded-full" 
                                                 style="width: {{ $assignment->progress_percentage > 100 ? 100 : $assignment->progress_percentage }}%">
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                            Progress: {{ $assignment->progress_percentage }}%
                                        </p>
                                    </div>
                                    @if($assignment->notes)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                            <strong>Notes:</strong> {{ $assignment->notes }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex space-x-2 ml-4">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($assignment->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                        @elseif($assignment->status === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 @endif">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                    <a href="{{ route('clerk-management.show', $assignment) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-sm">
                                        View Progress
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600 dark:text-gray-400 text-center py-8">No assignments created yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection