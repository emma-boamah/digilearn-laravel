<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'country',
        'password',
        'email_verified_at',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'last_login_ip',
        'registration_ip',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'avatar',
        'phone',
        'phone_verified_at',
        'date_of_birth',
        'city',
        'education_level',
        'grade',
        'preferred_language',
        'learning_style',
        'bio',
        'current_room_id',
        'is_online',
        'is_superuser',
        'last_activity_at',
        'is_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'locked_until' => 'datetime',
        'last_login_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_recovery_codes' => 'encrypted:array',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'failed_login_attempts' => 'integer',
        'is_online' => 'boolean',
        'is_superuser' => 'boolean',
        'last_activity_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * Get user's subscriptions
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get user's active subscription
     */
    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)
                    ->active()
                    ->with('pricingPlan')
                    ->latest();
    }

    /**
     * Get user's current subscription (active or trial)
     */
    public function currentSubscription()
    {
        return $this->hasOne(UserSubscription::class)
                    ->whereIn('status', ['active', 'trial'])
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->with('pricingPlan')
                    ->latest();
    }

    /**
     * Check if user account is locked
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Check if user has two-factor authentication enabled
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Check if user has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    /**
     * Check if user is in trial period
     */
    public function isInTrial(): bool
    {
        $subscription = $this->currentSubscription;
        return $subscription && $subscription->isInTrial();
    }

    /**
     * Get user's current plan name
     */
    public function getCurrentPlanAttribute()
    {
        $subscription = $this->currentSubscription;
        return $subscription ? $subscription->pricingPlan->name : 'Free';
    }

    /**
     * Check if user has the 'Extra Tuition' plan
     */
    public function hasExtraTuitionPlan(): bool
    {
        if ($this->is_superuser) {
            return true; // Superusers always have access to all plans
        }
        $subscription = $this->currentSubscription;
        return $subscription && $subscription->isActive() && $subscription->pricingPlan->name === 'Extra Tuition';
    }

    /**
     * Get the user's full name parts
     */
    public function getFirstNameAttribute()
    {
        return explode(' ', $this->name)[0] ?? '';
    }

    public function getLastNameAttribute()
    {
        return explode(' ', $this->name, 2)[1] ?? '';
    }

    /**
     * Get the user's avatar URL
     */
    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return null;
        }

        // If avatar is an absolute URL (e.g., Google), return as-is
        if (preg_match('/^https?:\/\//', $this->avatar)) {
            return $this->avatar;
        }

        // Check for invalid paths (temporary upload paths, etc.)
        if (preg_match('/^tmp\//', $this->avatar) || !preg_match('/^avatars\//', $this->avatar)) {
            // Invalid path detected, clear it
            $this->avatar = null;
            $this->save();
            return null;
        }

        // Local storage: generate a relative URL to avoid APP_URL host/scheme mismatches
        // This ensures the image is served from the current host (via the storage symlink)
        $relativeUrl = '/storage/' . ltrim($this->avatar, '/');
        return $relativeUrl . '?v=' . ($this->updated_at ? $this->updated_at->timestamp : time());
    }

    /**
     * Get the user's initials for avatar fallback
     */
    public function getInitialsAttribute()
    {
        $names = explode(' ', trim($this->name ?? ''));
        $initials = '';
        foreach ($names as $name) {
            if (!empty($name)) {
                $initials .= strtoupper(substr($name, 0, 1));
            }
        }
        return substr($initials ?: 'U', 0, 2);
    }

    /**
     * Get avatar with fallback to UI Avatars service
     */
    public function getAvatarWithFallback($size = 40)
    {
        if ($this->avatar_url) {
            return $this->avatar_url;
        }
        
        // Fallback to UI Avatars service
        $name = urlencode($this->name ?? 'User');
        return "https://ui-avatars.com/api/?name={$name}&size={$size}&background=random&color=fff&bold=true";
    }

    /**
     * Get avatar HTML element
     */
    public function getAvatarHtml($size = 40, $classes = '')
    {
        $avatarUrl = $this->getAvatarWithFallback($size);
        $defaultClasses = 'rounded-full object-cover';
        $allClasses = trim($defaultClasses . ' ' . $classes);
        
        return "<img src=\"{$avatarUrl}\" alt=\"{$this->name}\" class=\"{$allClasses}\" style=\"width: {$size}px; height: {$size}px;\" />";
    }

    /**
     * Get the virtual class the user is currently in.
     */
    public function virtualClass()
    {
        return $this->belongsTo(VirtualClass::class, 'current_room_id', 'room_id');
    }

    /**
     * Get the quizzes uploaded by the user.
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'uploaded_by');
    }

    /**
     * Get the videos uploaded by the user.
     */
    public function videos()
    {
        return $this->hasMany(Video::class, 'uploaded_by');
    }

    /**
     * Get the documents uploaded by the user.
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    /**
     * Get the user's notifications.
     */
    public function notifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable');
    }

    /**
     * Get the user's unread notifications.
     */
    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    /**
     * Get the user's notification preferences.
     */
    public function notificationPreferences()
    {
        return $this->hasMany(UserNotificationPreference::class);
    }
}
