@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Spouses</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage marriage spouses</p>
        </div>
        <a href="{{ route('spouses.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            Add Spouse
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6">
            @if($spouses->count() > 0)
                <div class="space-y-4">
                    @foreach($spouses as $spouse)
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ $spouse->name ?? 'N/A' }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Type: {{ ucfirst($spouse->spouse_type) }} | 
                                    Marriage: {{ $spouse->marriage->certificate_serial ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('spouses.show', $spouse) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                <a href="{{ route('spouses.edit', $spouse) }}" class="text-green-600 hover:text-green-900">Edit</a>
                                <form action="{{ route('spouses.destroy', $spouse) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $spouses->links() }}
                </div>
            @else
                <p class="text-gray-600 dark:text-gray-400">No spouses found.</p>
            @endif
        </div>
    </div>
</div>
@endsection