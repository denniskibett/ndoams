@extends('layouts.app')

@section('content')
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'PDF Uploads Management' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    @include('partials.card.pdf-card')<br />

    {{-- @include('partials.table.pdf-uploads-table')<br /> --}}

    @include('partials.table.pdf-pages-table')

@endsection
  