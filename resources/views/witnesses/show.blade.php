@extends('layouts.app')
@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Witness Details - {{ $witness->name }}</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p><strong>Side:</strong> {{ ucfirst($witness->spouse_side) }}</p>
            <p><strong>ID Type:</strong> {{ $witness->id_type_id }}</p>
            <p><strong>ID Number:</strong> {{ $witness->id_number }}</p>
            <p><strong>Marriage Serial:</strong> {{ $witness->marriage->certificate_serial ?? '-' }}</p>
            <p><strong>Created By:</strong> {{ $witness->created_by }}</p>
            <p><strong>Updated By:</strong> {{ $witness->updated_by }}</p>
        </div>
        <div>
            @if($witness->signature_image)
                <img src="{{ asset('storage/'.$witness->signature_image) }}" class="w-32 border">
            @endif
        </div>
    </div>
</div>
@endsection
