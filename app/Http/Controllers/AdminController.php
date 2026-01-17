<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\StorageMonitoringSetting;
use App\Models\StorageAlert;
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
use App\Models\ActivityLog;
use App\Models\UserSubscription; // Import the UserSubscription model
use App\Models\PricingPlan; // Import the PricingPlan model
use App\Models\ProgressionStandard; // Import the ProgressionStandard model
use App\Models\UserProgress; // Import the UserProgress model
use App\Models\QuizAttempt; // Import the QuizAttempt model
use App\Models\QuizRating; // Import the QuizRating model
use App\Models\Subject;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage; // For file uploads
use App\Services\NotificationService;
use App\Services\UserActivityService;

class AdminController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function toggleLock(Request $request)
    {
        // Check recovery codes before locking
        if (!$request->has('confirm_lock')) {
            $superusers = User::where('is_superuser', true)->get();
            $hasValidRecoveryCodes = false;

            foreach ($superusers as $superuser) {
                $recoveryCodesCount = SuperuserRecoveryCode::where('user_id', $superuser->id)->count();
                if ($recoveryCodesCount > 0) {
                    $hasValidRecoveryCodes = true;
                    break;
                }
            }

            if (!$hasValidRecoveryCodes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot lock website: No valid recovery codes found for superusers. Please generate recovery codes first.',
                    'requires_recovery_codes' => true
                ], 400);
            }
        }

        $lockSetting = WebsiteLockSetting::firstOrCreate();
        $lockSetting->is_locked = !$lockSetting->is_locked;
        $lockSetting->save();

        Log::channel('security')->info('website_lock_toggled', [
            'admin_id' => Auth::id(),
            'locked' => $lockSetting->is_locked,
            'ip' => get_client_ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);

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
        Log::info('Admin dashboard accessed', [
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString()
        ]);

        // Get dashboard statistics
        $stats = $this->getDashboardStats();

        // Get recent activities
        $recentActivities = $this->getRecentActivities();

        // Get system health
        $systemHealth = $this->getSystemHealth();

        // Get popular lessons
        $popularLessons = $this->getPopularLessons();

        // Get revenue data
        $revenueData = $this->getRevenueData();

        // Get revenue trends
        $revenueTrends = $this->getRevenueTrends();

        // Get top performing plans
        $topPlans = $this->getTopPerformingPlans();

        // Get subscription analytics
        $subscriptionAnalytics = $this->getSubscriptionAnalytics();

        // Check if website is locked
        $websiteLocked = WebsiteLockSetting::first()->is_locked ?? false;

        Log::channel('security')->info('admin_dashboard_accessed', [
            'admin_id' => Auth::id(),
            'ip' => get_client_ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ]);

        return view('admin.dashboard', compact('stats', 'recentActivities', 'systemHealth', 'popularLessons', 'revenueData', 'revenueTrends', 'topPlans', 'subscriptionAnalytics', 'websiteLocked'));
    }

    /**
     * Get dashboard statistics via AJAX
     */
    public function getDashboardStatsAjax()
    {
        try {
            $stats = $this->getDashboardStats();

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard stats AJAX error', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
                'ip' => get_client_ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard statistics'
            ], 500);
        }
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

        // Get distinct grades for filter
        $levels = User::distinct('grade')->pluck('grade')->filter()->toArray();

        return view('admin.users.index', compact('users', 'userStats', 'levels'));
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
            'ip' => get_client_ip(),
            'user_agent' => $request->userAgent(),
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
            'ip' => get_client_ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ]);

        return redirect()->back()->with('success', 'User unsuspended successfully.');
    }

    /**
     * Toggle user status (suspend/unsuspend) via AJAX
     */
    public function toggleUserStatus(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $reason = $request->input('reason');

            // Log the attempt
            Log::info('Toggle user status attempt', [
                'admin_id' => Auth::id(),
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
                'current_status' => $user->isSuspended() ? 'suspended' : 'active',
                'action' => $user->isSuspended() ? 'unsuspend' : 'suspend',
                'reason' => $reason,
                'ip' => get_client_ip(),
                'timestamp' => now()->toISOString()
            ]);

            if ($user->isSuspended()) {
                // Unsuspend the user
                $user->update([
                    'suspended_at' => null,
                    'suspension_reason' => null
                ]);

                Log::channel('security')->info('user_unsuspended_via_toggle', [
                    'admin_id' => Auth::id(),
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'ip' => get_client_ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toISOString()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'User unsuspended successfully.',
                    'action' => 'unsuspended'
                ]);
            } else {
                // Suspend the user
                $user->update([
                    'suspended_at' => now(),
                    'suspension_reason' => $reason ?: 'Suspended by admin'
                ]);

                Log::channel('security')->warning('user_suspended_via_toggle', [
                    'admin_id' => Auth::id(),
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'reason' => $reason ?: 'Suspended by admin',
                    'ip' => get_client_ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toISOString()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'User suspended successfully.',
                    'action' => 'suspended'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('toggle_user_status_error', [
                'admin_id' => Auth::id(),
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => get_client_ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating user status: ' . $e->getMessage()
            ], 500);
        }
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
            'ip' => get_client_ip(),
            'user_agent' => $request->userAgent(),
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
     * Show user activities page
     */
    public function activities()
    {
        return view('admin.activities.index');
    }

    /**
     * Get security data via AJAX
     */
    public function getSecurityDataAjax()
    {
        try {
            // Get security logs
            $securityLogs = $this->getSecurityLogs();

            // Get failed login attempts
            $failedLogins = $this->getFailedLoginAttempts();

            // Get suspicious activities
            $suspiciousActivities = $this->getSuspiciousActivities();

            // Get statistics
            $stats = [
                'total_activities' => is_array($securityLogs) ? count($securityLogs) : $securityLogs->count(),
                'failed_logins' => is_array($failedLogins) ? count($failedLogins) : $failedLogins->count(),
                'suspicious_activities' => is_array($suspiciousActivities) ? count($suspiciousActivities) : $suspiciousActivities->count(),
                'active_threats' => is_array($suspiciousActivities)
                    ? collect($suspiciousActivities)->whereIn('risk', ['high', 'critical'])->count()
                    : $suspiciousActivities->whereIn('risk', ['high', 'critical'])->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'securityLogs' => $securityLogs,
                    'failedLogins' => $failedLogins,
                    'suspiciousActivities' => $suspiciousActivities,
                    'stats' => $stats,
                ],
                'timestamp' => now()->toISOString(),
                'formatted_timestamp' => now()->format('M d, Y H:i')
            ]);
        } catch (\Exception $e) {
            Log::error('Security data AJAX error', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
                'ip' => get_client_ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load security data'
            ], 500);
        }
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
            'ip' => get_client_ip(),
            'user_agent' => $request->userAgent(),
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
            'ip' => get_client_ip(),
            'user_agent' => $request->userAgent(),
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
                'ip' => get_client_ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Virtual class created successfully! Students have been notified.');

        } catch (\Exception $e) {
            Log::error('virtual_class_creation_error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'ip' => get_client_ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);
            return back()->with('error', 'Failed to create virtual class. Please try again.')->withInput();
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        // Count online users using Redis
        $onlineUsers = 0;
        try {
            // Check if Redis is available
            if (class_exists('\Illuminate\Support\Facades\Redis')) {
                $keys = \Illuminate\Support\Facades\Redis::keys('user:*:last_seen');
                $onlineUsers = count($keys);
            } else {
                throw new \Exception('Redis facade not available');
            }
        } catch (\Exception $e) {
            // Fallback to database if Redis fails
            $onlineUsers = User::where('last_activity_at', '>=', now()->subMinutes(5))->count();
        }

        return [
            'total_users' => User::count(),
            'active_users' => User::whereNull('suspended_at')->count(),
            'online_users' => $onlineUsers,
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'suspended_users' => User::whereNotNull('suspended_at')->count(),
            'total_lessons' => Video::count(), // Should be replaced with actual query
            'total_subjects' => Subject::count(),  // Should be replaced with actual query
            'subscription_plans' => $this->getSubscriptionPlansData(),
            'cookie_consents' => $this->getCookieConsentStats(),
        ];
    }

    /**
     * Get subscription plans data for dashboard badges
     */
    private function getSubscriptionPlansData()
    {
        $plans = PricingPlan::active()->ordered()->get();

        if ($plans->isEmpty()) {
            // Return sample data if no plans exist in database
            return [
                [
                    'name' => 'Essential',
                    'subscribers' => 180,
                    'revenue' => 9000.00,
                    'color' => 'blue'
                ],
                [
                    'name' => 'Extra Tuition',
                    'subscribers' => 200,
                    'revenue' => 40000.00,
                    'color' => 'green'
                ],
                [
                    'name' => 'Home School',
                    'subscribers' => 70,
                    'revenue' => 14000.00,
                    'color' => 'purple'
                ]
            ];
        }

        $colors = ['blue', 'green', 'purple', 'red', 'yellow', 'indigo', 'pink'];

        return $plans->map(function ($plan, $index) use ($colors) {
            $activeSubscriptions = $plan->activeSubscriptions()->count();
            $totalRevenue = $plan->subscriptions()
                ->where('status', 'active')
                ->sum('amount_paid');

            return [
                'name' => $plan->name,
                'subscribers' => $activeSubscriptions,
                'revenue' => (float) $totalRevenue,
                'color' => $colors[$index % count($colors)]
            ];
        })->toArray();
    }

    /**
     * Get cookie consent statistics for dashboard
     */
    private function getCookieConsentStats()
    {
        try {
            $cookieStats = \App\Models\CookieConsent::getConsentStats();

            return [
                'total_consents' => $cookieStats['total_consents'] ?? 0,
                'recent_consents' => $cookieStats['recent_consents'] ?? 0,
                'unique_ips' => $cookieStats['unique_ips'] ?? 0,
                'analytics_accepted' => ($cookieStats['consent_types']['analytics'] ?? 0),
                'consent_rate' => $cookieStats['total_consents'] > 0
                    ? round(($cookieStats['total_consents'] / User::count()) * 100, 1)
                    : 0,
            ];
        } catch (\Exception $e) {
            // Return default values if there's an error
            return [
                'total_consents' => 0,
                'recent_consents' => 0,
                'unique_ips' => 0,
                'analytics_accepted' => 0,
                'consent_rate' => 0,
            ];
        }
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        return UserActivityService::getRecentActivities(10);
    }

    /**
     * API endpoint for paginated user activities
     */
    public function getUserActivitiesApi(Request $request)
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:10|max:100',
            'user_id' => 'nullable|exists:users,id',
            'type' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'search' => 'nullable|string|max:255',
        ]);

        $filters = $request->only([
            'user_id', 'type', 'ip_address', 'date_from', 'date_to', 'search'
        ]);

        $perPage = $request->input('per_page', 20);

        try {
            $activities = UserActivityService::getPaginatedActivities($filters, $perPage);

            // Add formatted timestamps and user info
            $activities->getCollection()->transform(function ($activity) {
                $activity->formatted_created_at = $activity->created_at->format('M d, Y H:i:s');
                $activity->time_ago = $activity->created_at->diffForHumans();
                $activity->user_name = $activity->user->name ?? 'Unknown';
                $activity->user_email = $activity->user->email ?? '';
                return $activity;
            });

            return response()->json([
                'success' => true,
                'data' => $activities,
                'stats' => UserActivityService::getActivityStats(
                    $filters['user_id'] ?? null,
                    30 // Last 30 days
                ),
            ]);

        } catch (\Exception $e) {
            Log::error('User activities API error', [
                'error' => $e->getMessage(),
                'filters' => $filters,
                'admin_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load user activities',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get activity statistics for dashboard
     */
    public function getActivityStatsApi(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        $userId = $request->input('user_id');
        $days = $request->input('days', 30);

        try {
            $stats = UserActivityService::getActivityStats($userId, $days);

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Activity stats API error', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'days' => $days,
                'admin_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load activity statistics',
            ], 500);
        }
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
        // Get real activity logs from database
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return $logs->map(function ($log) {
            return [
                'level' => $log->level,
                'message' => $log->description,
                'ip' => $log->ip_address,
                'time' => $log->created_at->diffForHumans(),
                'user' => $log->user ? $log->user->name : 'System',
                'action' => $log->action,
            ];
        })->toArray();
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
        $suspiciousActivities = [];

        // Check for users with multiple IP addresses in short time
        $multiIpUsers = ActivityLog::where('action', 'login')
            ->where('created_at', '>=', now()->subHours(24))
            ->selectRaw('user_id, user_email, COUNT(DISTINCT ip_address) as ip_count')
            ->groupBy('user_id', 'user_email')
            ->having('ip_count', '>', 2)
            ->get();

        foreach ($multiIpUsers as $user) {
            $suspiciousActivities[] = [
                'type' => 'Multiple IPs',
                'user' => $user->user_email ?? 'Unknown',
                'description' => "Login from {$user->ip_count} different IP addresses in 24 hours",
                'risk' => 'high',
            ];
        }

        // Check for rapid failed login attempts
        $rapidAttempts = ActivityLog::where('action', 'failed_login')
            ->where('created_at', '>=', now()->subHours(1))
            ->selectRaw('ip_address, COUNT(*) as attempt_count, MIN(created_at) as first_attempt, MAX(created_at) as last_attempt')
            ->groupBy('ip_address')
            ->having('attempt_count', '>', 10)
            ->get();

        foreach ($rapidAttempts as $attempt) {
            $duration = $attempt->first_attempt->diffInMinutes($attempt->last_attempt);
            $rate = $duration > 0 ? $attempt->attempt_count / $duration : $attempt->attempt_count;

            if ($rate > 5) { // More than 5 attempts per minute
                $suspiciousActivities[] = [
                    'type' => 'Rapid Failed Logins',
                    'user' => $attempt->ip_address,
                    'description' => "{$attempt->attempt_count} failed login attempts in {$duration} minutes",
                    'risk' => 'high',
                ];
            }
        }

        // Check for unusual login times (if we had user timezone data)
        // This would require additional user profile data

        return array_slice($suspiciousActivities, 0, 10); // Limit to 10 most recent
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
        try {
            // Get default storage monitoring settings
            $settings = StorageMonitoringSetting::active()->default()->first();

            if (!$settings) {
                // Fallback to basic check if no settings configured
                return $this->basicStorageCheck();
            }

            $monitoredPaths = $settings->monitored_paths ?? [storage_path()];
            $totalUsage = 0;
            $totalCapacity = 0;
            $issues = [];

            foreach ($monitoredPaths as $path) {
                $storageInfo = $this->getStorageInfo($path);

                if ($storageInfo) {
                    $totalUsage += $storageInfo['used_space_bytes'];
                    $totalCapacity += $storageInfo['total_space_bytes'];

                    // Check for alerts
                    $alertType = $settings->getAlertLevel($storageInfo['usage_percentage']);
                    if ($alertType) {
                        // Check throttling
                        if (!$settings->shouldThrottleAlert($alertType, $path)) {
                            $this->sendStorageAlert($settings, $alertType, $path, $storageInfo);
                            $issues[] = "Path {$path}: {$alertType} alert triggered";
                        }
                    }
                }
            }

            if ($totalCapacity > 0) {
                $overallUsage = round(($totalUsage / $totalCapacity) * 100, 2);
                $status = $this->getStorageStatus($overallUsage);

                return [
                    'status' => $status,
                    'used_percentage' => $overallUsage . '%',
                    'message' => "Storage {$overallUsage}% used" . (!empty($issues) ? ' - ' . implode(', ', $issues) : ''),
                    'total_used' => $this->formatBytes($totalUsage),
                    'total_capacity' => $this->formatBytes($totalCapacity),
                    'issues' => $issues
                ];
            }

            return $this->basicStorageCheck();

        } catch (\Exception $e) {
            Log::error('Storage health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'used_percentage' => 'N/A',
                'message' => 'Unable to check storage health'
            ];
        }
    }

    /**
     * Basic storage check fallback
     */
    private function basicStorageCheck()
    {
        $freeSpace = disk_free_space(storage_path());
        $totalSpace = disk_total_space(storage_path());
        $usedPercentage = (($totalSpace - $freeSpace) / $totalSpace) * 100;
        $usedPercentage = round($usedPercentage, 2);

        // Legacy alert check
        if ($usedPercentage >= 85) {
            $this->sendStorageAlertNotification($usedPercentage, $this->formatBytes($totalSpace - $freeSpace), $this->formatBytes($totalSpace));
        }

        return [
            'status' => $usedPercentage < 90 ? 'healthy' : 'warning',
            'used_percentage' => $usedPercentage . '%',
            'message' => "Storage {$usedPercentage}% used"
        ];
    }

    /**
     * Get storage information for a path
     */
    private function getStorageInfo(string $path): ?array
    {
        try {
            $totalSpace = disk_total_space($path);
            $freeSpace = disk_free_space($path);

            if ($totalSpace === false || $freeSpace === false || $totalSpace == 0) {
                return null;
            }

            $usedSpace = $totalSpace - $freeSpace;
            $usagePercentage = round(($usedSpace / $totalSpace) * 100, 2);

            return [
                'total_space_bytes' => $totalSpace,
                'used_space_bytes' => $usedSpace,
                'free_space_bytes' => $freeSpace,
                'usage_percentage' => $usagePercentage
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get storage status based on usage percentage
     */
    private function getStorageStatus(float $percentage): string
    {
        if ($percentage >= 98) return 'critical';
        if ($percentage >= 95) return 'warning';
        if ($percentage >= 90) return 'caution';
        return 'healthy';
    }

    /**
     * Send storage alert using new system
     */
    private function sendStorageAlert(\App\Models\StorageMonitoringSetting $settings, string $alertType, string $path, array $storageInfo)
    {
        try {
            $adminUsers = User::where('is_admin', true)->get();

            if ($adminUsers->isEmpty()) {
                Log::warning('No admin users found to send storage alert');
                return;
            }

            $notification = new \App\Notifications\StorageAlertNotification(
                $storageInfo['usage_percentage'],
                $this->formatBytes($storageInfo['used_space_bytes']),
                $this->formatBytes($storageInfo['total_space_bytes']),
                $alertType,
                $path
            );

            $this->notificationService->sendToUsers($adminUsers, $notification, ['database', 'mail']);

            // Record the alert
            StorageAlert::create([
                'alert_type' => $alertType,
                'path' => $path,
                'usage_percentage' => $storageInfo['usage_percentage'],
                'used_space_bytes' => $storageInfo['used_space_bytes'],
                'total_space_bytes' => $storageInfo['total_space_bytes'],
                'admin_users_notified' => $adminUsers->pluck('id')->toArray(),
                'alert_sent_at' => now()
            ]);

            Log::info("Storage alert sent: {$alertType} for {$path}", [
                'usage_percentage' => $storageInfo['usage_percentage'],
                'admin_count' => $adminUsers->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send storage alert', [
                'alert_type' => $alertType,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
        }
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
     * Send storage alert notification to all admin users
     */
    private function sendStorageAlertNotification(float $usagePercentage, string $usedSpace, string $totalSpace)
    {
        try {
            // Get all admin users
            $adminUsers = User::where('is_admin', true)->get();

            if ($adminUsers->isEmpty()) {
                Log::warning('No admin users found to send storage alert notification');
                return;
            }

            // Create and send notification
            $notification = new \App\Notifications\StorageAlertNotification(
                $usagePercentage,
                $usedSpace,
                $totalSpace
            );

            $this->notificationService->sendToUsers($adminUsers, $notification, ['database', 'mail']);

            Log::info('Storage alert notification sent', [
                'usage_percentage' => $usagePercentage,
                'used_space' => $usedSpace,
                'total_space' => $totalSpace,
                'admin_count' => $adminUsers->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send storage alert notification', [
                'error' => $e->getMessage(),
                'usage_percentage' => $usagePercentage
            ]);
        }
    }

    /**
     * Format bytes into human readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
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
        $query = Video::with(['uploader:id,name,email,avatar']);

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('grade_level') && $request->grade_level != '') {
            $query->where('grade_level', $request->grade_level);
        }

        if ($request->has('difficulty_level') && $request->difficulty_level != '') {
            $query->where('difficulty_level', $request->difficulty_level);
        }

        if ($request->has('is_featured') && $request->is_featured != '') {
            $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('upload_date') && $request->upload_date != '') {
            $query->whereDate('created_at', $request->upload_date);
        }

        $videos = $query->orderBy('created_at', 'desc')->paginate(10);

        $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3']; // Example grades
        $quizzes = Quiz::all(); // For associating quizzes

        // Get pending videos for review section
        $pendingVideos = Video::pending()
            ->with(['uploader:id,name,email'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Video statistics
        $totalVideos = Video::count();
        $pendingCount = Video::pending()->count();
        $approvedCount = Video::approved()->count();
        $rejectedCount = Video::where('status', 'rejected')->count();
        $mostWatchedVideo = Video::orderBy('views', 'desc')->first();
        $averageDurationSeconds = Video::avg('duration_seconds');
        $averageDuration = $averageDurationSeconds ? gmdate("H:i:s", $averageDurationSeconds) : '00:00:00';

        // Check for expired temp files
        $expiredVideos = Video::where('temp_expires_at', '<', now())
            ->whereNotNull('temp_file_path')
            ->count();

        return view('admin.content.videos.index', compact(
            'videos', 
            'pendingVideos', 
            'gradeLevels', 
            'totalVideos', 
            'pendingCount',
            'approvedCount', 
            'rejectedCount',
            'expiredVideos',
            'mostWatchedVideo', 
            'averageDuration', 
            'quizzes'
        ));
    }

    public function storeVideo(Request $request)
    {
        // Check temporary video limit
        $pendingCount = Video::pending()->count();
        $maxTempVideos = (int) config('services.vimeo.max_temp_videos', 10);
        
        if ($pendingCount >= $maxTempVideos) {
            return back()->withErrors([
                'video_file' => "Maximum number of pending videos ({$maxTempVideos}) reached. Please review existing videos first."
            ]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'video_file' => 'required|file|mimes:mp4,mov,avi,wmv|max:600000', // Max 600MB
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            'grade_level' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        // Store video temporarily
        $tempVideoPath = $request->file('video_file')->store('temp_videos', 'public');
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail_file')) {
            $thumbnailPath = $request->file('thumbnail_file')->store('thumbnails', 'public');
        }

        // Get file size
        $fileSize = $request->file('video_file')->getSize();

        // Calculate expiry time
        $expiryHours = (int) config('services.vimeo.temp_expiry_hours', 72);
        $tempExpiresAt = now()->addHours($expiryHours);

        // Simulate duration calculation (in a real app, you'd use a video processing library)
        $durationSeconds = rand(60, 3600); // Placeholder duration

        // Handle document upload
        $documentPath = null;
        if ($request->hasFile('document_file')) {
            $documentPath = $request->file('document_file')->store('documents', 'public');
        }

        $video = Video::create([
            'title' => $request->title,
            'video_path' => null, // Will be set after Vimeo upload
            'temp_file_path' => $tempVideoPath,
            'temp_expires_at' => $tempExpiresAt,
            'thumbnail_path' => $thumbnailPath,
            'grade_level' => $request->grade_level,
            'duration_seconds' => $durationSeconds,
            'file_size_bytes' => $fileSize,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
            'status' => 'pending', // Set to pending for review
            'uploaded_by' => Auth::id(),
            'uploader_ip' => get_client_ip(),
            'uploader_user_agent' => $request->userAgent(),
            'views' => 0,
            'document_path' => $documentPath,
        ]);

        // Handle quiz association
        if ($request->filled('quiz_id')) {
            $video->quiz_id = $request->quiz_id;
            $video->save();
        }

        // Save document path if uploaded
        if ($documentPath) {
            $video->document_path = $documentPath;
            $video->save();
        }

        return redirect()->route('admin.content.videos.index')->with('success', 'Video uploaded successfully and is pending review!');
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
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:600000',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'grade_level' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        if ($request->hasFile('video_file')) {
            if ($video->video_path) {
                Storage::disk('public')->delete($video->video_path);
            }
            $video->video_path = $request->file('video_file')->store('videos', 'public');
        }

        if ($request->hasFile('thumbnail_file')) {
            if ($video->thumbnail_path) {
                Storage::disk('public')->delete($video->thumbnail_path);
            }
            $video->thumbnail_path = $request->file('thumbnail_file')->store('thumbnails', 'public');
        }

        // Handle document upload
        if ($request->hasFile('document_file')) {
            $documentPath = $request->file('document_file')->store('documents', 'public');
            $video->document_path = $documentPath;
        }

        // Handle quiz association
        if ($request->filled('quiz_id')) {
            $video->quiz_id = $request->quiz_id;
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
        // Safely delete all associated files
        $video->deleteFiles();
        $video->delete();

        return redirect()->route('admin.content.videos.index')->with('success', 'Video deleted successfully!');
    }

    public function toggleVideoFeature(Video $video)
    {
        $video->is_featured = !$video->is_featured;
        $video->save();

        return back()->with('success', 'Video feature status updated.');
    }

    /**
     * Approve video and upload to Vimeo
     */
    public function approveVideo(Request $request, $id)
    {
        $video = Video::findOrFail($id);

        if (!$video->isPending()) {
            Log::warning('Video approval attempted on non-pending video', ['video_id' => $video->id, 'status' => $video->status]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Video is not pending approval.'], 400);
            }
            return back()->withErrors(['error' => 'Video is not pending approval.']);
        }

        try {
            Log::info('Starting video approval process', ['video_id' => $video->id, 'title' => $video->title]);

            // Set status to processing
            $video->update([
                'status' => 'processing',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $request->input('review_notes')
            ]);

            // Check if temporary file exists and hasn't expired
            if (!$video->temp_file_path || $video->isTempExpired()) {
                Log::error('Temporary file issue', [
                    'video_id' => $video->id,
                    'temp_file_path' => $video->temp_file_path,
                    'is_expired' => $video->isTempExpired()
                ]);
                
                $video->update([
                    'status' => 'rejected',
                    'review_notes' => 'Temporary file expired or not found'
                ]);
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'error' => 'Temporary video file has expired or not found.'], 400);
                }
                return back()->withErrors(['error' => 'Temporary video file has expired or not found.']);
            }

            $tempFilePath = storage_path('app/public/' . $video->temp_file_path);
            
            Log::info('Checking file existence', ['temp_path' => $tempFilePath]);
            
            if (!file_exists($tempFilePath)) {
                Log::error('File not found on server', ['temp_path' => $tempFilePath]);
                $video->update([
                    'status' => 'rejected',
                    'review_notes' => 'Temporary file not found on server'
                ]);
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'error' => 'Video file not found on server.'], 400);
                }
                return back()->withErrors(['error' => 'Video file not found on server.']);
            }

            // Check file size and permissions
            $fileSize = filesize($tempFilePath);
            Log::info('File details', [
                'video_id' => $video->id,
                'file_size' => $fileSize,
                'is_readable' => is_readable($tempFilePath)
            ]);

            // Determine upload destination based on video_source or upload_destination
            $uploadDestination = $video->video_source ?? 'mux'; // Default to Mux if not specified

            Log::info('Video upload destination determined', [
                'video_id' => $video->id,
                'upload_destination' => $uploadDestination
            ]);

            if ($uploadDestination === 'vimeo') {
                // Upload to Vimeo
                $vimeoService = new \App\Services\VimeoService();

                Log::info('Calling VimeoService uploadVideo', [
                    'video_id' => $video->id,
                    'file_path' => $video->temp_file_path,
                    'title' => $video->title
                ]);

                $result = $vimeoService->uploadVideo(
                    $video->temp_file_path,
                    $video->title,
                    $video->description
                );

                Log::info('Vimeo upload result', ['video_id' => $video->id, 'result' => $result]);

                if ($result['success']) {
                    // Update video with Vimeo information
                    $video->update([
                        'video_source' => 'vimeo',
                        'vimeo_id' => $result['video_id'],
                        'vimeo_embed_url' => $result['embed_url'],
                        'status' => 'approved',
                        'video_path' => null, // Clear local path since it's now on Vimeo
                    ]);

                    // Clean up temporary file
                    if ($video->temp_file_path) {
                        Storage::disk('public')->delete($video->temp_file_path);
                        $video->update(['temp_file_path' => null, 'temp_expires_at' => null]);
                    }

                    Log::info('Video approved and uploaded to Vimeo successfully', [
                        'video_id' => $video->id,
                        'vimeo_id' => $result['video_id']
                    ]);

                    if ($request->expectsJson()) {
                        return response()->json(['success' => true, 'message' => 'Video approved and uploaded to Vimeo successfully!']);
                    }
                    return back()->with('success', 'Video approved and uploaded to Vimeo successfully!');
                } else {
                    Log::error('Vimeo upload failed', [
                        'video_id' => $video->id,
                        'error' => $result['error'] ?? 'Unknown error'
                    ]);

                    $video->update(['status' => 'pending']);
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'error' => 'Failed to upload to Vimeo: ' . ($result['error'] ?? 'Unknown error')], 500);
                    }
                    return back()->withErrors(['error' => 'Failed to upload to Vimeo: ' . ($result['error'] ?? 'Unknown error')]);
                }
            } else {
                // Upload to Mux (existing logic)
                $muxService = new \App\Services\MuxService();

                Log::info('Calling MuxService uploadVideo', [
                    'video_id' => $video->id,
                    'file_path' => $video->temp_file_path,
                    'title' => $video->title
                ]);

                $result = $muxService->uploadVideo(
                    $video->temp_file_path,
                    $video->title,
                    $video->description
                );

                Log::info('Mux upload result', ['video_id' => $video->id, 'result' => $result]);

                if ($result['success']) {
                    // Update video with Mux information
                    $video->update([
                        'mux_asset_id' => $result['asset_id'] ?? null,
                        'mux_playback_id' => $result['playback_id'] ?? null,
                        'status' => 'approved',
                        'video_path' => null, // Clear local path since it's now on Mux
                    ]);

                    // Clean up temporary file
                    if ($video->temp_file_path) {
                        Storage::disk('public')->delete($video->temp_file_path);
                        $video->update(['temp_file_path' => null, 'temp_expires_at' => null]);
                    }

                    Log::info('Video approved and uploaded to Mux successfully', [
                        'video_id' => $video->id,
                        'mux_asset_id' => $result['asset_id'] ?? null
                    ]);

                    if ($request->expectsJson()) {
                        return response()->json(['success' => true, 'message' => 'Video approved and uploaded to Mux successfully!']);
                    }
                    return back()->with('success', 'Video approved and uploaded to Mux successfully!');
                } else {
                    Log::error('Mux upload failed', [
                        'video_id' => $video->id,
                        'error' => $result['error'] ?? 'Unknown error'
                    ]);

                    $video->update(['status' => 'pending']);
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'error' => 'Failed to upload to Mux: ' . ($result['error'] ?? 'Unknown error')], 500);
                    }
                    return back()->withErrors(['error' => 'Failed to upload to Mux: ' . ($result['error'] ?? 'Unknown error')]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Video approval failed with exception', [
                'video_id' => $video->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $video->update(['status' => 'pending']);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'An error occurred while approving the video: ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'An error occurred while approving the video: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject video and clean up temporary files
     */
    public function rejectVideo(Request $request, $id)
    {
        $video = Video::findOrFail($id);

        if (!$video->isPending()) {
            Log::warning('Video rejection attempted on non-pending video', ['video_id' => $video->id, 'status' => $video->status]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Video is not pending approval.'], 400);
            }
            return back()->withErrors(['error' => 'Video is not pending approval.']);
        }

        try {
            Log::info('Starting video rejection process', ['video_id' => $video->id, 'title' => $video->title]);

            // Update video status to rejected
            $video->update([
                'status' => 'rejected',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $request->input('review_notes')
            ]);

            // Clean up temporary file if it exists
            if ($video->temp_file_path) {
                $tempFilePath = storage_path('app/public/' . $video->temp_file_path);
                if (file_exists($tempFilePath)) {
                    Storage::disk('public')->delete($video->temp_file_path);
                    Log::info('Temporary video file deleted during rejection', ['video_id' => $video->id, 'file_path' => $video->temp_file_path]);
                }
                $video->update(['temp_file_path' => null, 'temp_expires_at' => null]);
            }

            // Clean up thumbnail if it exists and was temporary
            if ($video->thumbnail_path && !preg_match('/^https?:\/\//', $video->thumbnail_path)) {
                $thumbnailPath = storage_path('app/public/' . $video->thumbnail_path);
                if (file_exists($thumbnailPath)) {
                    Storage::disk('public')->delete($video->thumbnail_path);
                    Log::info('Thumbnail file deleted during rejection', ['video_id' => $video->id, 'thumbnail_path' => $video->thumbnail_path]);
                }
                $video->update(['thumbnail_path' => null]);
            }

            Log::info('Video rejected successfully', [
                'video_id' => $video->id,
                'reviewed_by' => Auth::id(),
                'review_notes' => $request->input('review_notes')
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Video rejected successfully!']);
            }
            return back()->with('success', 'Video rejected successfully!');

        } catch (\Exception $e) {
            Log::error('Video rejection failed with exception', [
                'video_id' => $video->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $video->update(['status' => 'pending']);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'An error occurred while rejecting the video: ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'An error occurred while rejecting the video: ' . $e->getMessage()]);
        }
    }


    /**
     * Get video for preview (AJAX)
     */
    public function previewVideo(Video $video)
    {
        return response()->json([
            'id' => $video->id,
            'title' => $video->title,
            'description' => $video->description,
            'grade_level' => $video->grade_level,
            'duration' => $video->duration_seconds,
            'file_size' => $video->getFormattedFileSize(),
            'video_url' => $video->getVideoUrl(),
            'status' => $video->status,
            'uploaded_by' => $video->uploader->name,
            'uploaded_at' => $video->created_at->format('M d, Y H:i'),
            'expires_at' => $video->temp_expires_at ? $video->temp_expires_at->format('M d, Y H:i') : null,
        ]);
    }

    /**
     * Verify video upload status (Mux or Vimeo)
     */
    public function verifyVideoUpload(Request $request, Video $video)
    {
        try {
            if ($video->video_source === 'vimeo') {
                // Verify Vimeo upload
                $vimeoService = new \App\Services\VimeoService();

                if (!$video->vimeo_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No Vimeo video ID found for this video'
                    ]);
                }

                $videoInfo = $vimeoService->getVideoInfo($video->vimeo_id);

                if ($videoInfo) {
                    // Update video status based on Vimeo status
                    $vimeoStatus = $videoInfo['status'] ?? 'unknown';
                    $newStatus = in_array($vimeoStatus, ['available', 'ready']) ? 'approved' : 'processing';

                    $video->update(['status' => $newStatus]);

                    return response()->json([
                        'success' => true,
                        'status' => $newStatus,
                        'vimeo_status' => $vimeoStatus,
                        'message' => 'Video is ' . $vimeoStatus . ' on Vimeo'
                    ]);
                } else {
                    // Video doesn't exist on Vimeo
                    $video->update(['status' => 'rejected']);

                    return response()->json([
                        'success' => false,
                        'message' => 'Video not found on Vimeo'
                    ]);
                }
            } else {
                // Verify Mux upload
                $muxService = new \App\Services\MuxService();

                if (!$video->mux_asset_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No Mux asset ID found for this video'
                    ]);
                }

                $assetInfo = $muxService->getAsset($video->mux_asset_id);

                if ($assetInfo['success']) {
                    // Update video status based on Mux status
                    $muxStatus = $assetInfo['status'];
                    $newStatus = $muxStatus === 'ready' ? 'approved' : 'processing';

                    $video->update(['status' => $newStatus]);

                    return response()->json([
                        'success' => true,
                        'status' => $newStatus,
                        'mux_status' => $muxStatus,
                        'message' => 'Video is ' . $muxStatus . ' on Mux'
                    ]);
                } else {
                    // Asset doesn't exist on Mux
                    $video->update(['status' => 'rejected']);

                    return response()->json([
                        'success' => false,
                        'message' => 'Video not found on Mux'
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Video verification failed', [
                'video_id' => $video->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ]);
        }
    }

    // Content Management - Quizzes
    public function indexQuizzes(Request $request)
    {
        $query = Quiz::with('uploader', 'video');

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhereHas('subject', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
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
        $subjects = Subject::select('id', 'name', DB::raw('COUNT(quizzes.id) as count'))
            ->leftJoin('quizzes', 'subjects.id', '=', 'quizzes.subject_id')
            ->groupBy('subjects.id', 'subjects.name')
            ->orderBy('subjects.name')
            ->get();

        return view('admin.content.quizzes.index', compact('quizzes', 'gradeLevels', 'videos', 'uploaders', 'subjects'));
    }

    public function storeQuiz(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'nullable|exists:subjects,id',
            'video_id' => 'nullable|exists:videos,id',
            'grade_level' => 'nullable|string|max:255',
            'quiz_data' => 'nullable|string', // Or 'json' if you enforce JSON structure
            'is_featured' => 'boolean',
        ]);

        Quiz::create([
            'title' => $request->title,
            'subject_id' => $request->subject_id,
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
            'subject_id' => 'nullable|exists:subjects,id',
            'video_id' => 'nullable|exists:videos,id',
            'grade_level' => 'nullable|string|max:255',
            'quiz_data' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        $quiz->update([
            'title' => $request->title,
            'subject_id' => $request->subject_id,
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
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
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
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        return redirect()->route('admin.content.documents.index')->with('success', 'Document deleted successfully!');
    }

    public function toggleDocumentFeature(Document $document)
    {
        $document->is_featured = !$document->is_featured;
        $document->save();

        return back()->with('success', 'Document feature status updated.');
    }

    /**
     * Update user avatar from admin panel
     */
    public function updateUserAvatar(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Delete old avatar if it exists and is a local file
            if ($user->avatar && !preg_match('/^https?:\/\//', $user->avatar) && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store the new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();

            Log::channel('security')->info('admin_updated_user_avatar', [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => get_client_ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar updated successfully!',
                'avatar_url' => $user->avatar_url
            ]);

        } catch (\Exception $e) {
            Log::error('admin_avatar_update_error', [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => get_client_ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update avatar. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete user avatar from admin panel
     */
    public function deleteUserAvatar($userId)
    {
        $user = User::findOrFail($userId);

        try {
            // Delete avatar if it exists and is a local file
            if ($user->avatar && !preg_match('/^https?:\/\//', $user->avatar) && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = null;
            $user->save();

            Log::channel('security')->info('admin_deleted_user_avatar', [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => get_client_ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('admin_avatar_delete_error', [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => get_client_ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete avatar. Please try again.'
            ], 500);
        }
    }

    /**
     * Show revenue analytics page
     */
    public function revenue()
    {
        // Get revenue data
        $revenueData = $this->getRevenueData();

        // Get subscription analytics
        $subscriptionAnalytics = $this->getSubscriptionAnalytics();

        // Get revenue trends
        $revenueTrends = $this->getRevenueTrends();

        // Get top performing plans
        $topPlans = $this->getTopPerformingPlans();

        Log::channel('security')->info('revenue_analytics_accessed', [
            'admin_id' => Auth::id(),
            'ip' => get_client_ip(),
            'user_agent' => request()->header('User-Agent'),
            'timestamp' => now()->toISOString()
        ]);

        return view('admin.revenue.index', compact('revenueData', 'subscriptionAnalytics', 'revenueTrends', 'topPlans'));
    }

    /**
     * Get revenue data for analytics
     */
    private function getRevenueData()
    {
        // Get total revenue from successful payments
        $totalRevenue = \App\Models\Payment::where('status', 'success')->sum('amount');

        // Get current month revenue
        $monthlyRevenue = \App\Models\Payment::where('status', 'success')
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');

        // Get current week revenue
        $weeklyRevenue = \App\Models\Payment::where('status', 'success')
            ->whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');

        // Get today's revenue
        $dailyRevenue = \App\Models\Payment::where('status', 'success')
            ->whereDate('paid_at', today())
            ->sum('amount');

        // Calculate revenue growth (current month vs previous month)
        $previousMonthRevenue = \App\Models\Payment::where('status', 'success')
            ->whereYear('paid_at', now()->subMonth()->year)
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->sum('amount');

        $revenueGrowth = $previousMonthRevenue > 0
            ? (($monthlyRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100
            : 0;

        // Get active subscriptions count
        $activeSubscriptions = \App\Models\UserSubscription::active()->count();

        // Get new subscriptions today
        $newSubscriptionsToday = \App\Models\UserSubscription::whereDate('created_at', today())->count();

        // Calculate churn rate (simplified: expired subscriptions in last 30 days / total active subscriptions)
        $expiredSubscriptionsLastMonth = \App\Models\UserSubscription::where('status', 'expired')
            ->where('expires_at', '>=', now()->subMonth())
            ->where('expires_at', '<=', now())
            ->count();

        $churnRate = $activeSubscriptions > 0
            ? ($expiredSubscriptionsLastMonth / ($activeSubscriptions + $expiredSubscriptionsLastMonth)) * 100
            : 0;

        // Calculate average revenue per user (total revenue / users who made payments)
        $usersWithPayments = \App\Models\Payment::where('status', 'success')->distinct('user_id')->count('user_id');
        $averageRevenuePerUser = $usersWithPayments > 0 ? $totalRevenue / $usersWithPayments : 0;

        return [
            'total_revenue' => (float) $totalRevenue,
            'monthly_revenue' => (float) $monthlyRevenue,
            'weekly_revenue' => (float) $weeklyRevenue,
            'daily_revenue' => (float) $dailyRevenue,
            'revenue_growth' => round($revenueGrowth, 1),
            'active_subscriptions' => $activeSubscriptions,
            'new_subscriptions_today' => $newSubscriptionsToday,
            'churn_rate' => round($churnRate, 1),
            'average_revenue_per_user' => round($averageRevenuePerUser, 2)
        ];
    }

    /**
     * Get subscription analytics
     */
    private function getSubscriptionAnalytics()
    {
        $plans = \App\Models\PricingPlan::active()->ordered()->get();
        $totalRevenue = \App\Models\Payment::where('status', 'success')->sum('amount');

        $analytics = [];
        foreach ($plans as $plan) {
            $activeSubscribers = $plan->activeSubscriptions()->count();
            $planRevenue = $plan->subscriptions()
                ->where('status', 'active')
                ->sum('amount_paid');

            $percentage = $totalRevenue > 0 ? ($planRevenue / $totalRevenue) * 100 : 0;

            $analytics[$plan->slug] = [
                'name' => $plan->name,
                'subscribers' => $activeSubscribers,
                'revenue' => (float) $planRevenue,
                'percentage' => round($percentage, 1)
            ];
        }

        return $analytics;
    }

    /**
     * Get revenue trends for charts
     */
    private function getRevenueTrends()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            // Get revenue for this month
            $revenue = \App\Models\Payment::where('status', 'success')
                ->whereYear('paid_at', $date->year)
                ->whereMonth('paid_at', $date->month)
                ->sum('amount');

            // Get subscription count for this month
            $subscriptions = \App\Models\UserSubscription::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $data[] = [
                'month' => $date->format('M Y'),
                'revenue' => (float) $revenue,
                'subscriptions' => $subscriptions
            ];
        }
        return $data;
    }

    /**
     * Get top performing plans
     */
    private function getTopPerformingPlans()
    {
        $plans = \App\Models\PricingPlan::active()->with(['subscriptions' => function($query) {
            $query->where('status', 'active');
        }])->get();

        $planData = [];
        foreach ($plans as $plan) {
            $currentRevenue = $plan->subscriptions->sum('amount_paid');

            // Calculate growth (compare current month to previous month revenue for this plan)
            $currentMonthRevenue = $plan->subscriptions()
                ->where('status', 'active')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount_paid');

            $previousMonthRevenue = $plan->subscriptions()
                ->where('status', 'active')
                ->whereYear('created_at', now()->subMonth()->year)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->sum('amount_paid');

            $growth = $previousMonthRevenue > 0
                ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100
                : 0;

            $planData[] = [
                'plan' => $plan->name,
                'subscribers' => $plan->subscriptions->count(),
                'revenue' => (float) $currentRevenue,
                'growth' => round($growth, 1)
            ];
        }

        // Sort by revenue descending and return top 3
        usort($planData, function($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        return array_slice($planData, 0, 3);
    }

    /**
     * Show unified contents management page (YouTube-style dashboard)
     */
    public function contents(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, videos, documents, quizzes
        $sort = $request->get('sort', 'newest'); // newest, oldest, most_viewed, most_liked

        // Get all content with unified structure
        $contents = $this->getUnifiedContents($query, $type, $sort);

        // Get content statistics
        $stats = [
            'total_videos' => Video::count(),
            'total_documents' => Document::count(),
            'total_quizzes' => Quiz::count(),
            'total_views' => Video::sum('views') + Document::sum('views') + Quiz::sum('attempts_count'),
            'pending_reviews' => Video::pending()->count(),
        ];

        // Get subjects for the upload modal
        $subjects = Subject::orderBy('name')->get();

        return view('admin.contents.index', compact('contents', 'stats', 'query', 'type', 'sort', 'subjects'));
    }

    /**
     * Get unified contents for the dashboard
     */
    private function getUnifiedContents($query = '', $type = 'all', $sort = 'newest')
    {
        $contents = collect();

        // Get videos (always included, with attachment counts)
        if ($type === 'all' || $type === 'videos' || $type === 'pending') {
            $videoQuery = Video::with(['uploader:id,name,email', 'quiz', 'documents', 'quizzes', 'comments', 'subject:id,name'])
                ->when($query, function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                });

            // Filter for pending videos if type is 'pending'
            if ($type === 'pending') {
                $videoQuery->where('status', 'pending');
            }

            $videos = $videoQuery->select([
                'id', 'title', 'description', 'thumbnail_path', 'views', 'comments_count', 'created_at',
                'uploaded_by', 'status', 'grade_level', 'duration_seconds', 'document_path', 'quiz_id', 'subject_id',
                DB::raw("'video' as content_type"),
                DB::raw('0 as likes'),
                DB::raw('0 as dislikes')
            ]);

            $contents = $contents->merge($videos->get()->map(function($item) {
                $item->published_date = $item->created_at->format('M d, Y');
                $item->uploader_name = $item->uploader->name ?? 'Unknown';
                $item->uploader_email = $item->uploader->email ?? '';
                $item->duration_formatted = $item->duration_seconds ? gmdate('H:i:s', $item->duration_seconds) : '00:00:00';
                $item->subject_name = $item->subject->name ?? null;
                // Add counts manually
                $item->documents_count = $item->documents->count();
                $item->quizzes_count = $item->quizzes->count();
                return $item;
            }));
        }

        // Get standalone documents (only when specifically filtering for documents)
        if ($type === 'documents') {
            $documents = Document::with(['uploader:id,name,email'])
                ->whereNull('video_id') // Only standalone documents
                ->when($query, function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                })
                ->select([
                    'id', 'title', 'description', 'file_path', 'views', 'created_at',
                    'uploaded_by', 'grade_level',
                    DB::raw("'document' as content_type"),
                    DB::raw('0 as likes'),
                    DB::raw('0 as dislikes'),
                    DB::raw('0 as comments_count'),
                    DB::raw('NULL as duration_seconds')
                ]);

            $contents = $contents->merge($documents->get()->map(function($item) {
                $item->published_date = $item->created_at->format('M d, Y');
                $item->uploader_name = $item->uploader->name ?? 'Unknown';
                $item->uploader_email = $item->uploader->email ?? '';
                $item->thumbnail_path = null; // Documents don't have thumbnails
                $item->status = 'approved'; // Documents are auto-approved
                $item->duration_formatted = 'N/A';
                return $item;
            }));
        }

        // Get standalone quizzes (only when specifically filtering for quizzes)
        if ($type === 'quizzes') {
            $quizzes = Quiz::with(['uploader:id,name,email', 'ratings', 'subject:id,name'])
                ->whereNull('video_id') // Only standalone quizzes
                ->when($query, function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhereHas('subject', function($subQ) use ($query) {
                          $subQ->where('name', 'like', "%{$query}%");
                      });
                })
                ->select([
                    'id', 'title', 'created_at', 'uploaded_by',
                    'grade_level', 'is_featured', 'subject_id',
                    DB::raw("'quiz' as content_type"),
                    DB::raw('0 as views'),
                    DB::raw('0 as likes'),
                    DB::raw('0 as dislikes'),
                    DB::raw('0 as comments_count'),
                    DB::raw('NULL as duration_seconds')
                ]);

            $contents = $contents->merge($quizzes->get()->map(function($item) {
                $item->published_date = $item->created_at->format('M d, Y');
                $item->uploader_name = $item->uploader->name ?? 'Unknown';
                $item->uploader_email = $item->uploader->email ?? '';
                $item->thumbnail_path = null;
                $item->status = 'approved'; // Quizzes are auto-approved
                $item->duration_formatted = 'N/A';
                $item->description = $item->subject->name ?? 'No Subject';
                $item->subject_name = $item->subject->name ?? null;

                // Add quiz ratings data
                $ratings = $item->ratings;
                $item->average_rating = $ratings->count() > 0 ? round($ratings->avg('rating'), 1) : null;
                $item->total_ratings = $ratings->count();

                return $item;
            }));
        }

        // Sort contents
        switch ($sort) {
            case 'oldest':
                $contents = $contents->sortBy('created_at');
                break;
            case 'most_viewed':
                $contents = $contents->sortByDesc('views');
                break;
            case 'most_liked':
                $contents = $contents->sortByDesc('likes');
                break;
            case 'newest':
            default:
                $contents = $contents->sortByDesc('created_at');
                break;
        }

        return $contents;
    }

    /**
     * Store a complete content package (video + documents + quiz)
     */
    public function storeContentPackage(Request $request)
    {
        try {
            DB::beginTransaction();

            $responseData = [
                'video_id' => null,
                'documents_count' => 0,
                'quiz_id' => null,
                'questions_count' => 0
            ];

            // Step 1: Upload and store the video (only if video data is provided)
            $video = null;
            if ($request->hasFile('video_file') || $request->filled('external_video_url') || $request->filled('vimeo_url') || $request->filled('title')) {
                $videoData = [
                    'title' => $request->title,
                    'subject_id' => $request->subject_id,
                    'description' => $request->description,
                    'grade_level' => $request->grade_level,
                    'uploaded_by' => Auth::id(),
                    'video_source' => $request->video_source ?? 'local',
                    'status' => 'pending' // Videos need approval
                ];

                // Handle Vimeo URL input (when Vimeo upload is selected)
                if ($request->filled('vimeo_url') && $request->video_source === 'vimeo') {
                    $parsed = \App\Services\VideoSourceService::parseVideoUrl($request->vimeo_url);
                    if ($parsed && $parsed['source'] === 'vimeo') {
                        $videoData['external_video_id'] = $parsed['video_id'];
                        $videoData['external_video_url'] = $parsed['embed_url'];
                        $videoData['video_source'] = 'vimeo';
                        $videoData['status'] = 'approved'; // Vimeo URLs are auto-approved
                    } else {
                        throw new \Exception('Invalid Vimeo URL provided');
                    }
                }
                // Handle external video URL (for other sources like YouTube)
                elseif ($request->filled('external_video_url')) {
                    $parsed = \App\Services\VideoSourceService::parseVideoUrl($request->external_video_url);
                    if ($parsed) {
                        $videoData['external_video_id'] = $parsed['video_id'];
                        $videoData['external_video_url'] = $parsed['embed_url'];
                        $videoData['status'] = 'approved'; // External videos are auto-approved
                    } else {
                        throw new \Exception('Invalid video URL provided');
                    }
                }

                $video = Video::create($videoData);
                $responseData['video_id'] = $video->id;

                // Handle local video file upload (for Vimeo upload or local storage)
                if ($request->hasFile('video_file')) {
                    $videoFile = $request->file('video_file');
                    $filename = time() . '_' . $video->id . '.' . $videoFile->getClientOriginalExtension();
                    $tempPath = $videoFile->storeAs('temp_videos', $filename, 'public');
                    $video->update(['temp_file_path' => $tempPath]);

                    // If upload destination is specified, set video_source accordingly
                    if ($request->filled('upload_destination')) {
                        $video->update(['video_source' => $request->upload_destination]);

                        // If uploading to Vimeo, do it immediately
                        if ($request->upload_destination === 'vimeo') {
                            try {
                                Log::info('Starting immediate Vimeo upload', ['video_id' => $video->id]);

                                $vimeoService = new \App\Services\VimeoService();
                                $uploadId = 'video_' . $video->id . '_' . time();
                                $result = $vimeoService->uploadVideo($tempPath, $video->title, $video->description, Auth::id(), $uploadId);

                                if ($result['success']) {
                                    $video->update([
                                        'vimeo_id' => $result['video_id'],
                                        'vimeo_embed_url' => $result['embed_url'],
                                        'status' => 'approved',
                                        'temp_file_path' => null, // Clear temp file path
                                        'temp_expires_at' => null
                                    ]);

                                    // Clean up temporary file
                                    Storage::disk('public')->delete($tempPath);

                                    Log::info('Immediate Vimeo upload successful', ['video_id' => $video->id, 'vimeo_id' => $result['video_id']]);
                                } else {
                                    Log::error('Immediate Vimeo upload failed', ['video_id' => $video->id, 'error' => $result['error']]);
                                    $video->update(['status' => 'rejected']);
                                    throw new \Exception('Failed to upload to Vimeo: ' . ($result['error'] ?? 'Unknown error'));
                                }
                            } catch (\Exception $e) {
                                Log::error('Exception during immediate Vimeo upload', [
                                    'video_id' => $video->id,
                                    'error' => $e->getMessage()
                                ]);
                                $video->update(['status' => 'rejected']);
                                throw $e;
                            }
                        }
                    }
                }

                // Handle thumbnail upload
                if ($request->hasFile('thumbnail_file')) {
                    $thumbnailFile = $request->file('thumbnail_file');
                    $thumbnailFilename = time() . '_thumb_' . $video->id . '.' . $thumbnailFile->getClientOriginalExtension();
                    $thumbnailPath = $thumbnailFile->storeAs('thumbnails', $thumbnailFilename, 'public');
                    $video->update(['thumbnail_path' => $thumbnailPath]);
                }
            }

            // Step 2: Upload documents (optional) - only if documents are provided and video_id is set
            $documents = [];
            if ($request->hasFile('documents') && $video) {
                foreach ($request->file('documents') as $docFile) {
                    // Handle document file upload first
                    $filename = time() . '_' . uniqid() . '.' . $docFile->getClientOriginalExtension();
                    $path = $docFile->storeAs('documents', $filename, 'public');

                    $docData = [
                        'title' => pathinfo($docFile->getClientOriginalName(), PATHINFO_FILENAME),
                        'file_path' => $path,
                        'description' => 'Related document for video: ' . ($video ? $video->title : 'Unknown'),
                        'uploaded_by' => Auth::id(),
                        'video_id' => $video ? $video->id : null,
                        'grade_level' => $request->grade_level
                    ];

                    $document = Document::create($docData);
                    $documents[] = $document;
                }
                $responseData['documents_count'] = count($documents);
            }

            // Step 3: Create quiz (optional) - only if quiz data is provided and video_id is set
            $quiz = null;
            if ($request->filled('quiz_data') && $video) {
                $quizData = json_decode($request->quiz_data, true);

                if (!empty($quizData['questions'])) {
                    // Find or create subject
                    $subject = Subject::firstOrCreate(
                        ['name' => $video->title],
                        ['description' => 'Auto-created subject for quiz']
                    );

                    $quizDataToCreate = [
                        'title' => 'Quiz for: ' . $video->title,
                        'subject_id' => $subject->id,
                        'uploaded_by' => Auth::id(),
                        'grade_level' => $request->grade_level,
                        'video_id' => $video->id,
                        'quiz_data' => json_encode($quizData),
                        'is_featured' => false
                    ];

                    $quiz = Quiz::create($quizDataToCreate);

                    // Update video's quiz_id for consistency
                    $video->quiz_id = $quiz->id;
                    $video->save();

                    $responseData['quiz_id'] = $quiz->id;
                    $responseData['questions_count'] = $quiz->questions()->count();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Content package uploaded successfully!',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Content package upload failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Progress Management - Admin
    public function progressOverview(Request $request)
    {
        $query = UserProgress::with(['user:id,name,email,grade']);

        // Filter by level group
        if ($request->has('level_group') && $request->level_group != '') {
            $query->where('level_group', $request->level_group);
        }

        // Filter by eligibility status
        if ($request->has('eligibility') && $request->eligibility != '') {
            $query->where('eligible_for_next_level', $request->eligibility === 'eligible');
        }

        // Search by user name or email
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $progressRecords = $query->orderBy('updated_at', 'desc')->paginate(20);

        $levelGroups = [
            'primary-lower' => 'Primary Lower (P1-P3)',
            'primary-upper' => 'Primary Upper (P4-P6)',
            'jhs' => 'Junior High School (JHS 1-3)',
            'shs' => 'Senior High School (SHS 1-3)',
        ];

        // Progress statistics
        $stats = [
            'total_students' => UserProgress::count(),
            'eligible_students' => UserProgress::where('eligible_for_next_level', true)->count(),
            'completed_levels' => UserProgress::where('level_completed', true)->count(),
            'active_students' => UserProgress::where('last_activity_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.progress.overview', compact('progressRecords', 'levelGroups', 'stats'));
    }

    public function userProgressDetail($userId)
    {
        $user = User::findOrFail($userId);
        $progressRecords = UserProgress::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $currentProgress = $progressRecords->first();
        $analytics = $currentProgress ? $currentProgress->getDetailedAnalytics() : null;

        // Get progression history
        $progressionHistory = \App\Models\LevelProgression::where('user_id', $userId)
            ->orderBy('progressed_at', 'desc')
            ->get();

        return view('admin.progress.user-detail', compact(
            'user',
            'progressRecords',
            'currentProgress',
            'analytics',
            'progressionHistory'
        ));
    }

    public function progressionStandards()
    {
        $standards = ProgressionStandard::orderBy('level_group')->get();
        $levelGroups = [
            'primary-lower' => 'Primary Lower (P1-P3)',
            'primary-upper' => 'Primary Upper (P4-P6)',
            'jhs' => 'Junior High School (JHS 1-3)',
            'shs' => 'Senior High School (SHS 1-3)',
        ];

        return view('admin.progress.standards', compact('standards', 'levelGroups'));
    }

    public function storeProgressionStandard(Request $request)
    {
        $request->validate([
            'level_group' => 'required|string',
            'required_lesson_completion_percentage' => 'required|numeric|min:0|max:100',
            'required_quiz_completion_percentage' => 'required|numeric|min:0|max:100',
            'required_average_quiz_score' => 'required|numeric|min:0|max:100',
            'minimum_quiz_score' => 'required|numeric|min:0|max:100',
            'lesson_watch_threshold_percentage' => 'required|numeric|min:0|max:100',
            'individual_level_lesson_threshold' => 'required|numeric|min:0|max:100',
            'individual_level_quiz_threshold' => 'required|numeric|min:0|max:100',
            'individual_level_score_threshold' => 'required|numeric|min:0|max:100',
            'required_number_of_lessons_individual' => 'required|integer|min:1',
            'required_number_of_quizzes_individual' => 'required|integer|min:1',
            'required_number_of_lessons_group' => 'required|integer|min:1',
            'required_number_of_quizzes_group' => 'required|integer|min:1',
        ]);

        // Deactivate existing standard for this level group
        ProgressionStandard::where('level_group', $request->level_group)
            ->update(['is_active' => false]);

        // Create new standard
        ProgressionStandard::create([
            'level_group' => $request->level_group,
            'required_lesson_completion_percentage' => $request->required_lesson_completion_percentage,
            'required_quiz_completion_percentage' => $request->required_quiz_completion_percentage,
            'required_average_quiz_score' => $request->required_average_quiz_score,
            'minimum_quiz_score' => $request->minimum_quiz_score,
            'lesson_watch_threshold_percentage' => $request->lesson_watch_threshold_percentage,
            'individual_level_lesson_threshold' => $request->individual_level_lesson_threshold,
            'individual_level_quiz_threshold' => $request->individual_level_quiz_threshold,
            'individual_level_score_threshold' => $request->individual_level_score_threshold,
            'required_number_of_lessons_individual' => $request->required_number_of_lessons_individual,
            'required_number_of_quizzes_individual' => $request->required_number_of_quizzes_individual,
            'required_number_of_lessons_group' => $request->required_number_of_lessons_group,
            'required_number_of_quizzes_group' => $request->required_number_of_quizzes_group,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Progression standards updated successfully!');
    }

    public function updateProgressionStandard(Request $request, ProgressionStandard $standard)
    {
        $request->validate([
            'required_lesson_completion_percentage' => 'required|numeric|min:0|max:100',
            'required_quiz_completion_percentage' => 'required|numeric|min:0|max:100',
            'required_average_quiz_score' => 'required|numeric|min:0|max:100',
            'minimum_quiz_score' => 'required|numeric|min:0|max:100',
            'lesson_watch_threshold_percentage' => 'required|numeric|min:0|max:100',
            'individual_level_lesson_threshold' => 'required|numeric|min:0|max:100',
            'individual_level_quiz_threshold' => 'required|numeric|min:0|max:100',
            'individual_level_score_threshold' => 'required|numeric|min:0|max:100',
            'required_number_of_lessons_individual' => 'required|integer|min:1',
            'required_number_of_quizzes_individual' => 'required|integer|min:1',
            'required_number_of_lessons_group' => 'required|integer|min:1',
            'required_number_of_quizzes_group' => 'required|integer|min:1',
        ]);

        $standard->update($request->only([
            'required_lesson_completion_percentage',
            'required_quiz_completion_percentage',
            'required_average_quiz_score',
            'minimum_quiz_score',
            'lesson_watch_threshold_percentage',
            'individual_level_lesson_threshold',
            'individual_level_quiz_threshold',
            'individual_level_score_threshold',
            'required_number_of_lessons_individual',
            'required_number_of_quizzes_individual',
            'required_number_of_lessons_group',
            'required_number_of_quizzes_group',
        ]));

        return redirect()->back()->with('success', 'Progression standard updated successfully!');
    }

    public function toggleStandardStatus(ProgressionStandard $standard)
    {
        // If activating, deactivate others for same level group
        if (!$standard->is_active) {
            ProgressionStandard::where('level_group', $standard->level_group)
                ->update(['is_active' => false]);
        }

        $standard->update(['is_active' => !$standard->is_active]);

        return redirect()->back()->with('success', 'Standard status updated successfully!');
    }

    public function manualProgressUser(Request $request, $userId)
    {
        $request->validate([
            'from_level' => 'required|string',
            'to_level' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $user = User::findOrFail($userId);

        // Use the existing manual progression method from ProgressController
        $progressController = new \App\Http\Controllers\ProgressController();
        $result = $progressController->manualProgression($request, $userId, $request->to_level);

        if ($result->getData()->success) {
            return redirect()->back()->with('success', 'User progressed successfully!');
        }

        return redirect()->back()->with('error', 'Failed to progress user.');
    }

    /**
     * Edit content from unified contents page
     */
    public function editContent($contentId)
    {
        // Find the content by checking different models
        $content = null;
        $contentType = null;

        // Try Video first
        $content = Video::with(['subject', 'documents', 'quizzes'])->find($contentId);
        if ($content) {
            $contentType = 'video';
        } else {
            // Try Document
            $content = Document::find($contentId);
            if ($content) {
                $contentType = 'document';
            } else {
                // Try Quiz
                $content = Quiz::find($contentId);
                if ($content) {
                    $contentType = 'quiz';
                }
            }
        }

        if (!$content) {
            return redirect()->route('admin.contents.index')->withErrors(['content' => 'Content not found.']);
        }

        // For videos, show the unified edit form
        if ($contentType === 'video') {
            $subjects = Subject::orderBy('name')->get();
            $availableQuizzes = Quiz::whereNull('video_id')->orWhere('video_id', $contentId)->get();
            $availableDocuments = Document::whereNull('video_id')->orWhere('video_id', $contentId)->get();

            return view('admin.contents.edit', compact('content', 'contentType', 'subjects', 'availableQuizzes', 'availableDocuments'));
        }

        // For other content types, redirect to their specific edit pages
        switch ($contentType) {
            case 'document':
                return redirect()->route('admin.content.documents.edit', $content);
            case 'quiz':
                return redirect()->route('admin.content.quizzes.edit', $content);
            default:
                return redirect()->route('admin.contents.index')->withErrors(['content' => 'Unknown content type.']);
        }
    }

    /**
     * Update content from unified contents page
     */
    public function updateContent(Request $request, $contentId)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade_level' => 'required|string',
            'quiz_id' => 'nullable|exists:quizzes,id',
            'document_ids' => 'nullable|array',
            'document_ids.*' => 'exists:documents,id',
            'is_featured' => 'boolean'
        ]);

        // Find the video
        $video = Video::findOrFail($contentId);

        try {
            DB::beginTransaction();

            // Update video basic info
            $video->update([
                'subject_id' => $request->subject_id,
                'grade_level' => $request->grade_level,
                'is_featured' => $request->has('is_featured'),
            ]);

            // Update quiz association
            if ($request->filled('quiz_id')) {
                $video->quiz_id = $request->quiz_id;
                $video->save();
            } else {
                $video->quiz_id = null;
                $video->save();
            }

            // Update document associations
            if ($request->has('document_ids')) {
                // Remove existing associations
                Document::where('video_id', $video->id)->update(['video_id' => null]);
                // Add new associations
                Document::whereIn('id', $request->document_ids)->update(['video_id' => $video->id]);
            } else {
                // Remove all document associations
                Document::where('video_id', $video->id)->update(['video_id' => null]);
            }

            DB::commit();

            return redirect()->route('admin.contents.index')->with('success', 'Content updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Content update failed', [
                'content_id' => $contentId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to update content: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Delete content from unified contents page
     */
    public function destroyContent(Request $request, $contentId)
    {
        // Find the content by checking different models
        $content = null;
        $contentType = null;

        // Try Video first
        $content = Video::find($contentId);
        if ($content) {
            $contentType = 'video';
        } else {
            // Try Document
            $content = Document::find($contentId);
            if ($content) {
                $contentType = 'document';
            } else {
                // Try Quiz
                $content = Quiz::find($contentId);
                if ($content) {
                    $contentType = 'quiz';
                }
            }
        }

        if (!$content) {
            return response()->json(['success' => false, 'message' => 'Content not found.'], 404);
        }

        try {
            $vimeoDeletionSuccess = true;
            $vimeoDeletionError = null;

            // Special handling for video deletion based on source
            if ($contentType === 'video') {
                // For Vimeo videos, delete from both database and Vimeo
                if ($content->video_source === 'vimeo' && $content->vimeo_id) {
                    Log::info('Preparing to delete Vimeo video from both database and Vimeo', [
                        'video_id' => $content->id,
                        'vimeo_id' => $content->vimeo_id,
                        'admin_id' => Auth::id()
                    ]);

                    // Attempt to delete from Vimeo first
                    try {
                        $vimeoService = new \App\Services\VimeoService();
                        $vimeoDeletionSuccess = $vimeoService->deleteVideo($content->vimeo_id);

                        if ($vimeoDeletionSuccess) {
                            Log::info('Successfully deleted video from Vimeo', [
                                'video_id' => $content->id,
                                'vimeo_id' => $content->vimeo_id,
                                'admin_id' => Auth::id()
                            ]);
                        } else {
                            Log::warning('Failed to delete video from Vimeo, but proceeding with database deletion', [
                                'video_id' => $content->id,
                                'vimeo_id' => $content->vimeo_id,
                                'admin_id' => Auth::id()
                            ]);
                            $vimeoDeletionError = 'Failed to delete from Vimeo, but video removed from database';
                        }
                    } catch (\Exception $e) {
                        Log::error('Exception deleting video from Vimeo, proceeding with database deletion', [
                            'video_id' => $content->id,
                            'vimeo_id' => $content->vimeo_id,
                            'admin_id' => Auth::id(),
                            'error' => $e->getMessage()
                        ]);
                        $vimeoDeletionSuccess = false;
                        $vimeoDeletionError = 'Error deleting from Vimeo: ' . $e->getMessage() . ', but video removed from database';
                    }
                }
                // For YouTube videos, we only delete from database (as requested)
                elseif ($content->video_source === 'youtube') {
                    Log::info('Deleting YouTube video from database only', [
                        'video_id' => $content->id,
                        'youtube_video_id' => $content->external_video_id,
                        'admin_id' => Auth::id()
                    ]);
                }
            }

            // Delete the content from database
            switch ($contentType) {
                case 'video':
                    $this->destroyVideo($content);
                    break;
                case 'document':
                    $this->destroyDocument($content);
                    break;
                case 'quiz':
                    $this->destroyQuiz($content);
                    break;
                default:
                    return response()->json(['success' => false, 'message' => 'Unknown content type.'], 400);
            }

            // Prepare response message based on deletion results
            if ($contentType === 'video' && $content->video_source === 'vimeo') {
                if ($vimeoDeletionSuccess) {
                    $message = 'Video deleted successfully from both database and Vimeo!';
                } else {
                    $message = $vimeoDeletionError ?: 'Video deleted from database, but Vimeo deletion failed.';
                }
            } elseif ($contentType === 'video' && $content->video_source === 'youtube') {
                $message = 'Video deleted successfully from database (YouTube video remains unchanged)!';
            } else {
                $message = ucfirst($contentType) . ' deleted successfully!';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'vimeo_deletion_success' => $contentType === 'video' && $content->video_source === 'vimeo' ? $vimeoDeletionSuccess : null
            ]);

        } catch (\Exception $e) {
            Log::error('Content deletion failed', [
                'content_id' => $contentId,
                'content_type' => $contentType,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete content: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix Vimeo video privacy settings for embedding
     */
    public function fixVimeoPrivacy(Request $request)
    {
        try {
            $vimeoService = new \App\Services\VimeoService();
            $result = $vimeoService->fixAllVimeoVideoPrivacy();

            if ($result['success']) {
                Log::channel('security')->info('vimeo_privacy_fix_completed', [
                    'admin_id' => Auth::id(),
                    'updated_videos' => $result['updated'],
                    'failed_videos' => $result['failed'],
                    'ip' => get_client_ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toISOString()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'updated' => $result['updated'],
                    'failed' => $result['failed']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fix Vimeo privacy settings: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('vimeo_privacy_fix_failed', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => get_client_ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fix Vimeo privacy settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload video component only
     */
    public function uploadVideoComponent(Request $request)
    {
        try {
            // Log all request inputs for debugging
            Log::info('Video upload component request received', [
                'has_upload_id' => $request->filled('upload_id'),
                'has_title' => $request->filled('title'),
                'has_subject_id' => $request->filled('subject_id'),
                'has_description' => $request->filled('description'),
                'has_grade_level' => $request->filled('grade_level'),
                'has_video_source' => $request->filled('video_source'),
                'all_params' => array_keys($request->all())
            ]);

            // Manually load config if not cached
            $uploadConfig = config('uploads');
            if (!$uploadConfig) {
                Log::warning('Config cache missing, reloading from file');
                // Force reload from file
                $uploadConfig = include config_path('uploads.php');
            }
            
            if (!$uploadConfig || !is_array($uploadConfig)) {
                Log::error('Upload config invalid', ['config_value' => $uploadConfig, 'config_type' => gettype($uploadConfig)]);
                throw new \Exception('Upload configuration not found or invalid');
            }
            
            // Ensure required config keys exist
            if (empty($uploadConfig['video']) || empty($uploadConfig['thumbnail'])) {
                throw new \Exception('Upload configuration missing required keys (video, thumbnail)');
            }
            
            $videoMaxSize = ($uploadConfig['video']['max_size'] ?? 32212254720) / 1024;
            $thumbnailMaxSize = ($uploadConfig['thumbnail']['max_size'] ?? 5242880) / 1024;
            
            // Log config loaded successfully
            Log::info('Upload config loaded successfully', [
                'has_video_config' => !empty($uploadConfig['video']),
                'has_thumbnail_config' => !empty($uploadConfig['thumbnail']),
                'video_max_size' => $videoMaxSize
            ]);
            
            // Determine if this is a chunked upload or direct upload
            $isChunkedUpload = $request->filled('upload_id');
            
            $validationRules = [
                'title' => 'required|string',
                'subject_id' => 'required|exists:subjects,id',
                'description' => 'nullable|string',
                'grade_level' => 'required|string',
                'video_source' => 'required|string',
                'thumbnail_file' => 'nullable|image|mimes:' . implode(',', $uploadConfig['thumbnail']['mimes']) . '|max:' . $thumbnailMaxSize,
                'external_video_url' => 'nullable|url',
                'vimeo_url' => 'nullable|url',
                'upload_destination' => 'nullable|string'
            ];
            
            if (!$isChunkedUpload) {
                // Only require video_file for direct uploads
                $validationRules['video_file'] = 'nullable|file|mimes:' . implode(',', $uploadConfig['video']['mimes']) . '|max:' . $videoMaxSize;
            } else {
                // For chunked uploads, require upload_id
                $validationRules['upload_id'] = 'required|string';
            }
            
            $request->validate($validationRules, [
                'video_file.max' => 'Video file size cannot exceed ' . $uploadConfig['video']['max_size_display'] . '.',
                'video_file.mimes' => 'Invalid video format. Accepted formats: ' . implode(', ', array_map('strtoupper', $uploadConfig['video']['mimes'])) . '. Please ensure the file is a video file, not a document, image, or GIF.',
                'video_file.file' => 'The uploaded file is not a valid video file.',
                'thumbnail_file.max' => 'Thumbnail size cannot exceed ' . $uploadConfig['thumbnail']['max_size_display'] . '.',
                'thumbnail_file.mimes' => 'Thumbnail must be one of: ' . implode(', ', $uploadConfig['thumbnail']['mimes']) . '.',
                'thumbnail_file.image' => 'Thumbnail must be a valid image file.',
            ]);

            Log::info('Video upload validation passed', [
                'title' => $request->title,
                'subject_id' => $request->subject_id,
                'grade_level' => $request->grade_level,
                'video_source' => $request->video_source,
                'is_chunked' => $isChunkedUpload
            ]);

            DB::beginTransaction();

            $videoData = [
                'title' => $request->title,
                'subject_id' => $request->subject_id,
                'description' => $request->description,
                'grade_level' => $request->grade_level,
                'uploaded_by' => Auth::id(),
                'video_source' => $request->video_source ?? 'local',
                'status' => 'pending'
            ];

            // Handle different video sources
            if ($request->filled('vimeo_url') && $request->video_source === 'vimeo') {
                $parsed = \App\Services\VideoSourceService::parseVideoUrl($request->vimeo_url);
                if ($parsed && $parsed['source'] === 'vimeo') {
                    $videoData['external_video_id'] = $parsed['video_id'];
                    $videoData['external_video_url'] = $parsed['embed_url'];
                    $videoData['video_source'] = 'vimeo';
                    $videoData['status'] = 'approved';
                } else {
                    throw new \Exception('Invalid Vimeo URL provided');
                }
            } elseif ($request->filled('external_video_url')) {
                $parsed = \App\Services\VideoSourceService::parseVideoUrl($request->external_video_url);
                if ($parsed) {
                    $videoData['external_video_id'] = $parsed['video_id'];
                    $videoData['external_video_url'] = $parsed['embed_url'];
                    $videoData['status'] = 'approved';
                } else {
                    throw new \Exception('Invalid video URL provided');
                }
            }

            $video = Video::create($videoData);

            // Handle file upload - either chunked or direct
            if ($isChunkedUpload) {
                // Handle chunked upload - file is already assembled in temp_videos
                $uploadId = $request->input('upload_id');
                $chunkFilename = $request->input('filename', 'video');
                $tempFilename = $uploadId . '_' . $chunkFilename;
                $tempPath = 'temp_videos/' . $tempFilename;
                
                // Verify the file exists
                if (!Storage::disk('public')->exists($tempPath)) {
                    throw new \Exception('Reassembled file not found. Upload may have failed.');
                }
                
                $video->update(['temp_file_path' => $tempPath]);

                if ($request->filled('upload_destination')) {
                    $video->update(['video_source' => $request->upload_destination]);

                    if ($request->upload_destination === 'vimeo') {
                        try {
                            $vimeoService = new \App\Services\VimeoService();
                            $result = $vimeoService->uploadVideo('storage/public/' . $tempPath, $video->title, $video->description, Auth::id(), 'video_' . $video->id);

                            if ($result && is_array($result) && ($result['success'] ?? false)) {
                                $video->update([
                                    'vimeo_id' => $result['video_id'] ?? null,
                                    'vimeo_embed_url' => $result['embed_url'] ?? null,
                                    'status' => 'approved',
                                    'temp_file_path' => null,
                                    'temp_expires_at' => null
                                ]);
                                Storage::disk('public')->delete($tempPath);
                            } else {
                                $video->update(['status' => 'rejected']);
                                $errorMsg = is_array($result) ? ($result['error'] ?? 'Unknown error') : 'Vimeo service returned invalid response';
                                throw new \Exception('Failed to upload to Vimeo: ' . $errorMsg);
                            }
                        } catch (\Exception $vimeoError) {
                            $video->update(['status' => 'rejected']);
                            throw $vimeoError;
                        }
                    }
                }
            } elseif ($request->hasFile('video_file')) {
                // Handle direct upload
                $videoFile = $request->file('video_file');
                $filename = time() . '_' . $video->id . '.' . $videoFile->getClientOriginalExtension();
                $tempPath = $videoFile->storeAs('temp_videos', $filename, 'public');
                $video->update(['temp_file_path' => $tempPath]);

                if ($request->filled('upload_destination')) {
                    $video->update(['video_source' => $request->upload_destination]);

                    if ($request->upload_destination === 'vimeo') {
                        try {
                            $vimeoService = new \App\Services\VimeoService();
                            $uploadId = 'video_' . $video->id . '_' . time();
                            $result = $vimeoService->uploadVideo($tempPath, $video->title, $video->description, Auth::id(), $uploadId);

                            if ($result && is_array($result) && ($result['success'] ?? false)) {
                                $video->update([
                                    'vimeo_id' => $result['video_id'] ?? null,
                                    'vimeo_embed_url' => $result['embed_url'] ?? null,
                                    'status' => 'approved',
                                    'temp_file_path' => null,
                                    'temp_expires_at' => null
                                ]);
                                Storage::disk('public')->delete($tempPath);
                            } else {
                                $video->update(['status' => 'rejected']);
                                $errorMsg = is_array($result) ? ($result['error'] ?? 'Unknown error') : 'Vimeo service returned invalid response';
                                throw new \Exception('Failed to upload to Vimeo: ' . $errorMsg);
                            }
                        } catch (\Exception $vimeoError) {
                            $video->update(['status' => 'rejected']);
                            throw $vimeoError;
                        }
                    }
                }
            }

            // Handle thumbnail
            if ($request->hasFile('thumbnail_file')) {
                $thumbnailFile = $request->file('thumbnail_file');
                $thumbnailFilename = time() . '_thumb_' . $video->id . '.' . $thumbnailFile->getClientOriginalExtension();
                $thumbnailPath = $thumbnailFile->storeAs('thumbnails', $thumbnailFilename, 'public');
                $video->update(['thumbnail_path' => $thumbnailPath]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Video uploaded successfully',
                'data' => ['video_id' => $video->id]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Video upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getFile() . ':' . $e->getLine(),
                'request_data' => [
                    'title' => $request->input('title'),
                    'subject_id' => $request->input('subject_id'),
                    'has_upload_id' => $request->filled('upload_id'),
                    'has_video_file' => $request->hasFile('video_file')
                ]
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload documents component only
     */
    public function uploadDocumentsComponent(Request $request)
    {
        try {
            Log::info('Documents upload component request received', [
                'has_video_id' => $request->filled('video_id'),
                'video_id' => $request->input('video_id'),
                'has_documents' => $request->hasFile('documents'),
                'all_params' => array_keys($request->all())
            ]);

            // Manually load config if not cached
            $uploadConfig = config('uploads');
            if (!$uploadConfig) {
                Log::warning('Config cache missing, reloading from file');
                $uploadConfig = include config_path('uploads.php');
            }
            
            if (!$uploadConfig || !is_array($uploadConfig)) {
                throw new \Exception('Upload configuration not found or invalid');
            }
            
            // Ensure required config keys exist
            if (empty($uploadConfig['document'])) {
                throw new \Exception('Upload configuration missing document config');
            }
            
            $documentMaxSize = ($uploadConfig['document']['max_size'] ?? 10485760) / 1024; // Convert bytes to KB
            
            Log::info('Documents config loaded', [
                'has_document_config' => !empty($uploadConfig['document']),
                'document_max_size' => $documentMaxSize
            ]);
            
            $request->validate([
                'video_id' => 'required|exists:videos,id',
                'documents' => 'required|array',
                'documents.*' => 'file|mimes:' . implode(',', $uploadConfig['document']['mimes']) . '|max:' . $documentMaxSize
            ], [
                'documents.*.max' => 'Each document cannot exceed ' . $uploadConfig['document']['max_size_display'] . '.',
                'documents.*.mimes' => 'Documents must be one of: ' . implode(', ', $uploadConfig['document']['mimes']) . '.',
            ]);

            Log::info('Documents upload validation passed', [
                'video_id' => $request->video_id,
                'documents_count' => count($request->file('documents'))
            ]);

            $video = Video::findOrFail($request->video_id);
            $documents = [];

            foreach ($request->file('documents') as $docFile) {
                $filename = time() . '_' . uniqid() . '.' . $docFile->getClientOriginalExtension();
                $path = $docFile->storeAs('documents', $filename, 'public');

                $docData = [
                    'title' => pathinfo($docFile->getClientOriginalName(), PATHINFO_FILENAME),
                    'file_path' => $path,
                    'description' => 'Related document for video: ' . $video->title,
                    'uploaded_by' => Auth::id(),
                    'video_id' => $video->id,
                    'grade_level' => $video->grade_level
                ];

                $document = Document::create($docData);
                $documents[] = $document;
            }

            return response()->json([
                'success' => true,
                'message' => count($documents) . ' documents uploaded successfully',
                'data' => ['documents_count' => count($documents)]
            ]);

        } catch (\Exception $e) {
            Log::error('Documents upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getFile() . ':' . $e->getLine(),
                'request_data' => [
                    'video_id' => $request->input('video_id'),
                    'has_documents' => $request->hasFile('documents')
                ]
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload quiz component only
     */
    public function uploadQuizComponent(Request $request)
    {
        try {
            Log::info('Quiz upload component request received', [
                'has_video_id' => $request->filled('video_id'),
                'video_id' => $request->input('video_id'),
                'has_quiz_data' => $request->filled('quiz_data'),
                'all_params' => array_keys($request->all())
            ]);

            $request->validate([
                'video_id' => 'required|exists:videos,id',
                'quiz_data' => 'required|string',
                'difficulty_level' => 'required|string',
                'time_limit_minutes' => 'required|integer|min:1'
            ]);

            Log::info('Quiz upload validation passed', [
                'video_id' => $request->video_id,
                'difficulty_level' => $request->difficulty_level,
                'time_limit_minutes' => $request->time_limit_minutes
            ]);

            $video = Video::findOrFail($request->video_id);
            $quizData = json_decode($request->quiz_data, true);

            if (empty($quizData['questions'])) {
                throw new \Exception('No questions found in quiz data');
            }

            // Find or create subject
            $subject = Subject::firstOrCreate(
                ['name' => $video->title],
                ['description' => 'Auto-created subject for quiz']
            );

            $quizDataToCreate = [
                'title' => 'Quiz for: ' . $video->title,
                'subject_id' => $subject->id,
                'uploaded_by' => Auth::id(),
                'grade_level' => $video->grade_level,
                'video_id' => $video->id,
                'quiz_data' => json_encode($quizData),
                'is_featured' => false
            ];

            $quiz = Quiz::create($quizDataToCreate);

            // Update video's quiz_id for consistency
            $video->quiz_id = $quiz->id;
            $video->save();

            return response()->json([
                'success' => true,
                'message' => 'Quiz uploaded successfully',
                'data' => [
                    'quiz_id' => $quiz->id,
                    'questions_count' => $quiz->questions()->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Quiz upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getFile() . ':' . $e->getLine(),
                'request_data' => [
                    'video_id' => $request->input('video_id'),
                    'has_quiz_data' => $request->filled('quiz_data')
                ]
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete video from Vimeo only (called after database deletion)
     */
    public function destroyVimeoVideo(Request $request)
    {
        $request->validate([
            'vimeo_id' => 'required|string',
            'video_id' => 'required|integer'
        ]);

        $vimeoId = $request->vimeo_id;
        $videoId = $request->video_id;

        try {
            Log::info('Attempting to delete video from Vimeo only', [
                'video_id' => $videoId,
                'vimeo_id' => $vimeoId,
                'admin_id' => Auth::id()
            ]);

            $vimeoService = new \App\Services\VimeoService();
            $deletionSuccess = $vimeoService->deleteVideo($vimeoId);

            if ($deletionSuccess) {
                Log::info('Successfully deleted video from Vimeo', [
                    'video_id' => $videoId,
                    'vimeo_id' => $vimeoId,
                    'admin_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Video successfully deleted from Vimeo!'
                ]);
            } else {
                Log::warning('Failed to delete video from Vimeo', [
                    'video_id' => $videoId,
                    'vimeo_id' => $vimeoId,
                    'admin_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete video from Vimeo. It may have already been deleted or you may not have permission.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Exception deleting video from Vimeo', [
                'video_id' => $videoId,
                'vimeo_id' => $vimeoId,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting from Vimeo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete YouTube content and all related data
     */
    public function destroyYouTubeContent(Request $request, $contentId)
    {
        try {
            DB::beginTransaction();

            // Find the video content
            $video = Video::findOrFail($contentId);

            // Verify it's YouTube content
            if ($video->video_source !== 'youtube') {
                return response()->json([
                    'success' => false,
                    'message' => 'This content is not a YouTube video.'
                ], 400);
            }

            // Log the deletion action
            Log::channel('security')->info('youtube_content_deletion_started', [
                'admin_id' => Auth::id(),
                'video_id' => $video->id,
                'video_title' => $video->title,
                'youtube_video_id' => $video->external_video_id,
                'ip' => get_client_ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);

            // Delete related comments
            $commentsCount = Comment::where('video_id', $video->id)->count();
            Comment::where('video_id', $video->id)->delete();

            // Delete related documents
            $documentsCount = Document::where('video_id', $video->id)->count();
            $documents = Document::where('video_id', $video->id)->get();
            foreach ($documents as $document) {
                if ($document->file_path) {
                    Storage::disk('public')->delete($document->file_path);
                }
                $document->delete();
            }

            // Delete related quizzes and their attempts/ratings
            $quizzesCount = Quiz::where('video_id', $video->id)->count();
            $quizzes = Quiz::where('video_id', $video->id)->get();
            foreach ($quizzes as $quiz) {
                // Delete quiz attempts
                QuizAttempt::where('quiz_id', $quiz->id)->delete();
                // Delete quiz ratings
                QuizRating::where('quiz_id', $quiz->id)->delete();
                // Delete the quiz
                $quiz->delete();
            }

            // Delete the video itself (this will also delete any associated files via the model's deleteFiles method)
            $video->deleteFiles();
            $video->delete();

            DB::commit();

            Log::channel('security')->info('youtube_content_deletion_completed', [
                'admin_id' => Auth::id(),
                'video_id' => $video->id,
                'video_title' => $video->title,
                'deleted_comments' => $commentsCount,
                'deleted_documents' => $documentsCount,
                'deleted_quizzes' => $quizzesCount,
                'ip' => get_client_ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'YouTube content and all related data deleted successfully.',
                'deleted_items' => [
                    'comments' => $commentsCount,
                    'documents' => $documentsCount,
                    'quizzes' => $quizzesCount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('youtube_content_deletion_failed', [
                'admin_id' => Auth::id(),
                'video_id' => $contentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => get_client_ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete YouTube content: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle chunked video upload (for large files)
     * Uploads video in chunks to overcome server size limits
     */
    public function uploadVideoChunk(\App\Http\Requests\ChunkedVideoUploadRequest $request)
    {
        try {
            $uploadConfig = config('uploads');
            $uploadId = $request->input('upload_id');
            $chunkIndex = (int) $request->input('chunk_index', 0);
            $totalChunks = (int) $request->input('total_chunks', 0);
            $filename = $request->input('filename');
            
            // Validate inputs
            if (empty($uploadId) || empty($filename) || $totalChunks <= 0) {
                throw new \Exception('Missing required fields: upload_id, filename, or total_chunks');
            }
            
            // Create temporary storage directory for chunks using public disk
            $tempChunksDir = storage_path('app/public/temp_chunks/' . $uploadId);
            if (!file_exists($tempChunksDir)) {
                mkdir($tempChunksDir, 0755, true);
            }
            
            // Store the chunk
            $chunkFile = $request->file('chunk');
            if (!$chunkFile) {
                throw new \Exception('No chunk file provided');
            }
            
            $chunkPath = $tempChunksDir . '/chunk_' . $chunkIndex;
            $chunkFile->move($tempChunksDir, 'chunk_' . $chunkIndex);
            
            // Check if all chunks are uploaded
            $uploadedChunks = count(glob($tempChunksDir . '/chunk_*'));
            
            if ($uploadedChunks === $totalChunks) {
                // All chunks uploaded, reassemble the file
                $tempVideosDir = storage_path('app/public/temp_videos');
                if (!file_exists($tempVideosDir)) {
                    mkdir($tempVideosDir, 0755, true);
                }
                $finalPath = $tempVideosDir . '/' . $uploadId . '_' . $filename;
                $finalDir = dirname($finalPath);
                
                if (!file_exists($finalDir)) {
                    mkdir($finalDir, 0755, true);
                }
                
                $finalFile = fopen($finalPath, 'wb');
                
                if (!$finalFile) {
                    throw new \Exception('Could not create final video file');
                }
                
                // Reassemble chunks in order
                for ($i = 0; $i < $totalChunks; $i++) {
                    $chunkPath = $tempChunksDir . '/chunk_' . $i;
                    if (!file_exists($chunkPath)) {
                        throw new \Exception('Missing chunk ' . $i);
                    }
                    
                    $chunkContent = file_get_contents($chunkPath);
                    fwrite($finalFile, $chunkContent);
                    unlink($chunkPath); // Delete chunk after writing
                }
                
                fclose($finalFile);
                
                // Clean up temp directory
                if (file_exists($tempChunksDir)) {
                    rmdir($tempChunksDir);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'All chunks uploaded successfully',
                    'data' => [
                        'upload_id' => $uploadId,
                        'completed' => true,
                        'temp_path' => 'temp_videos/' . $uploadId . '_' . $filename
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Chunk ' . $chunkIndex . ' uploaded successfully',
                'data' => [
                    'upload_id' => $uploadId,
                    'chunk_index' => $chunkIndex,
                    'uploaded_chunks' => $uploadedChunks,
                    'total_chunks' => $totalChunks,
                    'completed' => false
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chunked video upload failed', [
                'error' => $e->getMessage(),
                'upload_id' => $request->input('upload_id') ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
