@extends('layouts.app')
@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Edit Witness - {{ $witness->name }}</h1>
    <form action="{{ route('witnesses.update', $witness) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-2">
            <label class="block">Name</label>
            <input type="text" name="name" value="{{ old('name',$witness->name) }}" class="border p-2 w-full">
        </div>
        <div class="mb-2">
            <label class="block">Side</label>
            <select name="spouse_side" class="border p-2 w-full">
                <option value="husband" {{ $witness->spouse_side=='husband'?'selected':'' }}>Husband Side</option>
                <option value="wife" {{ $witness->spouse_side=='wife'?'selected':'' }}>Wife Side</option>
            </select>
        </div>
        <div class="mb-2">
            <label class="block">ID Type</label>
            <select name="id_type_id" class="border p-2 w-full">
                @foreach($categories->where('type','id_type') as $idType)
                    <option value="{{ $idType->id }}" {{ $witness->id_type_id==$idType->id?'selected':'' }}>{{ $idType->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-2">
            <label class="block">ID Number</label>
            <input type="text" name="id_number" value="{{ old('id_number',$witness->id_number) }}" class="border p-2 w-full">
        </div>
        <div class="mb-2">
            <label class="block">Signature Image</label>
            <input type="file" name="signature_image" class="border p-2 w-full">
            @if($witness->signature_image)
                <img src="{{ asset('storage/'.$witness->signature_image) }}" class="w-32 mt-2 border">
            @endif
        </div>
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Update Witness</button>
    </form>
</div>
@endsection
