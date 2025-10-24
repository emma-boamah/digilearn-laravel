<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'action',
        'description',
        'level',
        'user_id',
        'user_email',
        'ip_address',
        'user_agent',
        'metadata',
        'model_type',
        'model_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed this activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that was affected by this activity.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Log an activity
     */
    public static function log(
        string $action,
        string $description,
        string $level = 'info',
        ?int $userId = null,
        ?string $userEmail = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $metadata = null,
        ?Model $model = null
    ): self {
        $attributes = [
            'action' => $action,
            'description' => $description,
            'level' => $level,
            'user_id' => $userId,
            'user_email' => $userEmail,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ];

        if ($model) {
            $attributes['model_type'] = get_class($model);
            $attributes['model_id'] = $model->getKey();
        }

        return static::create($attributes);
    }

    /**
     * Get activities by level
     */
    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Get activities by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get activities by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get activities within date range
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get recent activities
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
