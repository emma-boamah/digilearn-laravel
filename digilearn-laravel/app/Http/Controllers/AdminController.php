<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{

    /**
     * Show the admin dashboard
     */
    public function dashboard()
    {
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities();
        
        // Get system health
        $systemHealth = $this->getSystemHealth();
        
        // Get popular lessons
        $popularLessons = $this->getPopularLessons();

        Log::channel('security')->info('admin_dashboard_accessed', [
            'admin_id' => Auth::id(),
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);

        return view('admin.dashboard', compact('stats', 'recentActivities', 'systemHealth', 'popularLessons'));
    }

    /**
     * Show users management page
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->whereNull('suspended_at');
                    break;
                case 'suspended':
                    $query->whereNotNull('suspended_at');
                    break;
                case 'verified':
                    $query->whereNotNull('email_verified_at');
                    break;
                case 'unverified':
                    $query->whereNull('email_verified_at');
                    break;
            }
        }

        // Filter by registration date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get user statistics
        $userStats = [
            'total' => User::count(),
            'active' => User::whereNull('suspended_at')->count(),
            'suspended' => User::whereNotNull('suspended_at')->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'new_today' => User::whereDate('created_at', today())->count(),
            'new_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        return view('admin.users.index', compact('users', 'userStats'));
    }

    /**
     * Show specific user details
     */
    public function showUser($id)
    {
        $user = User::findOrFail($id);
        
        // Get user activity logs (you might need to create this table)
        $activities = $this->getUserActivities($user->id);
        
        // Get user lesson progress
        $lessonProgress = $this->getUserLessonProgress($user->id);

        return view('admin.users.show', compact('user', 'activities', 'lessonProgress'));
    }

    /**
     * Suspend a user
     */
    public function suspendUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $reason = $request->input('reason', 'No reason provided');
        
        $user->update([
            'suspended_at' => now(),
            'suspension_reason' => $reason
        ]);

        Log::channel('security')->warning('user_suspended', [
            'admin_id' => Auth::id(),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'reason' => $reason,
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);

        return redirect()->back()->with('success', 'User suspended successfully.');
    }

    /**
     * Unsuspend a user
     */
    public function unsuspendUser($id)
    {
        $user = User::findOrFail($id);
        
        $user->update([
            'suspended_at' => null,
            'suspension_reason' => null
        ]);

        Log::channel('security')->info('user_unsuspended', [
            'admin_id' => Auth::id(),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);

        return redirect()->back()->with('success', 'User unsuspended successfully.');
    }

    /**
     * Bulk user operations
     */
    public function bulkUserAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:suspend,unsuspend,verify,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $userIds = $request->user_ids;
        $action = $request->action;
        $affectedCount = 0;

        switch ($action) {
            case 'suspend':
                $affectedCount = User::whereIn('id', $userIds)->update([
                    'suspended_at' => now(),
                    'suspension_reason' => 'Bulk suspension by admin'
                ]);
                break;
                
            case 'unsuspend':
                $affectedCount = User::whereIn('id', $userIds)->update([
                    'suspended_at' => null,
                    'suspension_reason' => null
                ]);
                break;
                
            case 'verify':
                $affectedCount = User::whereIn('id', $userIds)->update([
                    'email_verified_at' => now()
                ]);
                break;
                
            case 'delete':
                $affectedCount = User::whereIn('id', $userIds)->delete();
                break;
        }

        Log::channel('security')->warning('bulk_user_action', [
            'admin_id' => Auth::id(),
            'action' => $action,
            'affected_users' => $userIds,
            'affected_count' => $affectedCount,
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);

        return redirect()->back()->with('success', "Bulk {$action} completed. {$affectedCount} users affected.");
    }

    /**
     * Show content management page
     */
    public function content()
    {
        // Get lesson statistics
        $contentStats = $this->getContentStats();
        
        // Get recent content activities
        $recentContent = $this->getRecentContentActivities();

        return view('admin.content.index', compact('contentStats', 'recentContent'));
    }

    /**
     * Show analytics page
     */
    public function analytics()
    {
        // Get analytics data
        $analyticsData = $this->getAnalyticsData();
        
        return view('admin.analytics.index', compact('analyticsData'));
    }

    /**
     * Show security monitoring page
     */
    public function security()
    {
        // Get security logs
        $securityLogs = $this->getSecurityLogs();
        
        // Get failed login attempts
        $failedLogins = $this->getFailedLoginAttempts();
        
        // Get suspicious activities
        $suspiciousActivities = $this->getSuspiciousActivities();

        return view('admin.security.index', compact('securityLogs', 'failedLogins', 'suspiciousActivities'));
    }

    /**
     * Show system settings page
     */
    public function settings()
    {
        // Get current system settings
        $settings = $this->getSystemSettings();
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
            'max_login_attempts' => 'required|integer|min:1|max:10',
        ]);

        // Update settings (you might want to store these in a settings table or config)
        $settings = $request->only(['site_name', 'maintenance_mode', 'registration_enabled', 'max_login_attempts']);
        
        foreach ($settings as $key => $value) {
            Cache::put("setting.{$key}", $value, now()->addDays(30));
        }

        Log::channel('security')->info('system_settings_updated', [
            'admin_id' => Auth::id(),
            'updated_settings' => array_keys($settings),
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Export users data
     */
    public function exportUsers(Request $request)
    {
        $format = $request->input('format', 'csv');
        
        $users = User::select('id', 'name', 'email', 'phone', 'created_at', 'email_verified_at', 'suspended_at')
                    ->get();

        Log::channel('security')->info('users_data_exported', [
            'admin_id' => Auth::id(),
            'format' => $format,
            'user_count' => $users->count(),
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);

        if ($format === 'csv') {
            return $this->exportToCsv($users, 'users');
        }
        
        return $this->exportToJson($users, 'users');
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::whereNull('suspended_at')->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'suspended_users' => User::whereNotNull('suspended_at')->count(),
            'total_lessons' => 150, // Should be replaced with actual query
            'total_subjects' => 8,  // Should be replaced with actual query
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        // This would typically come from an activity log table
        return [
            ['type' => 'user_registration', 'user' => 'john@example.com', 'time' => '2 minutes ago'],
            ['type' => 'lesson_view', 'user' => 'Jane@example.com', 'lesson' => "Basic Mathematics", 'time' => '5 minutes ago'],
            ['type' => 'user_suspension', 'user' => 'User suspended: spam@example.com', 'time' => '10 minutes ago'],
            ['type' => 'system_update', 'user' => 'System settings updated', 'time' => '1 hour ago'],
        ];
    }

    /**
     * Get system health status
     */
    private function getSystemHealth()
    {
        return [
            'server_status' => 'healthy', // This could be more detailed
            'database_status' => $this->checkDatabaseHealth(),
            'cache_status' => $this->checkCacheHealth(),
            'storage_usage' => $this->checkStorageHealth(),
            'memory_usage' => $this->getMemoryUsage(),
            'cpu_usage' => $this->getCpuUsage(),
            'uptime' => $this->getUptime(),
            'last_backup' => $this->getLastBackup(),
            'queue' => $this->checkQueueHealth(),
        ];
    }

    /**
     * Get popular lessons
     */
    private function getPopularLessons()
    {
        // This would typically come from lesson view tracking
        return [
            ['title' => 'Basic Mathematics', 'views' => 1250, 'level' => 'Primary 1'],
            ['title' => 'English Alphabet', 'views' => 980, 'level' => 'Primary 1'],
            ['title' => 'Algebra Basics', 'views' => 750, 'level' => 'JHS 1'],
            ['title' => 'Grammar Rules', 'views' => 650, 'level' => 'JHS 1'],
        ];
    }

    /**
     * Get user activities
     */
    private function getUserActivities($userId)
    {
        // This would typically come from an activity log table
        return [
            ['action' => 'login', 'description' => 'User logged in', 'ip' => '192.168.1.1', 'created_at' => now()->subHours(2)],
            ['action' => 'lesson_view', 'description' => 'Viewed lesson: Basic Mathematics', 'ip' => '192.168.1.1', 'created_at' => now()->subHours(3)],
            ['action' => 'profile_update', 'description' => 'Updated profile information', 'ip' => '192.168.1.1', 'created_at' => now()->subDays(1)],
        ];
    }

    /**
     * Get user lesson progress
     */
    private function getUserLessonProgress($userId)
    {
        // This would typically come from a lesson progress table
        return [
            'completed_lessons' => 15,
            'total_lessons' => 50,
            'completion_rate' => 30,
            'favorite_subject' => 'Mathematics',
            'total_watch_time' => '5 hours 30 minutes'
        ];
    }

    /**
     * Get content statistics
     */
    private function getContentStats()
    {
        // This would come from your lessons/content tables
        return [
            'total_lessons' => 150,
            'total_subjects' => 8,
            'total_instructors' => 12,
            'total_watch_time' => '2,500 hours',
            'most_popular_subject' => 'Mathematics',
            'newest_lessons' => 5,
        ];
    }

    /**
     * Get recent content activities
     */
    private function getRecentContentActivities()
    {
        return [
            ['action' => 'lesson_added', 'title' => 'Advanced Calculus', 'instructor' => 'Dr. Frimpong', 'time' => '1 hour ago'],
            ['action' => 'lesson_updated', 'title' => 'Basic Mathematics', 'instructor' => 'Mrs. Asante', 'time' => '3 hours ago'],
            ['action' => 'instructor_added', 'title' => 'New instructor: Prof. Adjei', 'instructor' => 'System', 'time' => '1 day ago'],
        ];
    }

    /**
     * Get analytics data
     */
    private function getAnalyticsData()
    {
        return [
            'user_registrations' => $this->getUserRegistrationData(),
            'lesson_views' => $this->getLessonViewData(),
            'popular_subjects' => $this->getPopularSubjectsData(),
            'user_engagement' => $this->getUserEngagementData(),
        ];
    }

    /**
     * Get user registration data for charts
     */
    private function getUserRegistrationData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = User::whereDate('created_at', $date)->count();
            $data[] = [
                'date' => $date->format('M d'),
                'count' => $count
            ];
        }
        return $data;
    }

    /**
     * Get lesson view data
     */
    private function getLessonViewData()
    {
        // This would come from lesson view tracking
        return [
            ['subject' => 'Mathematics', 'views' => 2500],
            ['subject' => 'English', 'views' => 2100],
            ['subject' => 'Science', 'views' => 1800],
            ['subject' => 'Social Studies', 'views' => 1200],
        ];
    }

    /**
     * Get popular subjects data
     */
    private function getPopularSubjectsData()
    {
        return [
            ['name' => 'Mathematics', 'percentage' => 35],
            ['name' => 'English', 'percentage' => 28],
            ['name' => 'Science', 'percentage' => 22],
            ['name' => 'Social Studies', 'percentage' => 15],
        ];
    }

    /**
     * Get user engagement data
     */
    private function getUserEngagementData()
    {
        return [
            'daily_active_users' => 450,
            'weekly_active_users' => 1200,
            'monthly_active_users' => 3500,
            'average_session_duration' => '25 minutes',
        ];
    }

    /**
     * Get security logs
     */
    private function getSecurityLogs()
    {
        // This would come from your security log files or database
        return [
            ['level' => 'warning', 'message' => 'Multiple failed login attempts', 'ip' => '192.168.1.100', 'time' => '5 minutes ago'],
            ['level' => 'info', 'message' => 'Admin login successful', 'ip' => '192.168.1.1', 'time' => '10 minutes ago'],
            ['level' => 'warning', 'message' => 'Suspicious user activity detected', 'ip' => '10.0.0.50', 'time' => '1 hour ago'],
        ];
    }

    /**
     * Get failed login attempts
     */
    private function getFailedLoginAttempts()
    {
        return [
            ['email' => 'hacker@example.com', 'ip' => '192.168.1.100', 'attempts' => 5, 'last_attempt' => '2 minutes ago'],
            ['email' => 'test@test.com', 'ip' => '10.0.0.50', 'attempts' => 3, 'last_attempt' => '1 hour ago'],
        ];
    }

    /**
     * Get suspicious activities
     */
    private function getSuspiciousActivities()
    {
        return [
            ['type' => 'Multiple IPs', 'user' => 'user@example.com', 'description' => 'Login from 3 different countries', 'risk' => 'high'],
            ['type' => 'Rapid requests', 'user' => 'bot@example.com', 'description' => '100+ requests in 1 minute', 'risk' => 'medium'],
        ];
    }

    /**
     * Get system settings
     */
    private function getSystemSettings()
    {
        return [
            'site_name' => Cache::get('setting.site_name', 'DigiLearn'),
            'maintenance_mode' => Cache::get('setting.maintenance_mode', false),
            'registration_enabled' => Cache::get('setting.registration_enabled', true),
            'max_login_attempts' => Cache::get('setting.max_login_attempts', 5),
        ];
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }

    /**
     * Check cache health
     */
    private function checkCacheHealth()
    {
        try {
            Cache::put('health_check', 'test', 60);
            $value = Cache::get('health_check');
            return ['status' => $value === 'test' ? 'healthy' : 'error', 'message' => 'Cache is working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Cache connection failed'];
        }
    }

    /**
     * Check storage health
     */
    private function checkStorageHealth()
    {
        $freeSpace = disk_free_space(storage_path());
        $totalSpace = disk_total_space(storage_path());
        $usedPercentage = (($totalSpace - $freeSpace) / $totalSpace) * 100;
        $usedPercentage = round($usedPercentage, 2);
        
        return [
            'status' => $usedPercentage < 90 ? 'healthy' : 'warning',
            'used_percentage' => $usedPercentage, // Now returns just the number
            'message' => "Storage {$usedPercentage}% used"
        ];
    }

    /**
     * Check queue health
     */
    private function checkQueueHealth()
    {
        // This is a basic check - you might want to implement more sophisticated queue monitoring
        return ['status' => 'healthy', 'message' => 'Queue system operational'];
    }

    /**
     * Export data to CSV
     */
    private function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}_" . date('Y-m-d') . ".csv\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()->toArray()));
            }
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($file, $row->toArray());
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export data to JSON
     */
    private function exportToJson($data, $filename)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}_" . date('Y-m-d') . ".json\"",
        ];

        return response()->json($data, 200, $headers);
    }

    /**
     * Get memory usage percentage
     */
    private function getMemoryUsage()
    {
        // Linux only: get memory info from /proc/meminfo
        if (file_exists('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+) kB/', $meminfo, $total);
            preg_match('/MemAvailable:\s+(\d+) kB/', $meminfo, $available);

            if ($total && $available) {
                $used = $total[1] - $available[1];
                $percent = ($used / $total[1]) * 100;
                return round($percent, 2) . '%';
            }
        }
        return 'N/A';
    }

    /**
     * Get CPU usage percentage
     */
    private function getCpuUsage()
    {
        $stat1 = file_get_contents('/proc/stat');
        usleep(100000); // 0.1 second
        $stat2 = file_get_contents('/proc/stat');

        $cpu1 = explode(" ", preg_replace("!cpu +!", "", explode("\n", $stat1)[0]));
        $cpu2 = explode(" ", preg_replace("!cpu +!", "", explode("\n", $stat2)[0]));

        $dif = array_map(function($a, $b) { return $b - $a; }, $cpu1, $cpu2);
        $total = array_sum($dif);
        $cpu = $total > 0 ? (1 - ($dif[3] / $total)) * 100 : 0;

        return round($cpu, 2) . '%';
    }

    /**
     * Get system uptime
     */
    private function getUptime()
    {
        if (file_exists('/proc/uptime')) {
            $uptime = (int)explode(' ', file_get_contents('/proc/uptime'))[0];
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            return "{$days}d {$hours}h {$minutes}m";
        }
        return 'N/A';
    }

    /**
     * Get last backup time
     */
    private function getLastBackup()
    {
        $backupDir = storage_path('app/backups');
        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/*');
            if ($files) {
                $lastBackup = max(array_map('filemtime', $files));
                return date('Y-m-d H:i:s', $lastBackup);
            }
        }
        return 'No backup found';
    }
}
