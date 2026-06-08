<?php
// app/Models/PdfUpload.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PdfUpload extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants for PDF UPLOADS (matches database ENUM)
    const STATUS_UPLOADED = 'uploaded';   // pending, not assigned yet
    const STATUS_ASSIGNED = 'assigned';   // some pages assigned to clerks
    const STATUS_COMPLETED = 'completed'; // all pages completed
    const STATUS_PUBLISHED = 'published'; // final published state

    protected $fillable = [
        'uuid',
        'year',
        'month',
        'marriage_type_id',
        'county_code',
        'filename',
        'file_size',
        'name',
        'storage_path',
        'total_pages',
        'status',
        'uploaded_by',
        'file_hash',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'file_size' => 'integer',
        'total_pages' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = Str::uuid()->toString();
            }
            if (!$model->status) {
                $model->status = self::STATUS_UPLOADED;
            }
        });
    }

    // Relationships
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class, 'county_code', 'county_code');
    }

    public function marriageType(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'marriage_type_id')
            ->where('type', 'marriage_type');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(PdfPage::class);
    }

    public function marriages()
    {
        return $this->hasMany(Marriage::class, 'pdf_id');
    }

    public function clerkAssignments(): HasMany
    {
        return $this->hasMany(ClerkManagement::class, 'pdf_upload_id');
    }

    // Update status based on all pages
    public function updateStatusFromPages()
    {
        if ($this->total_pages == 0) {
            return $this;
        }

        $totalPages = $this->total_pages;
        
        // Count pages by status
        $completedPages = $this->pages()
            ->whereIn('status', [
                PdfPage::STATUS_COMPLETED,
                PdfPage::STATUS_REVIEW_NEEDED
            ])->count();
            
        $assignedPages = $this->pages()
            ->where('status', PdfPage::STATUS_ASSIGNED)
            ->count();
            
        $inProgressPages = $this->pages()
            ->where('status', PdfPage::STATUS_IN_PROGRESS)
            ->count();
            
        $pendingPages = $this->pages()
            ->where('status', PdfPage::STATUS_PENDING)
            ->count();
        
        $newStatus = self::STATUS_UPLOADED;
        
        // Determine new status based on page statuses
        if ($completedPages >= $totalPages && $totalPages > 0) {
            $newStatus = self::STATUS_COMPLETED;
        } elseif ($assignedPages > 0 || $inProgressPages > 0) {
            $newStatus = self::STATUS_ASSIGNED;
        } elseif ($pendingPages > 0) {
            $newStatus = self::STATUS_UPLOADED;
        }
        
        // Only update if status has changed
        if ($this->status !== $newStatus) {
            $this->status = $newStatus;
            $this->saveQuietly(); // Use saveQuietly to avoid infinite loops
        }
        
        return $this;
    }
    
    // Check if PDF is available for assignment
    public function isAvailableForAssignment()
    {
        return $this->status === self::STATUS_UPLOADED;
    }
    
    // Scopes for available assignments
    public function scopeAvailableForAssignment($query)
    {
        return $query->where('status', self::STATUS_UPLOADED);
    }
    
    public function scopeFilterByPeriod($query, $year, $month = null)
    {
        $query->where('year', $year);
        
        if ($month) {
            $query->where('month', $month);
        }
        
        return $query;
    }
    
    public function scopeFilterByType($query, $typeId)
    {
        if ($typeId) {
            $query->where('marriage_type_id', $typeId);
        }
        
        return $query;
    }

    // Helper method to get file size in readable format
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        if ($bytes == 0) return '0 B';
        
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function getSizePerPageAttribute(): ?string
    {
        if (empty($this->file_size) || empty($this->total_pages) || $this->total_pages == 0) {
            return null;
        }

        $bytesPerPage = $this->file_size / $this->total_pages;
        
        if ($bytesPerPage >= 1024 * 1024) {
            return round($bytesPerPage / (1024 * 1024), 2) . ' MB';
        } elseif ($bytesPerPage >= 1024) {
            return round($bytesPerPage / 1024, 2) . ' KB';
        }
        
        return round($bytesPerPage, 2) . ' B';
    }

    // Helper methods
    public function completedPagesCount(): int
    {
        return $this->pages()->whereIn('status', [
            PdfPage::STATUS_COMPLETED,
            PdfPage::STATUS_REVIEW_NEEDED
        ])->count();
    }

    public function pendingPagesCount(): int
    {
        return $this->pages()->where('status', PdfPage::STATUS_PENDING)->count();
    }
    
    public function inProgressPagesCount(): int
    {
        return $this->pages()->where('status', PdfPage::STATUS_IN_PROGRESS)->count();
    }
    
    public function assignedPagesCount(): int
    {
        return $this->pages()->where('status', PdfPage::STATUS_ASSIGNED)->count();
    }

    public function completionPercentage(): float
    {
        if ($this->total_pages == 0) return 0;
        return round(($this->completedPagesCount() / $this->total_pages) * 100, 2);
    }

    public function getNextPendingPage()
    {
        return $this->pages()
            ->where('status', PdfPage::STATUS_PENDING)
            ->orderBy('page_number')
            ->first();
    }

    // Accessor for month name
    public function getMonthNameAttribute(): string
    {
        try {
            return \Carbon\Carbon::create()->month($this->month)->format('F');
        } catch (\Exception $e) {
            return 'Month ' . $this->month;
        }
    }
    
    // Get status badge class for UI
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            self::STATUS_UPLOADED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_ASSIGNED => 'bg-blue-100 text-blue-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_PUBLISHED => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function pdfPages()
    {
        return $this->hasMany(PdfPage::class);
    }
}