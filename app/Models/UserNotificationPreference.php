<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type_id',
        'channels',
        'is_enabled',
    ];

    protected $casts = [
        'channels' => 'array',
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the user that owns the preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification type.
     */
    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }

    /**
     * Check if a specific channel is enabled.
     */
    public function isChannelEnabled(string $channel): bool
    {
        return $this->is_enabled && in_array($channel, $this->channels);
    }

    /**
     * Enable a specific channel.
     */
    public function enableChannel(string $channel): void
    {
        if (!$this->isChannelEnabled($channel)) {
            $this->channels = array_unique(array_merge($this->channels, [$channel]));
            $this->save();
        }
    }

    /**
     * Disable a specific channel.
     */
    public function disableChannel(string $channel): void
    {
        if ($this->isChannelEnabled($channel)) {
            $this->channels = array_diff($this->channels, [$channel]);
            $this->save();
        }
    }

    /**
     * Scope for enabled preferences.
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope for specific notification type.
     */
    public function scopeForType($query, $typeSlug)
    {
        return $query->whereHas('notificationType', function ($q) use ($typeSlug) {
            $q->where('slug', $typeSlug);
        });
    }
}