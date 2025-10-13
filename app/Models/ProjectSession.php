<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;


class ProjectSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_project_id',
        'session_start',
        'session_end',
        'duration_seconds',
        'progress_at_start',
        'progress_at_end',
        'session_data',
        'device_type',
        'browser',
        'ip_address',
        'session_status',
        'notes',
    ];

    protected $casts = [
        'session_start' => 'datetime',
        'session_end' => 'datetime',
        'session_data' => 'array',
        'progress_at_start' => 'decimal:2',
        'progress_at_end' => 'decimal:2',
    ];

    /**
     * Get the user project that owns the session.
     */
    public function userProject(): BelongsTo
    {
        return $this->belongsTo(UserProject::class);
    }

    /**
     * Start a new session
     */
    public static function startSession($userProjectId, $currentProgress = 0, $sessionData = [])
    {
        // End any active sessions for this project
        static::where('user_project_id', $userProjectId)
            ->where('session_status', 'active')
            ->update([
                'session_end' => now(),
                'session_status' => 'abandoned',
                'duration_seconds' => DB::raw('TIMESTAMPDIFF(SECOND, session_start, NOW())')
            ]);

        // Create new session
        return static::create([
            'user_project_id' => $userProjectId,
            'session_start' => now(),
            'progress_at_start' => $currentProgress,
            'session_data' => $sessionData,
            'device_type' => request()->header('User-Agent') ? 
                (preg_match('/Mobile/', request()->header('User-Agent')) ? 'mobile' : 'desktop') : 'unknown',
            'browser' => request()->header('User-Agent'),
            'ip_address' => request()->ip(),
            'session_status' => 'active',
        ]);
    }

    /**
     * End the session
     */
    public function endSession($finalProgress = null, $notes = null)
    {
        $this->session_end = now();
        $this->duration_seconds = $this->session_start->diffInSeconds($this->session_end);
        $this->session_status = 'completed';
        
        if ($finalProgress !== null) {
            $this->progress_at_end = $finalProgress;
        }
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $this->save();

        // Update the parent project's time spent
        $this->userProject->increment('time_spent_seconds', $this->duration_seconds);
    }

    /**
     * Update session progress
     */
    public function updateProgress($currentProgress, $sessionData = [])
    {
        $this->progress_at_end = $currentProgress;
        $this->session_data = array_merge($this->session_data ?? [], $sessionData);
        $this->save();
    }
}
