<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Marriage extends Model
{
    // System Status Constants
    const SYSTEM_STATUS_PENDING = 'Pending';        
    const SYSTEM_STATUS_IN_PROGRESS = 'In Progress'; 
    const SYSTEM_STATUS_UNDER_REVIEW = 'Under Review';
    const SYSTEM_STATUS_COMPLETED = 'Completed';   
    const SYSTEM_STATUS_RETURNED = 'Returned';      
    const SYSTEM_STATUS_SKIPPED = 'Skipped';       
    const SYSTEM_STATUS_LINKED = 'Linked';         

    protected $fillable = [
        'pdf_id',
        'pdf_page_id', 
        'image_id', 
        'certificate_serial',
        'license_no',  
        'marriage_type_id',
        'marriage_status_id',
        'verification_status_id',
        'system_status',
        'year', 'month', 
        'county', 
        'sub_county',
        'ward_id',  
        'reg_date', 
        'marriage_date', 
        'venue', 
        'religious_institution',
        'notes',
        'created_by', 'updated_by', 'verified_by',
        'completed_at',          
        'reviewed_at',         
        'returned_at',            
        'return_reason',          
        'has_errors'
    ];

    protected $casts = [
        'reg_date' => 'date',
        'marriage_date' => 'date',
        'year' => 'integer',
        'month' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'returned_at' => 'datetime',
        'has_errors' => 'boolean',
    ];

    // Status helper methods
    public function isPending(): bool
    {
        return $this->system_status === self::SYSTEM_STATUS_PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->system_status === self::SYSTEM_STATUS_IN_PROGRESS;
    }

    public function isUnderReview(): bool
    {
        return $this->system_status === self::SYSTEM_STATUS_UNDER_REVIEW;
    }

    public function isCompleted(): bool
    {
        return $this->system_status === self::SYSTEM_STATUS_COMPLETED;
    }

    public function isReturned(): bool
    {
        return $this->system_status === self::SYSTEM_STATUS_RETURNED;
    }

    public function isSkipped(): bool
    {
        return $this->system_status === self::SYSTEM_STATUS_SKIPPED;
    }

    public function isLinked(): bool
    {
        return $this->system_status === self::SYSTEM_STATUS_LINKED;
    }

    // Status transition methods
    public function markAsInProgress(): void
    {
        $this->system_status = self::SYSTEM_STATUS_IN_PROGRESS;
        $this->save();
        
        // Also update associated PDF page
        if ($this->pdfPage) {
            $this->pdfPage->markAsProcessing();
        }
    }

    public function markAsUnderReview(): void
    {
        $this->system_status = self::SYSTEM_STATUS_UNDER_REVIEW;
        $this->reviewed_at = now();
        $this->save();
        
        // Also update the associated PDF page
        if ($this->pdfPage) {
            $this->pdfPage->markAsReviewNeeded();
        }
    }

    public function markAsCompleted(): void
    {
        $this->system_status = self::SYSTEM_STATUS_COMPLETED;
        $this->completed_at = now();
        $this->save();
        
        // Also update the associated PDF page
        if ($this->pdfPage) {
            $this->pdfPage->markAsCompleted();
        }
    }

    public function markAsReturned(string $reason = null): void
    {
        $this->system_status = self::SYSTEM_STATUS_RETURNED;
        $this->returned_at = now();
        $this->return_reason = $reason;
        $this->save();
        
        // Also update the associated PDF page
        if ($this->pdfPage) {
            $this->pdfPage->markAsSkipped($reason);
        }
    }

    public function markAsSkipped(string $reason = null): void
    {
        $this->system_status = self::SYSTEM_STATUS_SKIPPED;
        $this->notes = $reason;
        $this->save();
        
        // Also update the associated PDF page
        if ($this->pdfPage) {
            $this->pdfPage->markAsSkipped($reason);
        }
    }

    // Get status badge class for UI
    public function getStatusBadgeClass(): string
    {
        return match($this->system_status) {
            self::SYSTEM_STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::SYSTEM_STATUS_UNDER_REVIEW => 'bg-purple-100 text-purple-800',
            self::SYSTEM_STATUS_IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::SYSTEM_STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::SYSTEM_STATUS_RETURNED => 'bg-red-100 text-red-800',
            self::SYSTEM_STATUS_SKIPPED => 'bg-gray-100 text-gray-800',
            self::SYSTEM_STATUS_LINKED => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // Get status icon for UI
    public function getStatusIcon(): string
    {
        return match($this->system_status) {
            self::SYSTEM_STATUS_COMPLETED => 'fa-check-circle text-green-500',
            self::SYSTEM_STATUS_UNDER_REVIEW => 'fa-clock text-purple-500',
            self::SYSTEM_STATUS_IN_PROGRESS => 'fa-spinner text-blue-500 animate-spin',
            self::SYSTEM_STATUS_PENDING => 'fa-hourglass-half text-yellow-500',
            self::SYSTEM_STATUS_RETURNED => 'fa-undo text-red-500',
            self::SYSTEM_STATUS_SKIPPED => 'fa-ban text-gray-500',
            self::SYSTEM_STATUS_LINKED => 'fa-link text-indigo-500',
            default => 'fa-file text-gray-500',
        };
    }

    // Relationships
    public function pdfPage()
    {
        return $this->belongsTo(PdfPage::class, 'pdf_page_id');
    }

    public function pdfUpload()
    {
        return $this->belongsTo(PdfUpload::class, 'pdf_id'); 
    }

    public function pdf()
    {
        return $this->belongsTo(PdfUpload::class, 'pdf_id');
    }

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

    public function clerkManagement()
    {
        return $this->hasOne(ClerkManagement::class, 'data_clerk_id', 'created_by')
            ->where('year', $this->year)
            ->where('month', $this->month);
    }

    public function getFormattedMarriageDateAttribute()
    {
        return $this->marriage_date ? $this->marriage_date->format('M d, Y') : 'N/A';
    }

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

        // Basic marriage info fields
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
        return $this->belongsTo(Spouse::class, 'spouse_type->husband');
    }
    
    public function wife()
    {
        return $this->belongsTo(Spouse::class, 'spouse_type->wife');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->isDirty('marriage_date')) {
                $model->reg_date = $model->marriage_date;
            } elseif ($model->isDirty('reg_date')) {
                $model->marriage_date = $model->reg_date;
            }
            
            if (empty($model->system_status)) {
                $model->system_status = self::SYSTEM_STATUS_PENDING;
            }
        });
    }
}