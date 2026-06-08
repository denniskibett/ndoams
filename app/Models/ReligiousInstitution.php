<?php
// app/Models/ReligiousInstitution.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReligiousInstitution extends Model
{
    protected $fillable = [
        'registered_name',
        'registration_date',
        'registration_number',
        'registration_type',
        'religion',
        'religion_type',
        'ward_id'
    ];

    protected $casts = [
        'registration_date' => 'date'
    ];

    public function ward()
    {
        return $this->belongsTo(County::class, 'ward_id');
    }

    public function scopeByReligion($query, $religion)
    {
        return $query->where('religion', $religion);
    }

    public function scopeByReligionType($query, $type)
    {
        return $query->where('religion_type', $type);
    }
}