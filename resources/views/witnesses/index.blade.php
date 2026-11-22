@extends('layouts.app')
@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Witnesses</h1>
    <a href="{{ route('witnesses.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">Add Witness</a>
    <table class="w-full table-auto border border-gray-300">
        <thead class="bg-gray-200">
            <tr class="text-center">
                <th class="px-4 py-2 border">Name</th>
                <th class="px-4 py-2 border">Side</th>
                <th class="px-4 py-2 border">Marriage Serial</th>
                <th class="px-4 py-2 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($witnesses as $witness)
            <tr class="text-center">
                <td class="px-4 py-2 border">{{ $witness->name }}</td>
                <td class="px-4 py-2 border">{{ ucfirst($witness->spouse_side) }}</td>
                <td class="px-4 py-2 border">{{ $witness->marriage->certificate_serial ?? '-' }}</td>
                <td class="px-4 py-2 border space-x-2">
                    <a href="{{ route('witnesses.show', $witness) }}" class="text-blue-600">View</a>
                    <a href="{{ route('witnesses.edit', $witness) }}" class="text-green-600">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">
        {{ $witnesses->links() }}
    </div>
</div>
@endsection
