@extends('layouts.app')
@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Add Witness</h1>
    <form action="{{ route('witnesses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-2">
            <label class="block">Marriage</label>
            <select name="marriage_id" class="border p-2 w-full">
                @foreach($marriages as $marriage)
                    <option value="{{ $marriage->id }}">{{ $marriage->certificate_serial }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-2">
            <label class="block">Side</label>
            <select name="spouse_side" class="border p-2 w-full">
                <option value="husband">Husband Side</option>
                <option value="wife">Wife Side</option>
            </select>
        </div>
        <div class="mb-2">
            <label class="block">Name</label>
            <input type="text" name="name" class="border p-2 w-full">
        </div>
        <div class="mb-2">
            <label class="block">ID Type</label>
            <select name="id_type_id" class="border p-2 w-full">
                @foreach($categories->where('type','id_type') as $idType)
                    <option value="{{ $idType->id }}">{{ $idType->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-2">
            <label class="block">ID Number</label>
            <input type="text" name="id_number" class="border p-2 w-full">
        </div>
        <div class="mb-2">
            <label class="block">Signature Image</label>
            <input type="file" name="signature_image" class="border p-2 w-full">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save Witness</button>
    </form>
</div>
@endsection
