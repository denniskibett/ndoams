@extends('layouts.app')

@section('content')
    <!-- Hidden element for JavaScript to detect user role and total count -->
    <div id="user-role-data" data-role="{{ $userRole ?? 'user' }}" data-total-count="{{ $totalCount ?? 0 }}" style="display: none;"></div>

    @include('partials.card.main-cards', ['cardData' => $cardData])
    
    @if(in_array($userRole, ['admin', 'marriage_teller']))
        @include('partials.chart.uploads-bar-chart', ['chartData' => $chartData])
    @endif

    {{-- Role-Specific Charts --}}
    @if($userRole === 'data_clerk')
        @include('partials.chart.data-clerk-charts')
    @elseif($userRole === 'marriage_teller')
        @include('partials.chart.marriage-teller-charts')
    @elseif($userRole === 'marriage_registrar')
        @include('partials.chart.marriage-registrar-charts')
    @elseif(in_array($userRole, ['attorney_general', 'ag']))
        @include('partials.chart.attorney-general-charts')
    @elseif($userRole === 'admin')
        @include('partials.chart.admin-charts')
    @endif

    @include('partials.table.pdf-pages-table', [
        'years' => $years,
        'counties' => $counties,
        'statuses' => $statuses ?? [],
        'months' => $months,
        'pdfPages' => $pdfPages,
        'marriageTypes' => $marriageTypes,
        'totalCount' => $totalCount ?? $pdfPages->count(),
    ])
@endsection