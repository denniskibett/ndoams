@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
        <p class="text-gray-600 dark:text-gray-400">New sign-ups start as "User". Change a role here to give someone access.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif
    @error('role_id')
        <div class="mb-4 rounded bg-red-100 px-4 py-3 text-red-800">{{ $message }}</div>
    @enderror

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">Joined</th>
                    <th class="px-5 py-3">Role</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 dark:text-white">
                @foreach($users as $u)
                <tr class="border-b border-gray-100 dark:border-gray-700">
                    <td class="px-5 py-3">{{ $u->name }}</td>
                    <td class="px-5 py-3">{{ $u->email }}</td>
                    <td class="px-5 py-3">{{ $u->created_at?->format('j M Y') }}</td>
                    <td class="px-5 py-3">
                        <form method="POST" action="{{ route('users.assign-role', $u) }}" class="flex gap-2">
                            @csrf
                            <select name="role_id" class="border border-gray-300 rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600"
                                    @disabled($u->is(auth()->user()))>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}" @selected($u->role_id === $r->id)>{{ str_replace('_', ' ', ucfirst($r->name)) }}</option>
                                @endforeach
                            </select>
                            @unless($u->is(auth()->user()))
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Save</button>
                            @endunless
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
