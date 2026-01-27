<!-- resources/views/marriages/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                @if(isset($image))
                    Create Marriage from Image
                @else
                    Create New Marriage Record
                @endif
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                @if(isset($image))
                    Linking image to new marriage record
                @else
                    Add a new marriage certificate record
                @endif
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('marriages.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 @if(isset($image)) lg:grid-cols-3 @endif gap-6">
        <!-- Image Preview Section - ONLY when creating from image -->
        @if(isset($image))
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Certificate Image</h2>
                
                <div class="aspect-[3/4] bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden mb-4">
                    @if($image->image_path && Storage::disk('public')->exists($image->image_path))
                        <img src="{{ Storage::url($image->image_path) }}" 
                             alt="Certificate Image" 
                             class="w-full h-full object-contain">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            Image not found
                        </div>
                    @endif
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Filename:</span>
                        <span class="text-gray-900 dark:text-white">{{ $image->filename }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Uploaded By:</span>
                        <span class="text-gray-900 dark:text-white">{{ $image->uploader->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Date:</span>
                        <span class="text-gray-900 dark:text-white">{{ $image->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Size:</span>
                        <span class="text-gray-900 dark:text-white">{{ $image->file_size }} KB</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Marriage Form Section -->
        <div class="@if(isset($image)) lg:col-span-2 @else lg:col-span-3 @endif">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Marriage Information</h2>
                
                <form action="{{ isset($image) ? route('marriages.store-from-image', $image->id) : route('marriages.store') }}" method="POST">
                    @csrf
                    
                    @if(isset($image))
                        <input type="hidden" name="image_id" value="{{ $image->id }}">
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Certificate Serial -->
                        <div>
                            <label for="certificate_serial" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Certificate Serial Number *
                            </label>
                            <input type="text" 
                                   name="certificate_serial" 
                                   id="certificate_serial"
                                   value="{{ old('certificate_serial', $image->certificate_serial ?? '') }}"
                                   required
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('certificate_serial')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Marriage Type -->
                        <div>
                            <label for="marriage_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Marriage Type *
                            </label>
                            <select name="marriage_type_id" 
                                    id="marriage_type_id"
                                    required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Marriage Type</option>
                                @foreach($categories->where('type', 'marriage_type') as $category)
                                    <option value="{{ $category->id }}" {{ old('marriage_type_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('marriage_type_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Marriage Date -->
                        <div>
                            <label for="marriage_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Marriage Date *
                            </label>
                            <input type="date" 
                                   name="marriage_date" 
                                   id="marriage_date"
                                   value="{{ old('marriage_date') }}"
                                   required
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('marriage_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Registration Date -->
                        <div>
                            <label for="reg_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Registration Date
                            </label>
                            <input type="date" 
                                   name="reg_date" 
                                   id="reg_date"
                                   value="{{ old('reg_date') }}"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Venue -->
                        <div class="md:col-span-2">
                            <label for="venue" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Venue *
                            </label>
                            <input type="text" 
                                   name="venue" 
                                   id="venue"
                                   value="{{ old('venue') }}"
                                   required
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('venue')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- County -->
                        <div>
                            <label for="county" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                County *
                            </label>
                            <input type="text" 
                                   name="county" 
                                   id="county"
                                   value="{{ old('county') }}"
                                   required
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('county')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sub County -->
                        <div>
                            <label for="sub_county" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Sub County
                            </label>
                            <input type="text" 
                                   name="sub_county" 
                                   id="sub_county"
                                   value="{{ old('sub_county') }}"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Constituency -->
                        <div>
                            <label for="constituency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Constituency
                            </label>
                            <input type="text" 
                                   name="constituency" 
                                   id="constituency"
                                   value="{{ old('constituency') }}"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Year and Month -->
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Year
                            </label>
                            <input type="number" 
                                   name="year" 
                                   id="year"
                                   value="{{ old('year', date('Y')) }}"
                                   min="2000"
                                   max="2030"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Month
                            </label>
                            <select name="month" 
                                    id="month"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('month', date('n')) == $i ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                            @if(isset($image))
                                Create Marriage Record
                            @else
                                Create New Marriage
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection