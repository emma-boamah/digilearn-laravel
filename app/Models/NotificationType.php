<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'color',
        'is_system',
        'is_active',
        'default_channels',
        'priority',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'default_channels' => 'array',
    ];

    /**
     * Get the user notification preferences for this type.
     */
    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    /**
     * Scope for active notification types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for system notification types.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for user notification types.
     */
    public function scopeUser($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabelAttribute()
    {
        return match($this->priority) {
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
            default => 'Medium'
        };
    }

    /**
     * Get available channels.
     */
    public static function getAvailableChannels()
    {
        return [
            'database' => 'In-App',
            'mail' => 'Email',
            'broadcast' => 'Real-time',
        ];
    }
}