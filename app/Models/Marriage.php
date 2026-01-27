<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Marriage extends Model
{
    protected $fillable = [
        'pdf_id',
        'pdf_page_id', 
        'image_id', 
        'certificate_serial',
        'marriage_type_id',
        'marriage_status_id',
        'verification_status_id',
        'system_status',
        'year', 'month', 
        'county', 
        'sub_county', 
        'reg_date', 
        'marriage_date', 
        'venue', 
        'created_by', 'updated_by', 'verified_by',
    ];

    protected $casts = [
        'reg_date' => 'date',
        'marriage_date' => 'date',
        'year' => 'integer',
        'month' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const SYSTEM_STATUS_PENDING = 'Pending';
    const SYSTEM_STATUS_LINKED  = 'Linked';

    // Add to your Marriage model (App\Models\Marriage.php)
    public function pdfPage()
    {
        return $this->belongsTo(PdfPage::class, 'pdf_page_id');
    }

public function pdfUpload()
{
    return $this->belongsTo(PdfUpload::class, 'pdf_id'); 
}

// Optional: Alias for consistency
public function pdf()
{
    return $this->belongsTo(PdfUpload::class, 'pdf_id');
}

// Add accessor to check if it's linked to PDF
public function getIsLinkedToPdfAttribute()
{
    return !is_null($this->pdf_id) || !is_null($this->pdf_page_id);
}
    public function marriageExtension()
    {
        return $this->hasOne(MarriageTypeExtension::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function marriageType(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'marriage_type_id');
    }

    public function marriageStatus(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'marriage_status_id');
    }

    public function verificationStatus(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'verification_status_id');
    }

    // Relationship with clerk management
    public function clerkManagement()
    {
        return $this->hasOne(ClerkManagement::class, 'data_clerk_id', 'created_by')
            ->where('year', $this->year)
            ->where('month', $this->month);
    }

    // Accessor for formatted marriage date
    public function getFormattedMarriageDateAttribute()
    {
        return $this->marriage_date ? $this->marriage_date->format('M d, Y') : 'N/A';
    }

    // Accessor for formatted registration date
    public function getFormattedRegDateAttribute()
    {
        return $this->reg_date ? $this->reg_date->format('M d, Y') : 'N/A';
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function spouses()
    {
        return $this->hasMany(Spouse::class);
    }

    public function witnesses()
    {
        return $this->hasMany(Witness::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    public function calculateCompletionRate()
    {
        $totalFields = 0;
        $completedFields = 0;

        // Basic marriage info fields (4 fields)
        $basicFields = ['certificate_serial', 'marriage_date', 'venue', 'county'];
        $totalFields += count($basicFields);
        foreach ($basicFields as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        // Spouses count (2 required)
        $totalFields += 2;
        $completedFields += min($this->spouses->count(), 2);

        // Witnesses count (2 required)
        $totalFields += 2;
        $completedFields += min($this->witnesses->count(), 2);

        // Calculate percentage
        return $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;
    }

    public function getCompletionStatus()
    {
        $rate = $this->calculateCompletionRate();
        
        if ($rate >= 80) {
            return ['text' => 'High', 'color' => 'text-green-600', 'bg' => 'bg-green-100'];
        } elseif ($rate >= 50) {
            return ['text' => 'Medium', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'];
        } else {
            return ['text' => 'Low', 'color' => 'text-red-600', 'bg' => 'bg-red-100'];
        }
    }


    public function husband()
    {
        return $this->belongsTo(Spouse::class, 'spouse_type->husband'); // Adjust based on your actual structure
    }
    
    public function wife()
    {
        return $this->belongsTo(Spouse::class, 'spouse_type->wife'); // Adjust based on your actual structure
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Sync marriage_date and reg_date
            if ($model->isDirty('marriage_date')) {
                $model->reg_date = $model->marriage_date;
            } elseif ($model->isDirty('reg_date')) {
                $model->marriage_date = $model->reg_date;
            }
            
            // Set default system_status if not provided
            if (empty($model->system_status)) {
                $model->system_status = 'Pending'; // Must match ENUM
            }
            
            // DON'T set verification_status - that column doesn't exist!
            // We only have verification_status_id
        });
    }
            
}