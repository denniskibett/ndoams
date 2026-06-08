{{-- resources/views/clerk-management/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Clerk Management</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage data clerk assignments and PDF pages</p>
        </div>
    </div>

    <!-- ==================== SECTION 1: ASSIGNED CLERKS TABLE ==================== -->
    <div class="mb-8">
        @include('partials.table.clerks-table', [
            'assignedClerks' => $assignedClerks,
            'availableDataClerks' => $availableDataClerks
        ])
    </div>

    <!-- ==================== SECTION 3: ALL ASSIGNED PAGES ==================== -->
    <div class="mt-8">
        @include('partials.table.pdf-pages-table', ['pdfPages' => $allAssignedPages])
    </div>
</div>
@endsection