<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpouseRequest extends FormRequest
{
    public function authorize() { return auth()->check(); }

    public function rules()
    {
        return [
            'marriage_id' => 'required|exists:marriages,id',
            'spouse_type' => 'required|in:husband,wife',
            'name' => 'nullable|string',
            'id_type_id' => 'nullable|exists:categories,id',
            'id_number' => 'nullable|string',
            'signature_image' => 'nullable|image|max:50',
            'residence' => 'nullable|string',
            'county' => 'nullable|string',
            'occupation' => 'nullable|string',
            'father_name'=>'nullable|string',
            'father_occupation'=>'nullable|string',
            'father_residence'=>'nullable|string',
            'father_id_type_id'=>'nullable|exists:categories,id',
            'father_id_number'=>'nullable|string',
            'mother_name'=>'nullable|string',
            'mother_occupation'=>'nullable|string',
            'mother_residence'=>'nullable|string',
            'mother_id_type_id'=>'nullable|exists:categories,id',
            'mother_id_number'=>'nullable|string',
            'age'=>'nullable|integer',
        ];
    }
}
