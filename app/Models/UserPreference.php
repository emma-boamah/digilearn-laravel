<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preference_type',
        'preference_value',
        'weight',
    ];

    protected $casts = [
        'weight' => 'integer',
    ];

    /**
     * Get the user that owns the preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by preference type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('preference_type', $type);
    }

    /**
     * Scope for filtering by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get preferences by type for a user
     */
    public static function getUserPreferences($userId, $type = null)
    {
        $query = self::where('user_id', $userId);

        if ($type) {
            $query->where('preference_type', $type);
        }

        return $query->orderBy('weight', 'desc')->get();
    }

    /**
     * Get subject preferences for a user
     */
    public static function getSubjectPreferences($userId)
    {
        return self::getUserPreferences($userId, 'subject');
    }

    /**
     * Get grade level preferences for a user
     */
    public static function getGradeLevelPreferences($userId)
    {
        return self::getUserPreferences($userId, 'grade_level');
    }

    /**
     * Get learning style preferences for a user
     */
    public static function getLearningStylePreferences($userId)
    {
        return self::getUserPreferences($userId, 'learning_style');
    }
}