@extends('layouts.app')

@section('content')
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'PDF Uploads Management' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    @include('partials.card.main-cards', ['cardData' => $cardData])<br />

    {{-- @include('partials.table.pdf-uploads-table')<br /> --}}

    {{-- PASS THE DATA HERE --}}
    @include('partials.table.pdf-pages-table', ['pdfPages' => $pdfPages])

@endsection