<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClerkManagement extends Model
{
    protected $fillable = [
        'data_clerk_id',
        'marriage_teller_id', 
        'year',
        'month',
        'filled_count',
        'target_count',
        'status',
        'notes'
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'filled_count' => 'integer',
        'target_count' => 'integer'
    ];

    public function dataClerk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'data_clerk_id');
    }

    public function marriageTeller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marriage_teller_id');
    }

    public function marriages(): HasMany
    {
        return $this->hasMany(Marriage::class, 'created_by', 'data_clerk_id')
            ->where('year', $this->year)
            ->where('month', $this->month);
    }

    // Progress percentage
    public function getProgressPercentageAttribute()
    {
        if ($this->target_count === 0) {
            return 0;
        }
        return round(($this->filled_count / $this->target_count) * 100, 2);
    }

    // Check if assignment is active
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    // Scope for active assignments
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for specific period
    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    // Scope for marriage teller
    public function scopeForMarriageTeller($query, $tellerId)
    {
        return $query->where('marriage_teller_id', $tellerId);
    }

    // Scope for data clerk
    public function scopeForDataClerk($query, $clerkId)
    {
        return $query->where('data_clerk_id', $clerkId);
    }

    public function pdfUpload()
    {
        return $this->belongsTo(PdfUpload::class);
    }

    public function assignedPdfPages()
    {
        return PdfPage::where('pdf_upload_id', $this->pdf_upload_id)
            ->whereIn('page_number', $this->assigned_pages ?? [])
            ->where('assigned_to', $this->data_clerk_id);
    }
}