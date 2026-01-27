<?php
// app/Models/Constituency.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Constituency extends Model
{
    protected $fillable = ['county_id', 'name', 'code'];
    
    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }
}