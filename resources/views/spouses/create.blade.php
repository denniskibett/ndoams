@extends('layouts.app')
@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Add Spouse</h1>
    <form action="{{ route('spouses.store') }}" method="POST" enctype="multipart/form-data">
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
            <label class="block">Spouse Type</label>
            <select name="spouse_type" class="border p-2 w-full">
                <option value="husband">Husband</option>
                <option value="wife">Wife</option>
            </select>
        </div>
        <div class="mb-2">
            <label class="block">Name</label>
            <input type="text" name="name" class="border p-2 w-full">
        </div>
        <div class="mb-2">
            <label class="block">Signature Image</label>
            <input type="file" name="signature_image" class="border p-2 w-full">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
    </form>
</div>
@endsection
