<?php
// app/Models/MarriageTypeExtension.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarriageTypeExtension extends Model
{
    protected $fillable = [
        'marriage_id',
        //civil
        'registrar_officer',
        //muslim
        'mahr_agreed', 'mahr_paid','mahr_deferred','gifts','muslim_officer',
        //christian
        'church_org', 'pastor_name','entry_no',
        //hindu
        'temple','dowry',
        'created_by','updated_by','verified_by'
    ];

    public function marriage(): BelongsTo
    {
        return $this->belongsTo(Marriage::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}