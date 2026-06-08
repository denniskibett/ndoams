<?php
// app/Models/ClerkManagement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ClerkManagement extends Model
{
    protected $table = 'clerk_management';
    
    protected $fillable = [
        'data_clerk_id',
        'marriage_teller_id',
        'filled_count',
        'target_count',
        'status',
        'rating',
        'notes'
    ];

    protected $casts = [
        'filled_count' => 'integer',
        'target_count' => 'integer',
        'rating' => 'integer'
    ];

    protected $appends = ['progress_percentage'];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    // Statuses that count as completed work
    const COMPLETED_STATUSES = ['review_needed', 'completed', 'published'];

    // Relationships
    public function dataClerk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'data_clerk_id');
    }

    public function marriageTeller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marriage_teller_id');
    }

    // Get all pages assigned to this clerk
    public function assignedPages(): HasManyThrough
    {
        return $this->hasManyThrough(
            PdfPage::class,
            User::class,
            'id', // Local key on users table
            'assigned_to', // Foreign key on pdf_pages table
            'data_clerk_id', // Local key on clerk_management
            'id' // Local key on users
        )->where('pdf_pages.assigned_to', $this->data_clerk_id);
    }

    // Get completed pages for this clerk (pages that are done/processed)
    public function completedPages()
    {
        return $this->assignedPages()->whereIn('status', self::COMPLETED_STATUSES);
    }

    /**
     * Update counts for a single clerk based on current page assignments
     * This method queries the database once and updates the record
     */
    public function updateCounts()
    {
        // Target count = total pages assigned to this clerk
        $this->target_count = PdfPage::where('assigned_to', $this->data_clerk_id)->count();
        
        // Filled count = pages completed by data_clerk (any status that indicates completion)
        $this->filled_count = PdfPage::where('assigned_to', $this->data_clerk_id)
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->count();
        
        $this->save();
        
        return $this;
    }

    /**
     * Refresh all clerk counts in one efficient query
     * This runs a single query to update all clerks at once
     * Returns the number of clerks updated
     */
    public static function refreshAllCounts()
    {
        $updatedCount = 0;
        
        // Get all clerks with their assigned pages counts
        $clerks = self::all();
        
        foreach ($clerks as $clerk) {
            // Get counts using efficient queries
            $targetCount = PdfPage::where('assigned_to', $clerk->data_clerk_id)->count();
            $filledCount = PdfPage::where('assigned_to', $clerk->data_clerk_id)
                ->whereIn('status', self::COMPLETED_STATUSES)
                ->count();
            
            // Only update if values have changed to avoid unnecessary DB writes
            if ($clerk->target_count != $targetCount || $clerk->filled_count != $filledCount) {
                $clerk->target_count = $targetCount;
                $clerk->filled_count = $filledCount;
                $clerk->save();
                $updatedCount++;
            }
        }
        
        return $updatedCount;
    }

    /**
     * Alternative: Update all counts using a single raw query (more efficient for large datasets)
     * This runs one query to update all clerks at once
     */
    public static function refreshAllCountsBulk()
    {
        $updatedCount = 0;
        
        // Get all clerks with their counts in one go
        $clerks = self::withCount([
            'assignedPages as target_count',
            'assignedPages as filled_count' => function($query) {
                $query->whereIn('status', self::COMPLETED_STATUSES);
            }
        ])->get();
        
        foreach ($clerks as $clerk) {
            if ($clerk->target_count != $clerk->target_count_original || 
                $clerk->filled_count != $clerk->filled_count_original) {
                $clerk->target_count = $clerk->target_count;
                $clerk->filled_count = $clerk->filled_count;
                $clerk->save();
                $updatedCount++;
            }
        }
        
        return $updatedCount;
    }

    // Progress percentage
    public function getProgressPercentageAttribute()
    {
        if ($this->target_count === 0) {
            return 0;
        }
        return round(($this->filled_count / $this->target_count) * 100, 2);
    }

    // Get rating badge class
    public function getRatingBadgeClassAttribute()
    {
        if (!$this->rating) return 'bg-gray-100 text-gray-800';
        
        return match($this->rating) {
            1 => 'bg-red-100 text-red-800',
            2 => 'bg-orange-100 text-orange-800',
            3 => 'bg-yellow-100 text-yellow-800',
            4 => 'bg-green-100 text-green-800',
            5 => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForMarriageTeller($query, $tellerId)
    {
        return $query->where('marriage_teller_id', $tellerId);
    }

    public function scopeForDataClerk($query, $clerkId)
    {
        return $query->where('data_clerk_id', $clerkId);
    }

    /**
     * Boot the model
     * Add event listeners
     */
    protected static function boot()
    {
        parent::boot();
        
        // When a clerk management record is created, ensure counts are set to 0
        static::creating(function ($model) {
            if ($model->filled_count === null) $model->filled_count = 0;
            if ($model->target_count === null) $model->target_count = 0;
        });
    }
    
}