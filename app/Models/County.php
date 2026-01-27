<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class County extends Model
{
    use HasFactory;

    protected $fillable = [
        'county_code',
        'name', 
        'constituency',
        'wards'
    ];

    protected $table = 'counties';

    public function constituencies(): HasMany
    {
        return $this->hasMany(Constituency::class);
    }

    public function pdfUploads()
    {
        return $this->hasMany(PdfUpload::class, 'county_code', 'county_code'); 
        // Adjust columns if your foreign key is different
    }

    public function wards()
{
    $counties = County::orderBy('ward')->get();

    return view('marriages.create-from-pdf', compact('counties'));
}

public function subCounties()
{
    return $this->hasMany(County::class, 'id', 'sub_county');
}

}