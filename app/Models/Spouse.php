<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spouse extends Model
{
    protected $fillable = [
        'marriage_id','spouse_type','name','signature_image',
        'residence','county','occupation',
        'father_name','father_occupation','father_residence','father_id_type_id','father_id_number',
        'mother_name','mother_occupation','mother_residence','mother_id_type_id','mother_id_number',
        'age','created_by','updated_by','verified_by'
    ];

    public function marriage(): BelongsTo
    {
        return $this->belongsTo(Marriage::class);
    }
}
