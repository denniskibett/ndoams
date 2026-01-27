<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name', 'type', 'description', 'created_by', 'updated_by'
    ];

    // You can add relationships if needed
    public function marriages()
    {
        return $this->hasMany(Marriage::class, 'category_id');
    }   

    public function marriageStatuses()
    {
        return $this->hasMany(Marriage::class, 'marriage_status_id');
    }

    public function verificationStatuses()
    {
        return $this->hasMany(Marriage::class, 'verification_status_id');
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