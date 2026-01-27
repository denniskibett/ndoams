<?php

namespace App\Http\Controllers;

use App\Models\Witness;
use Illuminate\Http\Request;

class WitnessController extends Controller
{
    public function index()
    {
        $witnesses = Witness::with('marriage')->paginate(20);
        return view('witnesses.index', compact('witnesses'));
    }

    public function create()
    {
        return view('witnesses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'marriage_id' => 'required|exists:marriages,id',
            'spouse_side' => 'required|in:husband,wife',
            'name' => 'required|string',
            'id_type_id' => 'nullable|exists:categories,id',
            'id_number' => 'nullable|string',
            'signature_image' => 'nullable|image|max:50',
        ]);

        $data['created_by'] = auth()->id();
        
        if($request->hasFile('signature_image')){
            $data['signature_image'] = \App\Services\FileService::saveCompressedImage(
                $request->file('signature_image'), 'witnesses'
            );
        }

        Witness::create($data);
        return redirect()->route('witnesses.index');
    }

    public function show(Witness $witness)
    {
        $witness->load('marriage');
        return view('witnesses.show', compact('witness'));
    }

    public function edit(Witness $witness)
    {
        return response()->json($witness);
    }

    public function update(Request $request, Witness $witness)
    {
        $data = $request->validate([
            'spouse_side' => 'required|in:husband,wife',
            'name' => 'required|string',
            'id_type_id' => 'nullable|exists:categories,id',
            'id_number' => 'nullable|string',
            'signature_image' => 'nullable|image|max:50',
        ]);

        $data['updated_by'] = auth()->id();
        
        if($request->hasFile('signature_image')){
            $data['signature_image'] = \App\Services\FileService::saveCompressedImage(
                $request->file('signature_image'), 'witnesses'
            );
        }

        $witness->update($data);
        return redirect()->route('witnesses.index');
    }

    public function destroy(Witness $witness)
    {
        $witness->delete();
        return redirect()->route('witnesses.index');
    }
}