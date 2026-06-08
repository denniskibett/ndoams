<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'bio',
        'avatar',
        'country',
        'city',
        'postal_code',
        'tax_id',
        'social',
        'role_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'social' => 'array',
    ];

    /**
     * Get the role that belongs to the user
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get marriages where user is the registrar
     */
    public function marriagesAsRegistrar(): HasMany
    {
        return $this->hasMany(Marriage::class, 'marriage_registrar_id');
    }

    /**
     * Get uploaded images
     */
    public function uploadedImages(): HasMany
    {
        return $this->hasMany(Image::class, 'uploaded_by');
    }

    /**
     * Get uploaded PDFs
     */
    public function uploadedPdfs(): HasMany
    {
        return $this->hasMany(PdfUpload::class, 'uploaded_by');
    }

    /**
     * Get PDF pages assigned to this user (for data clerks)
     */
    public function assignedPages(): HasMany
    {
        return $this->hasMany(PdfPage::class, 'assigned_to');
    }

    /**
     * Get completed pages for this user
     */
    public function completedPages(): HasMany
    {
        return $this->hasMany(PdfPage::class, 'assigned_to')
            ->whereIn('status', ['completed', 'review_needed']);
    }

    /**
     * Get pending pages for this user
     */
    public function pendingPages(): HasMany
    {
        return $this->hasMany(PdfPage::class, 'assigned_to')
            ->whereNotIn('status', ['completed', 'review_needed']);
    }

    /**
     * Get marriages created from pages assigned to this user
     */
    public function marriagesFromAssignedPages(): HasMany
    {
        return $this->hasMany(Marriage::class, 'created_by')
            ->whereHas('pdfPage', function($query) {
                $query->where('assigned_to', $this->id);
            });
    }

    /**
     * For Marriage Teller: Get clerks managed by this teller
     */
    public function managedClerks()
    {
        return $this->belongsToMany(
            User::class, 
            'clerk_managements', 
            'marriage_teller_id', 
            'data_clerk_id'
        )->whereHas('role', function($q) {
            $q->where('name', 'data_clerk');
        });
    }

    /**
     * Get clerk management relationships where this user is the teller
     */
    public function clerkManagements(): HasMany
    {
        return $this->hasMany(ClerkManagement::class, 'marriage_teller_id');
    }

    /**
     * Get clerk management relationships where this user is the clerk
     */
    public function tellerManagements(): HasMany
    {
        return $this->hasMany(ClerkManagement::class, 'data_clerk_id');
    }

    /**
     * Get the teller managing this clerk (if user is a data clerk)
     */
    public function managingTeller()
    {
        return $this->belongsToMany(
            User::class,
            'clerk_managements',
            'data_clerk_id',
            'marriage_teller_id'
        )->whereHas('role', function($q) {
            $q->where('name', 'marriage_teller');
        })->first();
    }

    // ========== ROLE CHECK METHODS ==========

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'admin';
    }

    /**
     * Check if user is marriage registrar
     */
    public function isRegistrar(): bool
    {
        return $this->role && $this->role->name === 'marriage_registrar';
    }

    /**
     * Check if user is data clerk
     */
    public function isDataClerk(): bool
    {
        return $this->role && $this->role->name === 'data_clerk';
    }

    /**
     * Check if user is marriage teller
     */
    public function isTeller(): bool
    {
        return $this->role && $this->role->name === 'marriage_teller';
    }

    /**
     * Check if user is attorney general
     */
    public function isAttorneyGeneral(): bool
    {
        return $this->role && in_array($this->role->name, ['attorney_general', 'ag']);
    }

    /**
     * Get user's role name
     */
    public function getRoleNameAttribute(): string
    {
        return $this->role ? $this->role->name : 'user';
    }

    // ========== SCOPES ==========

    /**
     * Scope to get only data clerks
     */
    public function scopeDataClerks($query)
    {
        return $query->whereHas('role', function($q) {
            $q->where('name', 'data_clerk');
        });
    }

    /**
     * Scope to get only marriage tellers
     */
    public function scopeMarriageTellers($query)
    {
        return $query->whereHas('role', function($q) {
            $q->where('name', 'marriage_teller');
        });
    }

    /**
     * Scope to get only marriage registrars
     */
    public function scopeMarriageRegistrars($query)
    {
        return $query->whereHas('role', function($q) {
            $q->where('name', 'marriage_registrar');
        });
    }

    /**
     * Scope to get only admins
     */
    public function scopeAdmins($query)
    {
        return $query->whereHas('role', function($q) {
            $q->where('name', 'admin');
        });
    }

    /**
     * Scope to get active users (has activity in last 30 days)
     */
    public function scopeActive($query)
    {
        return $query->where('last_login_at', '>=', now()->subDays(30));
    }

    // ========== PERFORMANCE METRICS ==========

    /**
     * Get completion rate for data clerk
     */
    public function getCompletionRateAttribute(): float
    {
        if (!$this->isDataClerk()) {
            return 0;
        }
        
        $assigned = $this->assignedPages()->count();
        if ($assigned === 0) {
            return 0;
        }
        
        $completed = $this->completedPages()->count();
        return round(($completed / $assigned) * 100, 2);
    }

    /**
     * Get total pages assigned
     */
    public function getTotalAssignedPagesAttribute(): int
    {
        return $this->assignedPages()->count();
    }

    /**
     * Get total completed pages
     */
    public function getTotalCompletedPagesAttribute(): int
    {
        return $this->completedPages()->count();
    }

    /**
     * Get total pending pages
     */
    public function getTotalPendingPagesAttribute(): int
    {
        return $this->pendingPages()->count();
    }

    /**
     * Get today's completed pages
     */
    public function getTodayCompletedPagesAttribute(): int
    {
        return $this->completedPages()
            ->whereDate('updated_at', today())
            ->count();
    }

    // ========== SOCIAL MEDIA METHODS ==========

    /**
     * Get social links with proper URLs
     */
    public function getSocialLinksAttribute(): array
    {
        $social = $this->social ?: [];
        
        $links = [];
        
        if (!empty($social['facebook'])) {
            $links['facebook'] = $this->getSocialUrl($social['facebook'], 'https://facebook.com/');
        }
        
        if (!empty($social['twitter'])) {
            $links['twitter'] = $this->getSocialUrl($social['twitter'], 'https://twitter.com/');
        }
        
        if (!empty($social['instagram'])) {
            $links['instagram'] = $this->getSocialUrl($social['instagram'], 'https://instagram.com/');
        }
        
        if (!empty($social['linkedin'])) {
            $links['linkedin'] = $this->getSocialUrl($social['linkedin'], 'https://linkedin.com/in/');
        }
        
        return $links;
    }

    /**
     * Extract username from URL or return as-is
     */
    public function getSocialUsernamesAttribute(): array
    {
        $social = $this->social ?: [];
        $usernames = [];
        
        foreach ($social as $platform => $value) {
            $usernames[$platform] = $this->extractUsername($value);
        }
        
        return $usernames;
    }

    /**
     * Helper to extract username from URL
     */
    private function extractUsername(?string $url): string
    {
        if (empty($url)) {
            return '';
        }
        
        // If it's already a username (no dots, no slashes), return it
        if (!str_contains($url, '.') && !str_contains($url, '/')) {
            return $url;
        }
        
        // Remove protocol and domain
        $patterns = [
            'facebook' => [
                '/^https?:\/\/(www\.)?facebook\.com\//',
                '/^https?:\/\/fb\.com\//'
            ],
            'twitter' => [
                '/^https?:\/\/(www\.)?twitter\.com\//',
                '/^https?:\/\/x\.com\//'
            ],
            'instagram' => [
                '/^https?:\/\/(www\.)?instagram\.com\//'
            ],
            'linkedin' => [
                '/^https?:\/\/(www\.)?linkedin\.com\/in\//'
            ]
        ];
        
        // Try to match known patterns
        foreach ($patterns as $platformPatterns) {
            foreach ($platformPatterns as $pattern) {
                if (preg_match($pattern, $url)) {
                    return preg_replace($pattern, '', $url);
                }
            }
        }
        
        // If no match, return as-is
        return $url;
    }

    /**
     * Helper to create full URL from username
     */
    private function getSocialUrl(?string $value, string $baseUrl): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        // If it's already a URL, return it
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        
        // Otherwise, append to base URL
        return rtrim($baseUrl, '/') . '/' . ltrim($value, '/');
    }

    /**
     * Prepare social data for storage
     */
    public function prepareSocialData(array $data): array
    {
        $social = [];
        
        foreach ($data as $platform => $value) {
            if (empty($value)) {
                continue;
            }
            
            // Clean the value
            $value = trim($value);
            
            // If it's a full URL, store as-is
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                $social[$platform] = $value;
            } else {
                // Store as username
                $social[$platform] = $this->cleanUsername($value);
            }
        }
        
        return $social;
    }

    /**
     * Clean username (remove @ symbol, trim)
     */
    private function cleanUsername(string $username): string
    {
        return ltrim(trim($username), '@');
    }
}