@extends('layouts.app')

@php
    $money = fn ($n) => $currency . ' ' . number_format($n);
@endphp

@section('content')
<div class="p-6">
    <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $viewingOther ? $person->name . "'s earnings" : 'My Earnings' }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $entriesPerDay }} entries = 1 day = {{ $money($ratePerDay) }}. Cancelled entries don't count.
            </p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <label for="month" class="text-sm text-gray-700 dark:text-gray-300">Month</label>
            <input type="month" id="month" name="month" value="{{ $month }}" max="{{ now()->format('Y-m') }}"
                   class="border border-gray-300 rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Show</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Earned in {{ $monthLabel }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $money($summary['earnings']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Days completed</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $summary['days'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Entries this month</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['entries']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Towards next day</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $summary['towards_next_day'] }} / {{ $entriesPerDay }}
            </p>
            <div class="mt-2 h-2 bg-gray-200 dark:bg-gray-700 rounded">
                <div class="h-2 bg-blue-600 rounded"
                     style="width: {{ round($summary['towards_next_day'] / $entriesPerDay * 100) }}%"></div>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ $summary['needed_for_next_day'] }} more to earn {{ $money($ratePerDay) }}
            </p>
        </div>
    </div>

    @if($staff)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 overflow-x-auto">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white p-5 pb-0">All staff — {{ $monthLabel }}</h2>
        <table class="min-w-full mt-3 text-sm">
            <thead class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Role</th>
                    <th class="px-5 py-3 text-right">Entries</th>
                    <th class="px-5 py-3 text-right">Days</th>
                    <th class="px-5 py-3 text-right">Earnings</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="text-gray-900 dark:text-white">
                @foreach($staff as $row)
                <tr class="border-b border-gray-100 dark:border-gray-700">
                    <td class="px-5 py-3">{{ $row['user']->name }}</td>
                    <td class="px-5 py-3">{{ str_replace('_', ' ', ucfirst($row['user']->role->name)) }}</td>
                    <td class="px-5 py-3 text-right">{{ number_format($row['summary']['entries']) }}</td>
                    <td class="px-5 py-3 text-right">{{ $row['summary']['days'] }}</td>
                    <td class="px-5 py-3 text-right font-semibold">{{ $money($row['summary']['earnings']) }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('earnings.show', ['user' => $row['user']->id, 'month' => $month]) }}"
                           class="text-blue-600 hover:underline">Details</a>
                    </td>
                </tr>
                @endforeach
                <tr class="font-semibold">
                    <td class="px-5 py-3" colspan="2">Total</td>
                    <td class="px-5 py-3 text-right">{{ number_format($staff->sum(fn ($r) => $r['summary']['entries'])) }}</td>
                    <td class="px-5 py-3 text-right">{{ $staff->sum(fn ($r) => $r['summary']['days']) }}</td>
                    <td class="px-5 py-3 text-right">{{ $money($staff->sum(fn ($r) => $r['summary']['earnings'])) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-x-auto">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white p-5 pb-0">Entries by date</h2>
        @if($daily->isEmpty())
            <p class="p-5 text-gray-500 dark:text-gray-400">No entries in {{ $monthLabel }} yet.</p>
        @else
        <table class="min-w-full mt-3 text-sm">
            <thead class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3 text-right">Entries</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 dark:text-white">
                @foreach($daily as $date => $count)
                <tr class="border-b border-gray-100 dark:border-gray-700">
                    <td class="px-5 py-3">{{ \Carbon\Carbon::parse($date)->format('D, j M Y') }}</td>
                    <td class="px-5 py-3 text-right">{{ $count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
