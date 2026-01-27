<!-- resources/views/marriages/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'Marriage Record: {{ $marriage->certificate_serial ?? "No Serial" }}' }">
        @include('partials.breadcrumb')
    </div>
    
    <!-- Page Header with Buttons Inside Top Card -->
    <div class="mb-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Marriage Certificate Details</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        @if($marriage->certificate_serial)
                            Certificate Serial: <span class="font-mono font-medium text-gray-900 dark:text-white">{{ $marriage->certificate_serial }}</span>
                        @else
                            <span class="text-red-500 dark:text-red-400">Certificate Serial Not Entered</span>
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('marriages.index') }}" 
                       class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                        Back to List
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                        </svg>
                    </a>
                    
                    <!-- Edit Button - Conditional -->
                    @if(auth()->user()->role->name === 'admin' || 
                        auth()->user()->role->name === 'marriage_registrar' ||
                        (auth()->user()->role->name === 'marriage_teller' && $marriage->system_status == 'Pending'))
                        <a href="{{ route('marriages.edit', $marriage) }}" 
                           class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs ring-1 ring-blue-200 transition hover:bg-blue-600 dark:ring-blue-500/20">
                            Edit Record
                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Status and Completion Overview -->
            <div class="mt-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-4">
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium
                            @if($marriage->system_status === 'Completed') bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/15 dark:text-green-400 dark:ring-green-500/20
                            @elseif($marriage->system_status === 'Pending') bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/15 dark:text-yellow-400 dark:ring-yellow-500/20
                            @elseif($marriage->system_status === 'Approved') bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/15 dark:text-blue-400 dark:ring-blue-500/20
                            @else bg-gray-50 text-gray-700 ring-gray-300 dark:bg-gray-500/15 dark:text-gray-400 dark:ring-gray-500/20 @endif">
                            System: {{ $marriage->system_status }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium
                            @if($marriage->verification_status === 'Verified') bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/15 dark:text-green-400 dark:ring-green-500/20
                            @elseif($marriage->verification_status === 'Unverified') bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/15 dark:text-yellow-400 dark:ring-yellow-500/20
                            @else bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/15 dark:text-red-400 dark:ring-red-500/20 @endif">
                            Verification: {{ $marriage->verification_status }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                                Created by {{ $marriage->createdBy->name ?? 'System' }}
                            </span>
                            @if($marriage->verified_by)
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Verified by {{ $marriage->verifiedBy->name ?? 'System' }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Completion Progress -->
                <div class="mb-4">
                    <div class="mb-2 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Data Completion Progress</h3>
                        <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ $completionRate ?? 0 }}%</span>
                    </div>
                    <div class="mb-4">
                        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                            <div class="h-2 rounded-full bg-blue-500 transition-all duration-500" style="width: {{ $completionRate ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div class="text-center">
                            <div class="text-xs font-semibold {{ $marriage->certificate_serial ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                Certificate
                            </div>
                            <div class="text-lg font-bold {{ $marriage->certificate_serial ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $marriage->certificate_serial ? '✓' : '✗' }}
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-semibold {{ $marriage->marriage_date ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                Date
                            </div>
                            <div class="text-lg font-bold {{ $marriage->marriage_date ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $marriage->marriage_date ? '✓' : '✗' }}
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-semibold {{ $marriage->spouses->count() >= 2 ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                                Spouses
                            </div>
                            <div class="text-lg font-bold {{ $marriage->spouses->count() >= 2 ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                                {{ $marriage->spouses->count() }}/2
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-semibold {{ $marriage->witnesses->count() >= 2 ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                                Witnesses
                            </div>
                            <div class="text-lg font-bold {{ $marriage->witnesses->count() >= 2 ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                                {{ $marriage->witnesses->count() }}/2
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registrar Verification Section -->
                @if(in_array(auth()->user()->role->name, ['marriage_registrar', 'admin']) && $marriage->system_status === 'Completed')
                <div class="border-t border-gray-200 pt-6 dark:border-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Verification Action</h3>
                    
                    @if($marriage->verification_status === 'Unverified')
                    <div class="rounded-xl border border-yellow-200 bg-gradient-to-br from-yellow-50 to-white p-5 dark:border-yellow-800 dark:from-yellow-900/20 dark:to-gray-900">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h4 class="mb-2 font-medium text-yellow-800 dark:text-yellow-300">Pending Verification</h4>
                                <p class="text-sm text-yellow-700 dark:text-yellow-400">
                                    This marriage record is completed and ready for verification.
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <form action="{{ route('marriages.update', $marriage) }}" method="POST" class="flex-1 sm:flex-none">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="verification_status" value="Verified">
                                    <input type="hidden" name="verification_notes" value="Verified by {{ auth()->user()->name }} on {{ now()->format('Y-m-d') }}">
                                    <button type="submit" 
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-green-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs ring-1 ring-green-200 transition hover:bg-green-600 dark:ring-green-500/20"
                                            onclick="return confirm('Are you sure you want to verify this marriage record? This action cannot be undone.')">
                                        Verify Record
                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                                        </svg>
                                    </button>
                                </form>
                                
                                <button type="button" 
                                        @click="showRejectionModal = true"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs ring-1 ring-red-200 transition hover:bg-red-600 dark:ring-red-500/20 sm:w-auto">
                                    Reject Record
                                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @elseif($marriage->verification_status === 'Verified')
                    <div class="rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-white p-5 dark:border-green-800 dark:from-green-900/20 dark:to-gray-900">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="mb-2 flex items-center gap-2">
                                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <h4 class="font-medium text-green-800 dark:text-green-300">✓ Verified Record</h4>
                                </div>
                                <p class="text-sm text-green-700 dark:text-green-400">
                                    @if($marriage->verified_by)
                                        Verified by {{ $marriage->verifiedBy->name ?? 'System' }} on {{ $marriage->updated_at->format('M d, Y') }}
                                    @else
                                        This marriage record has been verified and approved.
                                    @endif
                                </p>
                            </div>
                            <form action="{{ route('marriages.update', $marriage) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="verification_status" value="Unverified">
                                <button type="submit" 
                                        class="inline-flex items-center gap-2 rounded-lg bg-yellow-50 px-4 py-2.5 text-sm font-medium text-yellow-700 shadow-theme-xs ring-1 ring-yellow-200 transition hover:bg-yellow-100 dark:bg-yellow-500/15 dark:text-yellow-400 dark:ring-yellow-500/20"
                                        onclick="return confirm('Are you sure you want to revert this record to unverified status?')">
                                    Revert to Unverified
                                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @elseif($marriage->verification_status === 'Rejected')
                    <div class="rounded-xl border border-red-200 bg-gradient-to-br from-red-50 to-white p-5 dark:border-red-800 dark:from-red-900/20 dark:to-gray-900">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="mb-2 flex items-center gap-2">
                                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <h4 class="font-medium text-red-800 dark:text-red-300">✗ Rejected Record</h4>
                                </div>
                                <p class="text-sm text-red-700 dark:text-red-400">
                                    @if($marriage->verified_by)
                                        Rejected by {{ $marriage->verifiedBy->name ?? 'System' }} on {{ $marriage->updated_at->format('M d, Y') }}
                                    @else
                                        This marriage record has been rejected and requires correction.
                                    @endif
                                </p>
                                @if($marriage->verification_notes)
                                <div class="mt-3 rounded-lg bg-red-50/50 p-3 dark:bg-red-900/20">
                                    <p class="text-sm font-medium text-red-800 dark:text-red-300">Notes:</p>
                                    <p class="text-sm text-red-700 dark:text-red-400">{{ $marriage->verification_notes }}</p>
                                </div>
                                @endif
                            </div>
                            <form action="{{ route('marriages.update', $marriage) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="verification_status" value="Unverified">
                                <button type="submit" 
                                        class="inline-flex items-center gap-2 rounded-lg bg-yellow-50 px-4 py-2.5 text-sm font-medium text-yellow-700 shadow-theme-xs ring-1 ring-yellow-200 transition hover:bg-yellow-100 dark:bg-yellow-500/15 dark:text-yellow-400 dark:ring-yellow-500/20"
                                        onclick="return confirm('Are you sure you want to mark this record as unverified?')">
                                    Mark as Unverified
                                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content: 50/50 Split -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Certificate Document Viewer (Left Side - 50%) -->
        <div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Certificate Document</h2>
                    @if($marriage->pdf_id || $marriage->image_id)
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-blue-200 dark:bg-blue-500/15 dark:text-blue-400 dark:ring-blue-500/20">
                            @if($marriage->pdf_id)
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                PDF Document
                            @elseif($marriage->image_id)
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Image Document
                            @endif
                        </span>
                    @endif
                </div>
                
                <!-- Document Viewer -->
                @if($marriage->pdf_id)
                    <!-- PDF Viewer -->
                    <div class="relative mb-4 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900" style="min-height: 400px; max-height: 500px;">
                        @if($marriage->pdfUpload)
                            <!-- Use the main PDF uploads show route instead of page route -->
                             <div class="h-screen w-full overflow-hidden">
            <iframe 
                src="{{ route('pdf-uploads.show', $marriage->pdfPage->id) }}"
                class="w-full h-full border-0"
                title="Marriage Certificate PDF"
                id="pdf-viewer">
            </iframe>
        </div>
   
                        @else
                            <div class="flex h-full items-center justify-center p-8">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">PDF document not available</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    @if($marriage->pdfUpload)
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800">
                                    <svg class="h-5 w-5 text-blue-700 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $marriage->pdfUpload->file_name ?? 'PDF Document' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $marriage->pdfUpload->total_pages ?? '?' }} pages total</p>
                                </div>
                            </div>
                            
                            <a href="{{ route('pdf-uploads.show', $marriage->pdfUpload->id) }}" 
                               target="_blank"
                               class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-50 px-4 py-2.5 text-sm font-medium text-blue-700 shadow-theme-xs ring-1 ring-blue-200 transition hover:bg-blue-100 dark:bg-blue-500/15 dark:text-blue-400 dark:ring-blue-500/20">
                                Open Full PDF
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                                </svg>
                            </a>
                        </div>
                    @endif
                @elseif($marriage->image_id)
                    <!-- Image Viewer -->
                    <div>
                        <div class="relative mb-4 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900" style="min-height: 400px; max-height: 500px;">
                            @if($marriage->image && $marriage->image->image_path)
                                <img src="{{ Storage::url($marriage->image->image_path) }}" 
                                     alt="Marriage Certificate" 
                                     class="h-full w-full object-contain"
                                     id="image-viewer"
                                     style="max-height: 500px;">
                            @else
                                <div class="flex h-full items-center justify-center">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Image not available</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        @if($marriage->image)
                            <div class="flex gap-3">
                                <button @click="zoomImage()"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                                    Zoom
                                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                                    </svg>
                                </button>
                                <a href="{{ Storage::url($marriage->image->image_path) }}" 
                                   target="_blank"
                                   class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-blue-50 px-4 py-2.5 text-sm font-medium text-blue-700 shadow-theme-xs ring-1 ring-blue-200 transition hover:bg-blue-100 dark:bg-blue-500/15 dark:text-blue-400 dark:ring-blue-500/20">
                                    Download
                                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- No Document Available -->
                    <div class="flex h-96 flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 dark:border-gray-700 dark:bg-gray-900">
                        <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-4 text-sm font-medium text-gray-900 dark:text-white">No Document Available</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This marriage record has no attached document</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Marriage Information Cards (50%) -->
        <div class="space-y-6">
            <!-- Marriage Information Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Marriage Information</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">Certificate Serial</label>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $marriage->certificate_serial ?? 'Not entered' }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">Marriage Date</label>
                            <p class="text-gray-900 dark:text-white">
                                @if($marriage->marriage_date)
                                    {{ \Carbon\Carbon::parse($marriage->marriage_date)->format('M d, Y') }}
                                @else
                                    <span class="text-red-500 dark:text-red-400">Not set</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">Registration Date</label>
                            <p class="text-gray-900 dark:text-white">
                                @if($marriage->reg_date)
                                    {{ \Carbon\Carbon::parse($marriage->reg_date)->format('M d, Y') }}
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">Venue</label>
                            <p class="text-gray-900 dark:text-white">{{ $marriage->venue ?? 'Not entered' }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">Location</label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $marriage->county ?? '' }} 
                            {{ $marriage->sub_county ? ', ' . $marriage->sub_county : '' }} 
                            {{ $marriage->constituency ? ', ' . $marriage->constituency : '' }}
                            @if(!$marriage->county && !$marriage->sub_county && !$marriage->constituency)
                                <span class="text-red-500 dark:text-red-400">Not entered</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">Officiating Minister / Registrar</label>
                        <p class="text-gray-900 dark:text-white">{{ $marriage->minister_name ?? 'Not entered' }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">Religious Denomination</label>
                        <p class="text-gray-900 dark:text-white">{{ $marriage->religious_denomination ?? 'Not entered' }}</p>
                    </div>
                </div>
            </div>

            <!-- Spouses Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Spouses</h2>
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-blue-200 dark:bg-blue-500/15 dark:text-blue-400 dark:ring-blue-500/20">
                        {{ $marriage->spouses->count() }} / 2
                    </span>
                </div>
                
                @if($marriage->spouses->count() > 0)
                    <div class="space-y-4">
                        @foreach($marriage->spouses as $spouse)
                            <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-5 transition-colors hover:border-gray-300 dark:border-gray-800 dark:from-gray-900/20 dark:to-gray-900 dark:hover:border-gray-700">
                                <div class="mb-3 flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800">
                                        <span class="text-lg font-bold text-blue-700 dark:text-blue-300">
                                            {{ strtoupper(substr($spouse->spouse_type ?? 'S', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold capitalize text-gray-900 dark:text-white">
                                            {{ $spouse->spouse_type }}: {{ $spouse->name ?? 'Name not entered' }}
                                        </h3>
                                        @if($spouse->age)
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Age: {{ $spouse->age }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="space-y-2 text-sm">
                                    @if($spouse->occupation)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-500 dark:text-gray-400">Occupation:</span>
                                            <span class="text-gray-900 dark:text-white">{{ $spouse->occupation }}</span>
                                        </div>
                                    @endif
                                    @if($spouse->residence)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-500 dark:text-gray-400">Residence:</span>
                                            <span class="text-gray-900 dark:text-white">{{ $spouse->residence }}</span>
                                        </div>
                                    @endif
                                    @if($spouse->id_number)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-500 dark:text-gray-400">ID Number:</span>
                                            <span class="font-mono text-gray-900 dark:text-white">{{ $spouse->id_number }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($spouse->father_name || $spouse->mother_name)
                                        <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Parents</p>
                                            @if($spouse->father_name)
                                                <p class="text-sm text-gray-900 dark:text-white">Father: {{ $spouse->father_name }}</p>
                                            @endif
                                            @if($spouse->mother_name)
                                                <p class="text-sm text-gray-900 dark:text-white">Mother: {{ $spouse->mother_name }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 dark:border-gray-700 dark:bg-gray-900">
                        <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 2.5L21 21"/>
                        </svg>
                        <p class="mt-4 text-sm font-medium text-gray-900 dark:text-white">No Spouses Added</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This marriage record has no spouse information</p>
                    </div>
                @endif
            </div>

            <!-- Witnesses Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Witnesses</h2>
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-blue-200 dark:bg-blue-500/15 dark:text-blue-400 dark:ring-blue-500/20">
                        {{ $marriage->witnesses->count() }} / 2
                    </span>
                </div>
                
                @if($marriage->witnesses->count() > 0)
                    <div class="space-y-4">
                        @foreach($marriage->witnesses as $witness)
                            <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-5 transition-colors hover:border-gray-300 dark:border-gray-800 dark:from-gray-900/20 dark:to-gray-900 dark:hover:border-gray-700">
                                <div class="mb-3 flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800">
                                        <svg class="h-5 w-5 text-green-700 dark:text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">
                                            {{ $witness->name ?? 'Name not entered' }}
                                        </h3>
                                        @if($witness->relationship)
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $witness->relationship }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="space-y-2 text-sm">
                                    @if($witness->occupation)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-500 dark:text-gray-400">Occupation:</span>
                                            <span class="text-gray-900 dark:text-white">{{ $witness->occupation }}</span>
                                        </div>
                                    @endif
                                    @if($witness->residence)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-500 dark:text-gray-400">Residence:</span>
                                            <span class="text-gray-900 dark:text-white">{{ $witness->residence }}</span>
                                        </div>
                                    @endif
                                    @if($witness->id_number)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-500 dark:text-gray-400">ID Number:</span>
                                            <span class="font-mono text-gray-900 dark:text-white">{{ $witness->id_number }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($witness->age)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-500 dark:text-gray-400">Age:</span>
                                            <span class="text-gray-900 dark:text-white">{{ $witness->age }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 dark:border-gray-700 dark:bg-gray-900">
                        <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="mt-4 text-sm font-medium text-gray-900 dark:text-white">No Witnesses Added</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This marriage record has no witness information</p>
                    </div>
                @endif
            </div>

            <!-- Timeline & Audit Log Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Record Timeline</h2>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                            <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">Record Created</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Created by {{ $marriage->createdBy->name ?? 'System' }} on {{ $marriage->created_at->format('M d, Y \a\t h:i A') }}
                            </p>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $marriage->created_at->diffForHumans() }}</span>
                    </div>
                    
                    @if($marriage->updated_at && $marriage->updated_at->notEqualTo($marriage->created_at))
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Last Updated</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($marriage->updatedBy)
                                        Updated by {{ $marriage->updatedBy->name }} on {{ $marriage->updated_at->format('M d, Y \a\t h:i A') }}
                                    @else
                                        Updated on {{ $marriage->updated_at->format('M d, Y \a\t h:i A') }}
                                    @endif
                                </p>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $marriage->updated_at->diffForHumans() }}</span>
                        </div>
                    @endif
                    
                    @if($marriage->verified_by)
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $marriage->verification_status === 'Verified' ? 'Record Verified' : 'Record Rejected' }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $marriage->verification_status === 'Verified' ? 'Verified' : 'Rejected' }} by {{ $marriage->verifiedBy->name ?? 'System' }} on {{ $marriage->updated_at->format('M d, Y \a\t h:i A') }}
                                </p>
                                @if($marriage->verification_notes && $marriage->verification_status === 'Rejected')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $marriage->verification_notes }}</p>
                                @endif
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $marriage->updated_at->diffForHumans() }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Rejection Notes -->
<div x-data="{ showRejectionModal: false }">
    <!-- Modal Backdrop -->
    <div x-show="showRejectionModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
         @click="showRejectionModal = false">
        
        <!-- Modal Content -->
        <div x-show="showRejectionModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop
             class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900">
            
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reject Marriage Record</h3>
                <button @click="showRejectionModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                Please provide a reason for rejecting this marriage record. This will help the teller understand what needs to be corrected.
            </p>
            
            <form action="{{ route('marriages.update', $marriage) }}" method="POST" id="rejectionForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="verification_status" value="Rejected">
                
                <div class="mb-4">
                    <label for="rejectionNotes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Rejection Notes</label>
                    <textarea 
                        id="rejectionNotes" 
                        name="verification_notes" 
                        rows="4" 
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-theme-xs ring-1 ring-gray-300 transition placeholder:text-gray-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:ring-gray-700 dark:placeholder:text-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500/20"
                        placeholder="Enter specific reasons for rejection..."
                        required></textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Explain what information is missing or incorrect that needs to be fixed.
                    </p>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" 
                            @click="showRejectionModal = false"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                        Cancel
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                        </svg>
                    </button>
                    <button type="submit" 
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-red-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs ring-1 ring-red-200 transition hover:bg-red-600 dark:ring-red-500/20"
                            onclick="return confirm('Are you sure you want to reject this marriage record?')">
                        Reject Record
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Alpine.js for zoom functionality -->
<script>
    function zoomImage() {
        const image = document.getElementById('image-viewer');
        if (image) {
            if (image.style.transform === 'scale(2)') {
                image.style.transform = 'scale(1)';
                image.style.cursor = 'default';
            } else {
                image.style.transform = 'scale(2)';
                image.style.cursor = 'zoom-out';
            }
        }
    }
</script>
@endsection