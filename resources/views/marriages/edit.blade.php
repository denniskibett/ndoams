<!-- resources/views/marriages/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Marriage Record</h1>
            <p class="text-gray-600 dark:text-gray-400">
                Certificate: {{ $marriage->certificate_serial ?? 'Not entered' }}
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('marriages.show', $marriage) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                View Details
            </a>
            <a href="{{ route('marriages.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Image Preview Section -->
        @if($marriage->image)
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Certificate Image</h2>
                
                <div class="aspect-[3/4] bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden mb-4">
                    @if($marriage->image->image_path && Storage::disk('public')->exists($marriage->image->image_path))
                        <img src="{{ Storage::url($marriage->image->image_path) }}" 
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
                        <span class="text-gray-900 dark:text-white">{{ $marriage->image->filename }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Uploaded By:</span>
                        <span class="text-gray-900 dark:text-white">{{ $marriage->image->uploader->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Date:</span>
                        <span class="text-gray-900 dark:text-white">{{ $marriage->image->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Size:</span>
                        <span class="text-gray-900 dark:text-white">{{ $marriage->image->file_size }} KB</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Marriage Form Section -->
        <div class="@if($marriage->image) lg:col-span-2 @else lg:col-span-3 @endif">
            <!-- Current Status Display -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Current Status</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">System Status</label>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($marriage->system_status === 'Completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                            @elseif($marriage->system_status === 'Pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300 @endif">
                            {{ $marriage->system_status }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Verification Status</label>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($marriage->verification_status === 'Verified') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                            @elseif($marriage->verification_status === 'Unverified') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @endif">
                            {{ $marriage->verification_status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit Marriage Information</h2>
                
                <form action="{{ route('marriages.update', $marriage) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Certificate Serial - READ ONLY -->
                        <div class="md:col-span-2">
                            <label for="certificate_serial" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Certificate Serial Number
                            </label>
                            <input type="text" 
                                   value="{{ $marriage->certificate_serial }}"
                                   readonly
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 cursor-not-allowed">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Certificate serial cannot be changed once set
                            </p>
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
                                    <option value="{{ $category->id }}" {{ old('marriage_type_id', $marriage->marriage_type_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Marriage Date -->
                        <div>
                            <label for="marriage_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Marriage Date *
                            </label>
                            <input type="date" 
                                   name="marriage_date" 
                                   id="marriage_date"
                                   value="{{ old('marriage_date', $marriage->marriage_date ? $marriage->marriage_date->format('Y-m-d') : '') }}"
                                   required
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Registration Date -->
                        <div>
                            <label for="reg_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Registration Date
                            </label>
                            <input type="date" 
                                   name="reg_date" 
                                   id="reg_date"
                                   value="{{ old('reg_date', $marriage->reg_date ? $marriage->reg_date->format('Y-m-d') : '') }}"
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
                                   value="{{ old('venue', $marriage->venue) }}"
                                   required
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- County -->
                        <div>
                            <label for="county" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                County *
                            </label>
                            <input type="text" 
                                   name="county" 
                                   id="county"
                                   value="{{ old('county', $marriage->county) }}"
                                   required
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Sub County -->
                        <div>
                            <label for="sub_county" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Sub County
                            </label>
                            <input type="text" 
                                   name="sub_county" 
                                   id="sub_county"
                                   value="{{ old('sub_county', $marriage->sub_county) }}"
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
                                   value="{{ old('constituency', $marriage->constituency) }}"
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
                                   value="{{ old('year', $marriage->year) }}"
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
                                    <option value="{{ $i }}" {{ old('month', $marriage->month) == $i ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <!-- Mark as Completed - For Marriage Tellers -->
                        @if(auth()->user()->role->name === 'marriage_teller' && $marriage->system_status === 'Pending')
                        <div class="md:col-span-2">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="mark_completed" 
                                       id="mark_completed"
                                       value="1"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="mark_completed" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                    Mark as Completed (ready for verification)
                                </label>
                            </div>
                        </div>
                        @endif

                        <!-- Verification Status - For Marriage Registrars and Admin -->
                        @if(in_array(auth()->user()->role->name, ['marriage_registrar', 'admin']))
                        <div class="md:col-span-2 border-t pt-4 mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Verification Control</h3>
                            
                            <div class="space-y-4">
                                <!-- Verification Status -->
                                <div>
                                    <label for="verification_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Verification Status
                                    </label>
                                    <select name="verification_status" 
                                            id="verification_status"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="Unverified" {{ old('verification_status', $marriage->verification_status) == 'Unverified' ? 'selected' : '' }}>Unverified</option>
                                        <option value="Verified" {{ old('verification_status', $marriage->verification_status) == 'Verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="Rejected" {{ old('verification_status', $marriage->verification_status) == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>

                                <!-- Verification Notes -->
                                <div>
                                    <label for="verification_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Verification Notes
                                    </label>
                                    <textarea name="verification_notes" 
                                              id="verification_notes" 
                                              rows="3"
                                              placeholder="Add notes about verification status (optional)..."
                                              class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('verification_notes', $marriage->verification_notes) }}</textarea>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Provide details if rejecting or any special notes about verification
                                    </p>
                                </div>

                                <!-- System Status Override -->
                                <div>
                                    <label for="system_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        System Status (Override)
                                    </label>
                                    <select name="system_status" 
                                            id="system_status"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="Pending" {{ old('system_status', $marriage->system_status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Completed" {{ old('system_status', $marriage->system_status) == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Only change if necessary for administrative purposes
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('marriages.show', $marriage) }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                            Update Marriage Record
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Actions for Registrars -->
            @if(in_array(auth()->user()->role->name, ['marriage_registrar', 'admin']) && $marriage->system_status === 'Completed')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Verification Actions</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Quick Verify -->
                    <form action="{{ route('marriages.update', $marriage) }}" method="POST" class="text-center">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="verification_status" value="Verified">
                        <input type="hidden" name="verification_notes" value="Quick verified by {{ auth()->user()->name }} on {{ now()->format('Y-m-d') }}">
                        <button type="submit" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg transition-colors flex items-center justify-center"
                                onclick="return confirm('Quick verify this marriage record?')">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Quick Verify
                        </button>
                    </form>

                    <!-- Quick Reject -->
                    <form action="{{ route('marriages.update', $marriage) }}" method="POST" class="text-center">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="verification_status" value="Rejected">
                        <button type="button" 
                                onclick="showQuickRejectModal()"
                                class="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Quick Reject
                        </button>
                    </form>

                    <!-- Reset to Unverified -->
                    <form action="{{ route('marriages.update', $marriage) }}" method="POST" class="text-center">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="verification_status" value="Unverified">
                        <input type="hidden" name="verification_notes" value="Reset to unverified by {{ auth()->user()->name }}">
                        <button type="submit" 
                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-3 px-4 rounded-lg transition-colors flex items-center justify-center"
                                onclick="return confirm('Reset verification status to unverified?')">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset Status
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Reject Modal -->
<div id="quickRejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Reject Marriage Record</h3>
            <form action="{{ route('marriages.update', $marriage) }}" method="POST" id="quickRejectForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="verification_status" value="Rejected">
                <div class="mb-4">
                    <label for="quick_rejection_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Rejection Reason (Optional)
                    </label>
                    <textarea name="verification_notes" id="quick_rejection_notes" rows="3" 
                              class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Please provide a reason for rejection..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="hideQuickRejectModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showQuickRejectModal() {
    document.getElementById('quickRejectModal').classList.remove('hidden');
}

function hideQuickRejectModal() {
    document.getElementById('quickRejectModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('quickRejectModal').addEventListener('click', function(e) {
    if (e.target.id === 'quickRejectModal') {
        hideQuickRejectModal();
    }
});
</script>
@endsection