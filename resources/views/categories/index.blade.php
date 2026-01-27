@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Categories</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage marriage categories and types</p>
        </div>
        <a href="{{ route('categories.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            Add Category
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">All Categories</h2>
        </div>
        <div class="p-6">
            @if($categories->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-4">Name</th>
                                <th class="text-left py-3 px-4">Type</th>
                                <th class="text-left py-3 px-4">Description</th>
                                <th class="text-left py-3 px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="py-3 px-4">{{ $category->name }}</td>
                                    <td class="py-3 px-4">
                                        <span class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm">
                                            {{ $category->type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">{{ $category->description ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('categories.edit', $category) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $categories->links() }}
                </div>
            @else
                <p class="text-gray-600 dark:text-gray-400">No categories found.</p>
            @endif
        </div>
    </div>
</div>
@endsection