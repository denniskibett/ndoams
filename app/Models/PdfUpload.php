<?php

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
            $model->uuid = Str::uuid()->toString();
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFilterByPeriod($query, $year, $month = null)
    {
        $query->where('year', $year);
        
        if ($month) {
            $query->where('month', $month);
        }
        
        return $query;
    }

    // Helper methods
    public function completedPagesCount(): int
    {
        return $this->pages()->where('status', 'completed')->count();
    }

    public function pendingPagesCount(): int
    {
        return $this->pages()->where('status', 'pending')->count();
    }

    public function completionPercentage(): float
    {
        if ($this->total_pages == 0) return 0;
        return round(($this->completedPagesCount() / $this->total_pages) * 100, 2);
    }

    public function getNextPendingPage()
    {
        return $this->pages()
            ->where('status', 'pending')
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

    public function pdfPages()
    {
        return $this->hasMany(PdfPage::class);
    }

}