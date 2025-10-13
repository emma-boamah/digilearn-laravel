<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\UserProject;
use App\Models\ProjectSession;
use Carbon\Carbon;

class ProjectController extends Controller
{
    /**
     * Show user projects dashboard
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $filter = $request->get('filter', 'all'); // all, in_progress, completed, paused, favorites
        $type = $request->get('type', 'all'); // all, lesson, quiz
        
        // Get projects based on filters
        $query = UserProject::where('user_id', $userId);
        
        if ($filter !== 'all') {
            if ($filter === 'favorites') {
                $query->where('is_favorite', true);
            } else {
                $query->where('status', $filter);
            }
        }
        
        if ($type !== 'all') {
            $query->where('project_type', $type);
        }
        
        $projects = $query->orderBy('last_accessed_at', 'desc')->paginate(12);
        
        // Get project statistics
        $stats = UserProject::getProjectStats($userId);
        
        // Get recent projects
        $recentProjects = UserProject::getRecentProjects($userId, 5);
        
        return view('dashboard.my-projects', compact(
            'projects',
            'stats',
            'recentProjects',
            'filter',
            'type'
        ));
    }

    /**
     * Start or resume a project
     */
    public function startProject(Request $request)
    {
        $request->validate([
            'project_type' => 'required|in:lesson,quiz',
            'project_id' => 'required|string',
            'project_data' => 'required|array',
        ]);

        $userId = Auth::id();
        
        // Create or update the project
        $project = UserProject::createOrUpdateProject(
            $userId,
            $request->project_type,
            $request->project_id,
            $request->project_data
        );

        // Start a new session
        $session = ProjectSession::startSession(
            $project->id,
            $project->progress_percentage,
            $request->get('session_data', [])
        );

        Log::info('project_started', [
            'user_id' => $userId,
            'project_id' => $project->id,
            'project_type' => $request->project_type,
            'session_id' => $session->id,
        ]);

        return response()->json([
            'success' => true,
            'project' => $project,
            'session' => $session,
            'message' => 'Project started successfully',
        ]);
    }

    /**
     * Update project progress
     */
    public function updateProgress(Request $request, $projectId)
    {
        $request->validate([
            'progress_data' => 'required|array',
            'session_data' => 'array',
        ]);

        $userId = Auth::id();
        $project = UserProject::where('user_id', $userId)->findOrFail($projectId);
        
        // Update project progress
        $project->updateProgress($request->progress_data);
        
        // Update active session if exists
        $activeSession = $project->sessions()
            ->where('session_status', 'active')
            ->latest()
            ->first();
            
        if ($activeSession) {
            $activeSession->updateProgress(
                $project->progress_percentage,
                $request->get('session_data', [])
            );
        }

        Log::info('project_progress_updated', [
            'user_id' => $userId,
            'project_id' => $project->id,
            'progress_percentage' => $project->progress_percentage,
            'status' => $project->status,
        ]);

        return response()->json([
            'success' => true,
            'project' => $project,
            'message' => 'Progress updated successfully',
        ]);
    }

    /**
     * Pause a project
     */
    public function pauseProject(Request $request, $projectId)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();
        $project = UserProject::where('user_id', $userId)->findOrFail($projectId);
        
        // Pause the project
        $project->pauseProject($request->notes);
        
        // End active session
        $activeSession = $project->sessions()
            ->where('session_status', 'active')
            ->latest()
            ->first();
            
        if ($activeSession) {
            $activeSession->endSession($project->progress_percentage, 'Project paused');
        }

        Log::info('project_paused', [
            'user_id' => $userId,
            'project_id' => $project->id,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'project' => $project,
            'message' => 'Project paused successfully',
        ]);
    }

    /**
     * Resume a paused project
     */
    public function resumeProject($projectId)
    {
        $userId = Auth::id();
        $project = UserProject::where('user_id', $userId)->findOrFail($projectId);
        
        // Resume the project
        $project->resumeProject();
        
        // Start a new session
        $session = ProjectSession::startSession(
            $project->id,
            $project->progress_percentage
        );

        Log::info('project_resumed', [
            'user_id' => $userId,
            'project_id' => $project->id,
            'session_id' => $session->id,
        ]);

        return response()->json([
            'success' => true,
            'project' => $project,
            'session' => $session,
            'message' => 'Project resumed successfully',
        ]);
    }

    /**
     * Complete a project
     */
    public function completeProject(Request $request, $projectId)
    {
        $request->validate([
            'final_progress_data' => 'array',
            'completion_notes' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();
        $project = UserProject::where('user_id', $userId)->findOrFail($projectId);
        
        // Update final progress
        if ($request->has('final_progress_data')) {
            $project->updateProgress($request->final_progress_data);
        }
        
        // Mark as completed
        $project->status = 'completed';
        $project->completed_at = now();
        $project->progress_percentage = 100;
        
        if ($request->completion_notes) {
            $project->notes = $request->completion_notes;
        }
        
        $project->save();
        
        // End active session
        $activeSession = $project->sessions()
            ->where('session_status', 'active')
            ->latest()
            ->first();
            
        if ($activeSession) {
            $activeSession->endSession(100, 'Project completed');
        }

        Log::info('project_completed', [
            'user_id' => $userId,
            'project_id' => $project->id,
            'completion_time' => $project->completed_at,
        ]);

        return response()->json([
            'success' => true,
            'project' => $project,
            'message' => 'Project completed successfully!',
        ]);
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite($projectId)
    {
        $userId = Auth::id();
        $project = UserProject::where('user_id', $userId)->findOrFail($projectId);
        
        $isFavorite = $project->toggleFavorite();

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
            'message' => $isFavorite ? 'Added to favorites' : 'Removed from favorites',
        ]);
    }

    /**
     * Delete a project
     */
    public function deleteProject($projectId)
    {
        $userId = Auth::id();
        $project = UserProject::where('user_id', $userId)->findOrFail($projectId);
        
        // End any active sessions
        $project->sessions()
            ->where('session_status', 'active')
            ->update([
                'session_end' => now(),
                'session_status' => 'abandoned',
                'duration_seconds' => \DB::raw('TIMESTAMPDIFF(SECOND, session_start, NOW())')
            ]);
        
        $project->delete();

        Log::info('project_deleted', [
            'user_id' => $userId,
            'project_id' => $projectId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ]);
    }

    /**
     * Get project details
     */
    public function getProject($projectId)
    {
        $userId = Auth::id();
        $project = UserProject::where('user_id', $userId)
            ->with(['sessions' => function($query) {
                $query->orderBy('session_start', 'desc')->limit(10);
            }])
            ->findOrFail($projectId);

        return response()->json([
            'success' => true,
            'project' => $project,
        ]);
    }

    /**
     * Get project analytics
     */
    public function getAnalytics(Request $request)
    {
        $userId = Auth::id();
        $period = $request->get('period', '7d'); // 7d, 30d, 90d, 1y
        
        $startDate = match($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => now()->subDays(7)
        };

        // Get project activity over time
        $activityData = UserProject::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as projects_started')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get completion rate by subject
        $subjectStats = UserProject::where('user_id', $userId)
            ->selectRaw('
                project_subject,
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                AVG(progress_percentage) as avg_progress
            ')
            ->groupBy('project_subject')
            ->get();

        // Get time spent by project type
        $timeByType = UserProject::where('user_id', $userId)
            ->selectRaw('
                project_type,
                SUM(time_spent_seconds) as total_time,
                AVG(time_spent_seconds) as avg_time,
                COUNT(*) as project_count
            ')
            ->groupBy('project_type')
            ->get();

        return response()->json([
            'success' => true,
            'analytics' => [
                'activity_data' => $activityData,
                'subject_stats' => $subjectStats,
                'time_by_type' => $timeByType,
                'period' => $period,
            ],
        ]);
    }
}
