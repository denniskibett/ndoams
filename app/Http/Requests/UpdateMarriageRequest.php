<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMarriageRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'certificate_serial' => 'nullable|string',
            'marriage_type_id' => 'nullable|exists:categories,id',
            'marriage_status_id' => 'nullable|exists:categories,id',
            'verification_status_id' => 'nullable|exists:categories,id',
            'year' => 'nullable|integer',
            'month' => 'nullable|integer',
            'county' => 'nullable|string',
            'sub_county' => 'nullable|string',
            'constituency' => 'nullable|string',
            'reg_date' => 'nullable|date',
            'marriage_date' => 'nullable|date',
            'venue' => 'nullable|string',
            'image_main' => 'nullable|image|max:50',
        ];
    }
}