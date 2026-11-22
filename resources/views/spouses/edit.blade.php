@extends('layouts.app')
@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Edit Spouse - {{ $spouse->name }}</h1>
    <form action="{{ route('spouses.update', $spouse) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-2">
            <label class="block">Name</label>
            <input type="text" name="name" value="{{ old('name',$spouse->name) }}" class="border p-2 w-full">
        </div>
        <div class="mb-2">
            <label class="block">Signature Image</label>
            <input type="file" name="signature_image" class="border p-2 w-full">
            @if($spouse->signature_image)
                <img src="{{ asset('storage/'.$spouse->signature_image) }}" class="w-32 mt-2">
            @endif
        </div>
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Update</button>
    </form>
</div>
@endsection
