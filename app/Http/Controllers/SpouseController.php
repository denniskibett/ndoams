<?php

namespace App\Http\Controllers;

use App\Models\Spouse;
use App\Http\Requests\StoreSpouseRequest;
use App\Services\FileService;

class SpouseController extends Controller
{
    public function index()
    {
        $spouses = Spouse::with('marriage')->paginate(20);
        return view('spouses.index', compact('spouses'));
    }

    public function create()
    {
        return view('spouses.create');
    }

    public function show(Spouse $spouse)
    {
        $spouse->load('marriage');
        return view('spouses.show', compact('spouse'));
    }

    public function store(StoreSpouseRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        if($request->hasFile('signature_image')){
            $data['signature_image'] = \App\Services\FileService::saveCompressedImage(
                $request->file('signature_image'), 'spouses'
            );
        }

        Spouse::create($data);
        return redirect()->route('spouses.index');
    }

    public function edit(Spouse $spouse)
    {
        return response()->json($spouse);
    }

    public function update(StoreSpouseRequest $request, Spouse $spouse)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        if($request->hasFile('signature_image')){
            $data['signature_image'] = \App\Services\FileService::saveCompressedImage(
                $request->file('signature_image'), 'spouses'
            );
        }

        $spouse->update($data);
        return redirect()->route('spouses.index');
    }

    public function destroy(Spouse $spouse)
    {
        $spouse->delete();
        return redirect()->route('spouses.index');
    }
}