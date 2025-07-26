<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\SuperuserRecoveryCode;
use App\Models\User;
use App\Models\WebsiteLockSetting;
use App\Models\VirtualClass; // Import VirtualClass model
use App\Notifications\ClassStartedNotification; // Import notification
use Illuminate\Support\Facades\Notification; // Import Notification facade
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Http\Controllers\DashboardController; // Import DashboardController to access getLevelGroups
use App\Models\Video; // Import the Video model
use App\Models\Quiz; // Import the Quiz model
use App\Models\Document; // Import the Document model
use Illuminate\Support\Facades\Storage; // For file uploads

class AdminController extends Controller
{
    public function toggleLock(Request $request)
    {
        $lockSetting = WebsiteLockSetting::firstOrCreate();
        $lockSetting->is_locked = !$lockSetting->is_locked;
        $lockSetting->save();

        return response()->json([
            'locked' => $lockSetting->is_locked,
            'message' => $lockSetting->is_locked
                ? 'Website locked successfully'
                : 'Website unlocked successfully'
        ]);
    }

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

        // Check if website is locked
        $websiteLocked = WebsiteLockSetting::first()->is_locked ?? false;

        Log::channel('security')->info('admin_dashboard_accessed', [
            'admin_id' => Auth::id(),
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);

        return view('admin.dashboard', compact('stats', 'recentActivities', 'systemHealth', 'popularLessons', 'websiteLocked'));
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
     * Show the form for creating a new virtual class.
     */
    public function showCreateClassForm()
    {
        // Get available grade levels from DashboardController's private method
        $dashboardController = new DashboardController();
        $levelGroups = $dashboardController->getLevelGroups();
        $gradeLevels = [];
        foreach ($levelGroups as $group) {
            foreach ($group['levels'] as $key => $level) {
                $gradeLevels[$key] = $level['title'];
            }
        }

        return view('admin.classes.create', compact('gradeLevels'));
    }

    /**
     * Store a newly created virtual class in storage.
     */
    public function createClass(Request $request)
    {
        $request->validate([
            'grade_level' => 'required|string|max:50',
            'topic' => 'nullable|string|max:255',
            'start_time' => 'required|date_format:Y-m-d\TH:i|after_or_equal:now', // Make start_time mandatory and in future/now
            'duration_minutes' => 'required|integer|min:10|max:240', // Make duration mandatory
        ]);

        $roomId = Str::random(10); // Generate unique room ID
        $startTime = Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addMinutes($request->duration_minutes);

        try {
            $virtualClass = VirtualClass::create([
                'tutor_id' => Auth::id(),
                'grade_level' => $request->grade_level,
                'room_id' => $roomId,
                'topic' => $request->topic,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_active' => true, // Set to true, status will be managed by start/end times
                'status' => 'scheduled', // Default status
            ]);

            // Notify students in the specified grade level
            $students = User::where('grade', $request->grade_level)->get();
            Notification::send($students, new ClassStartedNotification($virtualClass));

            Log::channel('security')->info('virtual_class_created', [
                'admin_id' => Auth::id(),
                'room_id' => $roomId,
                'grade_level' => $request->grade_level,
                'topic' => $request->topic,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'ip' => request()->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Virtual class created successfully! Students have been notified.');

        } catch (\Exception $e) {
            Log::error('virtual_class_creation_error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'ip' => request()->ip(),
            ]);
            return back()->with('error', 'Failed to create virtual class. Please try again.')->withInput();
        }
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
            'used_percentage' => $usedPercentage . '%', // Now returns with percentage sign
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

    public function showCredentials()
    {
        $recoveryCodes = SuperuserRecoveryCode::where('user_id', Auth::id())
            ->get()
            ->pluck('code');

        return view('admin.credentials', [
            'recoveryCodes' => $recoveryCodes
        ]);
    }

    public function updateCredentials(Request $request)
    {
        $user = User::find(Auth::id());
        $data = $request->validate([
            'email' => 'nullable|email|unique:users,email,'.$user->id,
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed'
        ]);

        // Validate current password
        if ($request->has('current_password') &&
            !Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ]);
        }

        // Update email if provided
        if ($request->filled('email')) {
            $user->email = $request->email;
        }

        // Update password if provided
        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'new_email' => $user->email
        ]);
    }

    public function generateRecoveryCodes()
    {
        $user = Auth::user();

        // Delete old codes
        SuperuserRecoveryCode::where('user_id', $user->id)->delete();

        // Generate new codes
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $code = Str::random(8).'-'.Str::random(8);
            SuperuserRecoveryCode::create([
                'user_id' => $user->id,
                'code' => $code
            ]);
            $codes[] = $code;
        }

        return response()->json(['success' => true]);
    }

    // Content Management - Videos
    public function indexVideos(Request $request)
    {
        $query = Video::with('uploader');

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('grade_level') && $request->grade_level != '') {
            $query->where('grade_level', $request->grade_level);
        }

        if ($request->has('is_featured') && $request->is_featured != '') {
            $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('upload_date') && $request->upload_date != '') {
            $query->whereDate('created_at', $request->upload_date);
        }

        $videos = $query->orderBy('created_at', 'desc')->paginate(10);

        $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3']; // Example grades

        // Video statistics
        $totalVideos = Video::count();
        $mostWatchedVideo = Video::orderBy('views', 'desc')->first();
        $averageDurationSeconds = Video::avg('duration_seconds');
        $averageDuration = $averageDurationSeconds ? gmdate("H:i:s", $averageDurationSeconds) : '00:00:00';

        return view('admin.content.videos.index', compact('videos', 'gradeLevels', 'totalVideos', 'mostWatchedVideo', 'averageDuration'));
    }

    public function storeVideo(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_file' => 'required|file|mimes:mp4,mov,avi,wmv|max:500000', // Max 500MB
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            'grade_level' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        $videoPath = $request->file('video_file')->store('videos', 'public');
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail_file')) {
            $thumbnailPath = $request->file('thumbnail_file')->store('thumbnails', 'public');
        }

        // Simulate duration calculation (in a real app, you'd use a video processing library)
        $durationSeconds = rand(60, 3600); // Placeholder duration

        Video::create([
            'title' => $request->title,
            'video_path' => $videoPath,
            'thumbnail_path' => $thumbnailPath,
            'grade_level' => $request->grade_level,
            'duration_seconds' => $durationSeconds,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
            'uploaded_by' => Auth::id(),
            'views' => 0, // Initialize views to 0
        ]);

        return redirect()->route('admin.content.videos.index')->with('success', 'Video uploaded successfully!');
    }

    public function editVideo(Video $video)
    {
        $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3'];
        return response()->json($video); // Return JSON for modal
    }

    public function updateVideo(Request $request, Video $video)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:500000',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'grade_level' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        if ($request->hasFile('video_file')) {
            Storage::disk('public')->delete($video->video_path);
            $video->video_path = $request->file('video_file')->store('videos', 'public');
        }

        if ($request->hasFile('thumbnail_file')) {
            if ($video->thumbnail_path) {
                Storage::disk('public')->delete($video->thumbnail_path);
            }
            $video->thumbnail_path = $request->file('thumbnail_file')->store('thumbnails', 'public');
        }

        $video->update([
            'title' => $request->title,
            'grade_level' => $request->grade_level,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()->route('admin.content.videos.index')->with('success', 'Video updated successfully!');
    }

    public function destroyVideo(Video $video)
    {
        Storage::disk('public')->delete($video->video_path);
        if ($video->thumbnail_path) {
            Storage::disk('public')->delete($video->thumbnail_path);
        }
        $video->delete();

        return redirect()->route('admin.content.videos.index')->with('success', 'Video deleted successfully!');
    }

    public function toggleVideoFeature(Video $video)
    {
        $video->is_featured = !$video->is_featured;
        $video->save();

        return back()->with('success', 'Video feature status updated.');
    }

    // Content Management - Quizzes
    public function indexQuizzes(Request $request)
    {
        $query = Quiz::with('uploader', 'video');

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
        }

        if ($request->has('grade_level') && $request->grade_level != '') {
            $query->where('grade_level', $request->grade_level);
        }

        if ($request->has('video_id') && $request->video_id != '') {
            $query->where('video_id', $request->video_id);
        }

        if ($request->has('uploaded_by') && $request->uploaded_by != '') {
            $query->where('uploaded_by', $request->uploaded_by);
        }

        if ($request->has('is_featured') && $request->is_featured != '') {
            $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('upload_date') && $request->upload_date != '') {
            $query->whereDate('created_at', $request->upload_date);
        }

        $quizzes = $query->orderBy('created_at', 'desc')->paginate(10);

        $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3'];
        $videos = Video::select('id', 'title')->get(); // For video course filter
        $uploaders = User::whereHas('quizzes')->select('id', 'name')->get(); // Users who have uploaded quizzes

        return view('admin.content.quizzes.index', compact('quizzes', 'gradeLevels', 'videos', 'uploaders'));
    }

    public function storeQuiz(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'video_id' => 'nullable|exists:videos,id',
            'grade_level' => 'nullable|string|max:255',
            'quiz_data' => 'nullable|string', // Or 'json' if you enforce JSON structure
            'is_featured' => 'boolean',
        ]);

        Quiz::create([
            'title' => $request->title,
            'subject' => $request->subject,
            'video_id' => $request->video_id,
            'grade_level' => $request->grade_level,
            'uploaded_by' => Auth::id(),
            'quiz_data' => $request->quiz_data,
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()->route('admin.content.quizzes.index')->with('success', 'Quiz uploaded successfully!');
    }

    public function editQuiz(Quiz $quiz)
    {
        $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3'];
        $videos = Video::select('id', 'title')->get();
        return view('admin.content.quizzes.edit', compact('quiz', 'gradeLevels', 'videos'));
    }

    public function updateQuiz(Request $request, Quiz $quiz)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'video_id' => 'nullable|exists:videos,id',
            'grade_level' => 'nullable|string|max:255',
            'quiz_data' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        $quiz->update([
            'title' => $request->title,
            'subject' => $request->subject,
            'video_id' => $request->video_id,
            'grade_level' => $request->grade_level,
            'quiz_data' => $request->quiz_data,
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()->route('admin.content.quizzes.index')->with('success', 'Quiz updated successfully!');
    }

    public function destroyQuiz(Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('admin.content.quizzes.index')->with('success', 'Quiz deleted successfully!');
    }

    public function toggleQuizFeature(Quiz $quiz)
    {
        $quiz->is_featured = !$quiz->is_featured;
        $quiz->save();

        return back()->with('success', 'Quiz feature status updated.');
    }

    // Content Management - Documents
    public function indexDocuments(Request $request)
    {
        $query = Document::with('uploader');

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('grade_level') && $request->grade_level != '') {
            $query->where('grade_level', $request->grade_level);
        }

        if ($request->has('uploaded_by') && $request->uploaded_by != '') {
            $query->where('uploaded_by', $request->uploaded_by);
        }

        if ($request->has('is_featured') && $request->is_featured != '') {
            $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('upload_date') && $request->upload_date != '') {
            $query->whereDate('created_at', $request->upload_date);
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(10);

        $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3'];
        $uploaders = User::whereHas('documents')->select('id', 'name')->get(); // Users who have uploaded documents

        return view('admin.content.documents.index', compact('documents', 'gradeLevels', 'uploaders'));
    }

    public function storeDocument(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480', // Max 20MB
            'grade_level' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        $filePath = $request->file('document_file')->store('documents', 'public');

        Document::create([
            'title' => $request->title,
            'file_path' => $filePath,
            'grade_level' => $request->grade_level,
            'description' => $request->description,
            'uploaded_by' => Auth::id(),
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()->route('admin.content.documents.index')->with('success', 'Document uploaded successfully!');
    }

    public function editDocument(Document $document)
    {
        $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3'];
        return response()->json($document); // Return JSON for modal
    }

    public function updateDocument(Request $request, Document $document)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            'grade_level' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        if ($request->hasFile('document_file')) {
            Storage::disk('public')->delete($document->file_path);
            $document->file_path = $request->file('document_file')->store('documents', 'public');
        }

        $document->update([
            'title' => $request->title,
            'grade_level' => $request->grade_level,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()->route('admin.content.documents.index')->with('success', 'Document updated successfully!');
    }

    public function destroyDocument(Document $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('admin.content.documents.index')->with('success', 'Document deleted successfully!');
    }

    public function toggleDocumentFeature(Document $document)
    {
        $document->is_featured = !$document->is_featured;
        $document->save();

        return back()->with('success', 'Document feature status updated.');
    }
}
