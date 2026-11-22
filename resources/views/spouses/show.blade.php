@extends('layouts.app')
@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">{{ ucfirst($spouse->spouse_type) }} - {{ $spouse->name }}</h1>
    <p><strong>ID Type:</strong> {{ $spouse->id_type_id }}</p>
    <p><strong>ID Number:</strong> {{ $spouse->id_number }}</p>
    <p><strong>Residence:</strong> {{ $spouse->residence }}</p>
    <p><strong>County:</strong> {{ $spouse->county }}</p>
    <p><strong>Occupation:</strong> {{ $spouse->occupation }}</p>
    @if($spouse->signature_image)
        <img src="{{ asset('storage/'.$spouse->signature_image) }}" class="w-32 mt-2">
    @endif
</div>
@endsection
