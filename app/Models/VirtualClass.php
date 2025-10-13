<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Import Carbon for date/time operations

class VirtualClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'grade_level',
        'room_id',
        'is_active',
        'topic',
        'start_time',
        'end_time',
        'status', // Added status field for class lifecycle (e.g., scheduled, active, ended, cancelled)
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function participants()
    {
        return $this->hasMany(User::class, 'current_room_id', 'room_id');
    }

    /**
     * Scope for active classes
     * A class is considered active if:
     * 1. Its 'is_active' flag is true.
     * 2. Its 'start_time' is in the past or present.
     * 3. Its 'end_time' is in the future or present.
     * 4. Its 'status' is 'active' or 'scheduled' (if scheduled_at is in the future).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('start_time', '<=', now())
                     ->where('end_time', '>=', now())
                     ->whereIn('status', ['active', 'scheduled']); // Consider 'scheduled' if within time frame
    }

    /**
     * Scope for classes by grade level
     */
    public function scopeForGrade($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * Check if the class is currently live.
     */
    public function isLive()
    {
        return $this->is_active &&
               $this->start_time &&
               $this->end_time &&
               Carbon::now()->between($this->start_time, $this->end_time);
    }

    /**
     * Get the remaining time until the class ends, or 0 if already ended.
     */
    public function getRemainingTimeAttribute()
    {
        if ($this->end_time && Carbon::now()->lt($this->end_time)) {
            return Carbon::now()->diffInMinutes($this->end_time);
        }
        return 0;
    }
}
