<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'name',
        'filename',
        'certificate_serial',
        'year',
        'month',
        'file_size',
        'fit_type', // Added this
        'uploaded_by',
        'updated_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'file_size' => 'integer',
    ];

    protected $attributes = [
        'fit_type' => 'stretch',
    ];

    protected static function booted()
    {
        static::saving(function ($image) {
            if ($image->isDirty('image_path')) {
                $image->filename = basename($image->image_path);
            }
        });
    }

    public function marriages()
    {
        return $this->hasMany(Marriage::class, 'certificate_serial', 'certificate_serial');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the user who last updated the image
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the full URL of the image
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Check if the physical file exists
     */
    public function fileExists(): bool
    {
        return Storage::disk('public')->exists($this->image_path);
    }

    /**
     * Get formatted file size (in KB)
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if ($this->file_size < 1024) {
            return $this->file_size . ' KB';
        }
        return number_format($this->file_size / 1024, 1) . ' MB';
    }

    /**
     * Get month name
     */
    public function getMonthNameAttribute(): string
    {
        return \DateTime::createFromFormat('!m', $this->month)->format('F');
    }

    /**
     * Get fit type display name
     */
    public function getFitTypeDisplayAttribute(): string
    {
        return match($this->fit_type) {
            'stretch' => 'Stretch to Fill',
            'fit' => 'Fit Within',
            'original' => 'Original Size',
            default => ucfirst($this->fit_type)
        };
    }

    public function marriage()
    {
        return $this->hasOne(Marriage::class);
    }
  
    public function getFullImagePathAttribute()
    {
        // Check if the stored path already has nested folders
        if (str_starts_with($this->image_path, 'images/')) {
            return $this->image_path; // already has full path
        }

        $year = date('Y', strtotime($this->created_at));
        $month = date('m', strtotime($this->created_at));
        return "images/{$year}/{$month}/{$this->image_path}";
    }

}