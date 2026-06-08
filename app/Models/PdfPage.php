<?php
// app/Models/PdfPage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfPage extends Model
{
    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REVIEW_NEEDED = 'review_needed';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_ASSIGNED = 'assigned';

    protected $fillable = [
        'pdf_upload_id',
        'page_number',
        'status',
        'assigned_to',
        'completed_by',
        'assigned_at',
        'started_at',
        'completed_at',
        'time_spent_seconds',
        'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'time_spent_seconds' => 'integer'
    ];

    // Relationships
    public function pdfUpload(): BelongsTo
    {
        return $this->belongsTo(PdfUpload::class);
    }
    
    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
    
    public function marriage()
    {
        return $this->hasOne(Marriage::class, 'pdf_page_id');
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    
    public function scopeAssigned($query)
    {
        return $query->whereNotNull('assigned_to')->where('status', '!=', self::STATUS_COMPLETED);
    }
    
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
    
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }
    
    // Status helper methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
    
    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to) && $this->status !== self::STATUS_COMPLETED;
    }
    
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
    
    // Assign page to a data clerk
    public function assignTo($dataClerkId, $marriageTellerId = null): void
    {
        $this->update([
            'assigned_to' => $dataClerkId,
            'assigned_at' => now(),
            'status' => self::STATUS_PENDING // Still pending until data clerk starts
        ]);
        
        // Update clerk management counts
        $this->updateClerkManagementCounts($dataClerkId);
    }
    
    // Mark page as started by data clerk
    public function markAsStarted($dataClerkId = null): void
    {
        $data = [
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now()
        ];
        
        if ($dataClerkId && !$this->assigned_to) {
            $data['assigned_to'] = $dataClerkId;
            $data['assigned_at'] = now();
        }
        
        $this->update($data);
        
        // Update PDF upload status
        if ($this->pdfUpload) {
            $this->pdfUpload->updateStatusFromPages();
        }
    }
    
    // Mark page as completed by marriage teller
    public function markAsCompleted($marriageTellerId): void
    {
        $now = now();
        $startedAt = $this->started_at ?? $now;
        
        $timeSpent = $startedAt->diffInSeconds($now);
        
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_by' => $marriageTellerId,
            'completed_at' => $now,
            'time_spent_seconds' => $timeSpent
        ]);
        
        // Update associated marriage if exists
        if ($this->marriage) {
            $this->marriage->markAsCompleted();
        }
        
        // Update PDF upload status
        if ($this->pdfUpload) {
            $this->pdfUpload->updateStatusFromPages();
        }
        
        // Update clerk management counts
        if ($this->assigned_to) {
            $this->updateClerkManagementCounts($this->assigned_to);
        }
    }
    
    // Update clerk management counts when pages are assigned or completed
    protected function updateClerkManagementCounts($dataClerkId)
    {
        $clerkManagements = ClerkManagement::where('data_clerk_id', $dataClerkId)
            ->where('status', ClerkManagement::STATUS_ACTIVE)
            ->get();
        
        foreach ($clerkManagements as $management) {
            $management->updateCounts();
        }
    }
    
    // Get status badge class for UI
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_REVIEW_NEEDED => 'bg-purple-100 text-purple-800',
            self::STATUS_IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_SKIPPED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
    
    // Get formatted time spent
    public function getFormattedTimeSpentAttribute()
    {
        if (!$this->time_spent_seconds) return '0 seconds';
        
        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;
        
        if ($minutes > 0) {
            return $minutes . 'm ' . $seconds . 's';
        }
        
        return $seconds . 's';
    }

    public function assignedUser()
{
    return $this->belongsTo(User::class, 'assigned_to'); 
    // 'assigned_to' is the foreign key on pdf_pages table
    // User::class assumes you have App\Models\User
}
}