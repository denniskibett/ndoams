@extends('layouts.app')

@section('content')

<div class="p-6">
    <!-- Role-specific welcome message -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                @switch(auth()->user()->role->name)
                    @case('admin') 👑 @break
                    @case('data_clerk') 📝 @break
                    @case('marriage_teller') 👨‍💼 @break
                    @case('marriage_registrar') ✅ @break
                    @default 👤
                @endswitch
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Welcome, {{ auth()->user()->name }} ({{ ucfirst(str_replace('_', ' ', auth()->user()->role->name)) }})
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    @switch(auth()->user()->role->name)
                        @case('admin') You have full access to all marriage records and system management. @break
                        @case('data_clerk') Your role is to upload marriage certificate images and enter basic information quickly. @break
                        @case('marriage_teller') You manage data clerks and complete detailed marriage information. @break
                        @case('marriage_registrar') You verify completed marriage records and ensure data accuracy. @break
                        @default You can view verified marriage records.
                    @endswitch
                </p>
            </div>
        </div>
    </div>

    <!-- Stats card -->
    @include('partials.card.marriages-card', ['stats' => $stats])

    <div class="flex justify-between items-center mb-6">
        
        <div class="flex space-x-3">
              
            @if(auth()->user()->role->name === 'marriage_registrar')
            <a href="{{ route('marriages.show', 0) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
                ✅ Verification Queue
            </a>
            @endif
            
            @if(auth()->user()->role->name === 'marriage_teller')
            <a href="{{ route('marriages.show', 0) }}" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">
                👥 Manage Clerks
            </a>
            @endif
        </div>
    </div>
    
    <!-- Role-specific marriage list -->
    @switch(auth()->user()->role->name)
        @case('data_clerk')
            @include('partials.table.marriages-data-clerk', ['marriages' => $marriages])
        @break
        
        @case('marriage_teller')
            @include('partials.table.marriages-teller', ['marriages' => $marriages])
        @break
        
        @case('marriage_registrar')
            @include('partials.table.marriages-registrar', ['marriages' => $marriages])
        @break
        
        @default
            @include('partials.table.marriages-admin', ['marriages' => $marriages])
    @endswitch
</div>
@endsection