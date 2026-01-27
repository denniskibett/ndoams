<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfPage extends Model
{
    protected $fillable = [
        'pdf_upload_id',
        'page_number',
        'status',
        'assigned_to',
        'started_at',
        'completed_at',
        'data_entry_by',
        'verified_by',
        'verification_status',
        'notes',
        'is_locked'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_locked' => 'boolean'
    ];

    // Relationships
    public function pdfUpload(): BelongsTo
    {
        return $this->belongsTo(PdfUpload::class);
    }
    
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    public function dataEntryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'data_entry_by');
    }
    
    public function marriage()
    {
        return $this->hasOne(Marriage::class, 'pdf_page_id');
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }
    
    // Status methods
    public function isCompleted()
    {
        return $this->status === 'completed';
    }
    
    public function markAsStarted($userId)
    {
        $this->update([
            'status' => 'processing',
            'assigned_to' => $userId,
            'started_at' => now()
        ]);
    }
    
    public function markAsCompleted($userId)
    {
        $this->update([
            'status' => 'completed',
            'data_entry_by' => $userId,
            'completed_at' => now()
        ]);
    }

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_LINKED = 'linked';
    const STATUS_COMPLETED = 'completed';

    // Update status badge method
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'linked' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
        };
    }

    public function getStatusIcon()
    {
        return match($this->status) {
            'completed' => 'fa-check-circle text-green-500',
            'processing' => 'fa-spinner text-blue-500',
            'pending' => 'fa-clock text-yellow-500',
            default => 'fa-file text-gray-500'
        };
    }

    public function isLinked()
    {
        return !is_null($this->marriage);
    }

    public function getAssignedUserName()
    {
        return $this->assignedUser ? $this->assignedUser->name : 'Unassigned';
    }

    public function getUploaderName()
    {
        return $this->pdfUpload->uploader->name ?? 'Unknown';
    }

    
}