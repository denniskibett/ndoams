<?php

namespace App\Http\Controllers;

use App\Models\MarriageTypeExtension;
use Illuminate\Http\Request;

class MarriageTypeExtensionController extends Controller
{
    public function index()
    {
        $extensions = MarriageTypeExtension::with('marriage')->paginate(20);
        return view('marriage-extensions.index', compact('extensions'));
    }

    public function create()
    {
        return view('marriage-extensions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'marriage_id' => 'required|exists:marriages,id',
            'mahr_agreed' => 'nullable|string',
            'mahr_paid' => 'nullable|string',
            'mahr_deferred' => 'nullable|string',
            'gifts' => 'nullable|string',
            'muslim_officer' => 'nullable|string',
            'church_org' => 'nullable|string',
            'pastor_name' => 'nullable|string',
            'entry_no' => 'nullable|string',
            'temple' => 'nullable|string',
            'dowry' => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        $extension = MarriageTypeExtension::create($data);
        return redirect()->route('marriage-extensions.index');
    }

    public function show(MarriageTypeExtension $marriageExtension)
    {
        $marriageExtension->load('marriage');
        return view('marriage-extensions.show', compact('marriageExtension'));
    }

    public function edit(MarriageTypeExtension $marriageExtension)
    {
        return view('marriage-extensions.edit', compact('marriageExtension'));
    }

    public function update(Request $request, MarriageTypeExtension $marriageExtension)
    {
        $data = $request->validate([
            'mahr_agreed' => 'nullable|string',
            'mahr_paid' => 'nullable|string',
            'mahr_deferred' => 'nullable|string',
            'gifts' => 'nullable|string',
            'muslim_officer' => 'nullable|string',
            'church_org' => 'nullable|string',
            'pastor_name' => 'nullable|string',
            'entry_no' => 'nullable|string',
            'temple' => 'nullable|string',
            'dowry' => 'nullable|string',
        ]);

        $data['updated_by'] = auth()->id();
        $marriageExtension->update($data);
        return redirect()->route('marriage-extensions.index');
    }

    public function destroy(MarriageTypeExtension $marriageExtension)
    {
        $marriageExtension->delete();
        return redirect()->route('marriage-extensions.index');
    }
}