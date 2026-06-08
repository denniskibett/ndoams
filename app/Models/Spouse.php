<?php
// app/Models/Spouse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spouse extends Model
{
    protected $fillable = [
        'marriage_id',
        'spouse_type',
        'name',
        'id_type_id',
        'id_number',
        'signature_image',
        'residence',
        'county',
        'occupation',
        'father_name',
        'father_occupation',
        'father_residence',
        'father_id_type_id',
        'father_id_number',
        'mother_name',
        'mother_occupation',
        'mother_residence',
        'mother_id_type_id',
        'mother_id_number',
        'age',
        'marital_status',
        'created_by',
        'updated_by',
        'verified_by'
    ];

    protected $casts = [
        'age' => 'integer',
        'marital_status' => 'string',
    ];

    public function marriage(): BelongsTo
    {
        return $this->belongsTo(Marriage::class);
    }

    public function idType()
    {
        return $this->belongsTo(Category::class, 'id_type_id');
    }

    public function fatherIdType()
    {
        return $this->belongsTo(Category::class, 'father_id_type_id');
    }

    public function motherIdType()
    {
        return $this->belongsTo(Category::class, 'mother_id_type_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Add helper for marital status display
    public function getFormattedMaritalStatusAttribute()
    {
        $statuses = [
            'Spinster' => 'Spinster',
            'Bachelor' => 'Bachelor', 
            'Widowed' => 'Widowed',
            'Divorced' => 'Divorced',
            'Married' => 'Married'

        ];
        
        return $statuses[$this->marital_status] ?? $this->marital_status;
    }
}