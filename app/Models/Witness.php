<?php
// app/Models/Witness.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Witness extends Model
{
    protected $fillable = [
        'marriage_id',
        'spouse_side',
        'name',
        'id_type_id',
        'id_number',
        'signature_image',
        'created_by',
        'updated_by',
        'verified_by'
    ];

    public function marriage(): BelongsTo
    {
        return $this->belongsTo(Marriage::class);
    }

    public function idType()
    {
        return $this->belongsTo(Category::class, 'id_type_id');
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
}