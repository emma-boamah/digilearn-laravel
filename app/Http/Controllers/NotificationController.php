<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 20);
        $onlyUnread = $request->boolean('unread_only', false);

        $notifications = $this->notificationService->getUserNotifications($user, $perPage, $onlyUnread);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->notificationService->markAsRead($user, $notificationId);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'unread_count' => $this->notificationService->getUnreadCount($user),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
            ], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->notificationService->markAllAsRead($user);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'unread_count' => 0,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notifications as read',
            ], 500);
        }
    }

    /**
     * Get unread count.
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Get user notification preferences.
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = Auth::user();
        $notificationTypes = $this->notificationService->getActiveNotificationTypes();

        $preferences = [];
        foreach ($notificationTypes as $type) {
            $preferences[$type->slug] = $this->notificationService->getUserPreferences($user, $type->slug);
        }

        return response()->json([
            'success' => true,
            'preferences' => $preferences,
            'available_channels' => \App\Models\NotificationType::getAvailableChannels(),
        ]);
    }

    /**
     * Update user notification preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*.type' => 'required|string',
            'preferences.*.channels' => 'array',
            'preferences.*.channels.*' => 'in:database,mail,broadcast',
            'preferences.*.is_enabled' => 'boolean',
        ]);

        try {
            foreach ($validated['preferences'] as $preference) {
                $this->notificationService->updateUserPreferences(
                    $user,
                    $preference['type'],
                    [
                        'channels' => $preference['channels'] ?? ['database'],
                        'is_enabled' => $preference['is_enabled'] ?? true,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification preferences updated successfully',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update preferences',
            ], 500);
        }
    }

    /**
     * Send system announcement (Admin only).
     */
    public function sendSystemAnnouncement(Request $request): JsonResponse
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'url' => 'nullable|string|regex:/^\/.*/',
            'channels' => 'array',
            'channels.*' => 'in:database,mail,broadcast',
        ]);

        try {
            $this->notificationService->sendSystemAnnouncement(
                $validated['title'],
                $validated['message'],
                $validated['url'] ?? null,
                $validated['channels'] ?? ['database', 'mail']
            );

            return response()->json([
                'success' => true,
                'message' => 'System announcement sent successfully',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send system announcement',
            ], 500);
        }
    }

    /**
     * Send targeted notification (Admin only).
     */
    public function sendTargetedNotification(Request $request): JsonResponse
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'url' => 'nullable|string|regex:/^\/.*/',
            'criteria' => 'required|array',
            'criteria.country' => 'nullable|string',
            'criteria.grade' => 'nullable|string',
            'criteria.education_level' => 'nullable|string',
            'criteria.has_active_subscription' => 'nullable|boolean',
            'criteria.user_ids' => 'nullable|array',
            'criteria.user_ids.*' => 'integer|exists:users,id',
            'channels' => 'array',
            'channels.*' => 'in:database,mail,broadcast',
        ]);

        try {
            $notification = new \App\Notifications\TargetedNotification(
                $validated['title'],
                $validated['message'],
                $validated['url'] ?? null
            );

            $this->notificationService->sendToUsersByCriteria(
                $validated['criteria'],
                $notification,
                $validated['channels'] ?? ['database']
            );

            return response()->json([
                'success' => true,
                'message' => 'Targeted notification sent successfully',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send targeted notification',
            ], 500);
        }
    }

    /**
     * Delete notification.
     */
    public function destroy(Request $request, string $notificationId): JsonResponse
    {
        $user = Auth::user();

        try {
            $notification = $user->notifications()->findOrFail($notificationId);
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification',
            ], 500);
        }
    }

    /**
     * Admin notifications index page.
     */
    public function adminIndex(Request $request)
    {
        $notificationTypes = $this->notificationService->getActiveNotificationTypes();
        $recentNotifications = $this->notificationService->getRecentNotifications(10);

        // Get statistics with caching
        $stats = [
            'total_notifications' => Cache::remember('admin_stats_total_notifications', 300, fn() => \App\Models\Notification::count()),
            'active_types' => $notificationTypes->count(),
            'system_announcements' => Cache::remember('admin_stats_system_announcements', 300, fn() => \App\Models\Notification::where('type', 'system')->count()),
            'unread_count' => Cache::remember('admin_stats_unread_count', 300, fn() => \App\Models\Notification::whereNull('read_at')->count()),
        ];

        return view('admin.notifications.index', compact('notificationTypes', 'recentNotifications', 'stats'));
    }

    /**
     * Admin show notification details.
     */
    public function adminShow(\App\Models\Notification $notification)
    {
        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Send notification from admin panel.
     */
    public function sendNotification(Request $request): JsonResponse
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'notification_type_id' => 'required|exists:notification_types,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'url' => 'nullable|string|regex:/^\/.*/',
            'send_type' => 'required|in:all,criteria,specific',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'criteria' => 'nullable|array',
            'criteria.*' => 'string',
            'channels' => 'array',
            'channels.*' => 'in:database,mail,broadcast',
        ]);

        try {
            $notificationType = \App\Models\NotificationType::findOrFail($validated['notification_type_id']);

            $notification = new \App\Notifications\AdminNotification(
                $validated['title'],
                $validated['message'],
                $validated['url'] ?? null,
                $notificationType
            );

            $channels = $validated['channels'] ?? ['database'];

            switch ($validated['send_type']) {
                case 'all':
                    $this->notificationService->sendToAllUsers($notification, $channels);
                    break;

                case 'specific':
                    if (empty($validated['user_ids'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Please select at least one user',
                        ], 422);
                    }
                    $this->notificationService->sendToSpecificUsers($validated['user_ids'], $notification, $channels);
                    break;

                case 'criteria':
                    $this->notificationService->sendToUsersByCriteria($validated['criteria'], $notification, $channels);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
            ], 500);
        }
    }

    /**
     * Get notification types for admin.
     */
    public function getNotificationTypes(Request $request): JsonResponse
    {
        Gate::authorize('admin');

        $types = $this->notificationService->getActiveNotificationTypes();

        return response()->json([
            'success' => true,
            'types' => $types,
        ]);
    }

    /**
     * Create notification type.
     */
    public function createNotificationType(Request $request): JsonResponse
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:notification_types,name',
            'slug' => 'required|string|max:255|unique:notification_types,slug',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'icon' => 'nullable|string|max:50',
            'default_channels' => 'array',
            'default_channels.*' => 'in:database,mail,broadcast',
            'is_active' => 'boolean',
        ]);

        try {
            $type = \App\Models\NotificationType::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Notification type created successfully',
                'type' => $type,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification type',
            ], 500);
        }
    }

    /**
     * Update notification type.
     */
    public function updateNotificationType(Request $request, \App\Models\NotificationType $type): JsonResponse
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:notification_types,name,' . $type->id,
            'slug' => 'required|string|max:255|unique:notification_types,slug,' . $type->id,
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'icon' => 'nullable|string|max:50',
            'default_channels' => 'array',
            'default_channels.*' => 'in:database,mail,broadcast',
            'is_active' => 'boolean',
        ]);

        try {
            $type->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Notification type updated successfully',
                'type' => $type,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification type',
            ], 500);
        }
    }

    /**
     * Delete notification type.
     */
    public function deleteNotificationType(Request $request, \App\Models\NotificationType $type): JsonResponse
    {
        Gate::authorize('admin');

        try {
            // Check if type is being used
            if ($type->notifications()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete notification type that has existing notifications',
                ], 422);
            }

            $type->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification type deleted successfully',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification type',
            ], 500);
        }
    }

    /**
     * Toggle notification type active status.
     */
    public function toggleNotificationType(Request $request, \App\Models\NotificationType $type): JsonResponse
    {
        Gate::authorize('admin');

        try {
            $type->update(['is_active' => !$type->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Notification type ' . ($type->is_active ? 'activated' : 'deactivated') . ' successfully',
                'is_active' => $type->is_active,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle notification type',
            ], 500);
        }
    }

    /**
     * Dashboard notifications page.
     */
    public function dashboardIndex(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 20);
        $onlyUnread = $request->boolean('unread_only', false);

        $notifications = $this->notificationService->getUserNotifications($user, $perPage, $onlyUnread);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return view('dashboard.notifications', compact('notifications', 'unreadCount'));
    }
}