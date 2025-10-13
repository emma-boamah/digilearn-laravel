<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_type',
        'project_id',
        'project_title',
        'project_subject',
        'project_level',
        'status',
        'progress_percentage',
        'project_data',
        'progress_data',
        'started_at',
        'last_accessed_at',
        'completed_at',
        'paused_at',
        'time_spent_seconds',
        'access_count',
        'is_favorite',
        'notes',
    ];

    protected $casts = [
        'project_data' => 'array',
        'progress_data' => 'array',
        'started_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'completed_at' => 'datetime',
        'paused_at' => 'datetime',
        'progress_percentage' => 'decimal:2',
        'is_favorite' => 'boolean',
    ];

    /**
     * Get the user that owns the project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sessions for this project.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(ProjectSession::class);
    }

    /**
     * Create or update a user project
     */
    public static function createOrUpdateProject($userId, $projectType, $projectId, $projectData, $progressData = null)
    {
        $project = static::updateOrCreate(
            [
                'user_id' => $userId,
                'project_type' => $projectType,
                'project_id' => $projectId,
            ],
            [
                'project_title' => $projectData['title'],
                'project_subject' => $projectData['subject'],
                'project_level' => $projectData['level'] ?? 'unknown',
                'project_data' => $projectData,
                'last_accessed_at' => now(),
                'access_count' => DB::raw('access_count + 1'),
            ]
        );

        // Set started_at if this is the first access
        if (!$project->started_at) {
            $project->started_at = now();
            $project->status = 'in_progress';
        }

        // Update progress if provided
        if ($progressData) {
            $project->updateProgress($progressData);
        }

        $project->save();

        return $project;
    }

    /**
     * Update project progress
     */
    public function updateProgress($progressData)
    {
        $this->progress_data = array_merge($this->progress_data ?? [], $progressData);
        
        // Calculate progress percentage based on project type
        if ($this->project_type === 'lesson') {
            $this->progress_percentage = $progressData['completion_percentage'] ?? 0;
            
            if ($this->progress_percentage >= 90) {
                $this->status = 'completed';
                $this->completed_at = now();
            } elseif ($this->progress_percentage > 0) {
                $this->status = 'in_progress';
            }
        } elseif ($this->project_type === 'quiz') {
            $this->progress_percentage = $progressData['completion_percentage'] ?? 0;
            
            if (isset($progressData['completed']) && $progressData['completed']) {
                $this->status = 'completed';
                $this->completed_at = now();
                $this->progress_percentage = 100;
            } elseif ($this->progress_percentage > 0) {
                $this->status = 'in_progress';
            }
        }

        $this->last_accessed_at = now();
    }

    /**
     * Mark project as paused
     */
    public function pauseProject($notes = null)
    {
        $this->status = 'paused';
        $this->paused_at = now();
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();
    }

    /**
     * Resume paused project
     */
    public function resumeProject()
    {
        $this->status = 'in_progress';
        $this->paused_at = null;
        $this->last_accessed_at = now();
        $this->save();
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite()
    {
        $this->is_favorite = !$this->is_favorite;
        $this->save();
        return $this->is_favorite;
    }

    /**
     * Get projects by status for a user
     */
    public static function getProjectsByStatus($userId, $status = null)
    {
        $query = static::where('user_id', $userId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderBy('last_accessed_at', 'desc')->get();
    }

    /**
     * Get project statistics for a user
     */
    public static function getProjectStats($userId)
    {
        return static::where('user_id', $userId)
            ->selectRaw('
                COUNT(*) as total_projects,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_projects,
                SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_projects,
                SUM(CASE WHEN status = "paused" THEN 1 ELSE 0 END) as paused_projects,
                SUM(CASE WHEN project_type = "lesson" THEN 1 ELSE 0 END) as lesson_projects,
                SUM(CASE WHEN project_type = "quiz" THEN 1 ELSE 0 END) as quiz_projects,
                AVG(progress_percentage) as avg_progress,
                SUM(time_spent_seconds) as total_time_spent
            ')
            ->first();
    }

    /**
     * Get recently accessed projects
     */
    public static function getRecentProjects($userId, $limit = 5)
    {
        return static::where('user_id', $userId)
            ->where('status', '!=', 'not_started')
            ->orderBy('last_accessed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get projects by type
     */
    public static function getProjectsByType($userId, $type)
    {
        return static::where('user_id', $userId)
            ->where('project_type', $type)
            ->orderBy('last_accessed_at', 'desc')
            ->get();
    }

    /**
     * Get favorite projects
     */
    public static function getFavoriteProjects($userId)
    {
        return static::where('user_id', $userId)
            ->where('is_favorite', true)
            ->orderBy('last_accessed_at', 'desc')
            ->get();
    }

    /**
     * Calculate estimated time to complete
     */
    public function getEstimatedTimeToComplete()
    {
        if ($this->status === 'completed') {
            return 0;
        }

        $remainingProgress = 100 - $this->progress_percentage;
        
        if ($this->progress_percentage > 0 && $this->time_spent_seconds > 0) {
            $timePerPercent = $this->time_spent_seconds / $this->progress_percentage;
            return round($timePerPercent * $remainingProgress);
        }

        // Default estimates based on project type
        if ($this->project_type === 'lesson') {
            return 1800; // 30 minutes default for lessons
        } elseif ($this->project_type === 'quiz') {
            return 900; // 15 minutes default for quizzes
        }

        return 1200; // 20 minutes default
    }

    /**
     * Get formatted time spent
     */
    public function getFormattedTimeSpent()
    {
        $hours = floor($this->time_spent_seconds / 3600);
        $minutes = floor(($this->time_spent_seconds % 3600) / 60);
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($minutes > 0) {
            return "{$minutes}m";
        } else {
            return "< 1m";
        }
    }

    /**
     * Get status badge color
     */
    public function getStatusColor()
    {
        return match($this->status) {
            'completed' => 'success',
            'in_progress' => 'primary',
            'paused' => 'warning',
            'not_started' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get status display text
     */
    public function getStatusText()
    {
        return match($this->status) {
            'completed' => 'Completed',
            'in_progress' => 'In Progress',
            'paused' => 'Paused',
            'not_started' => 'Not Started',
            default => 'Unknown'
        };
    }
}
