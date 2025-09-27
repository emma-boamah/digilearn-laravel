<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationType;
use App\Models\UserNotificationPreference;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    /**
     * Send a notification to a single user.
     */
    public function sendToUser(User $user, Notification $notification, ?array $channels = null): void
    {
        try {
            $channels = $channels ?? ['database'];
            $user->notify($notification);

            Log::info('Notification sent to user', [
                'user_id' => $user->id,
                'notification_type' => get_class($notification),
                'channels' => $channels,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification to user', [
                'user_id' => $user->id,
                'notification_type' => get_class($notification),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send a notification to multiple users.
     */
    public function sendToUsers(Collection $users, Notification $notification, ?array $channels = null): void
    {
        foreach ($users as $user) {
            $this->sendToUser($user, $notification, $channels);
        }
    }

    /**
     * Send a system announcement to all users.
     */
    public function sendSystemAnnouncement(string $title, string $message, ?string $url = null, ?array $channels = null): void
    {
        $users = User::where('is_verified', true)->get();

        $notification = new \App\Notifications\SystemAnnouncementNotification($title, $message, $url);

        $this->sendToUsers($users, $notification, $channels);

        Log::info('System announcement sent', [
            'title' => $title,
            'user_count' => $users->count(),
        ]);
    }

    /**
     * Send a notification to users with specific criteria.
     */
    public function sendToUsersByCriteria(array $criteria, Notification $notification, ?array $channels = null): void
    {
        $query = User::query();

        // Apply criteria filters
        if (isset($criteria['country'])) {
            $query->where('country', $criteria['country']);
        }

        if (isset($criteria['grade'])) {
            $query->where('grade', $criteria['grade']);
        }

        if (isset($criteria['education_level'])) {
            $query->where('education_level', $criteria['education_level']);
        }

        if (isset($criteria['has_active_subscription'])) {
            if ($criteria['has_active_subscription']) {
                $query->whereHas('currentSubscription');
            } else {
                $query->whereDoesntHave('currentSubscription');
            }
        }

        if (isset($criteria['user_ids'])) {
            $query->whereIn('id', $criteria['user_ids']);
        }

        $users = $query->where('is_verified', true)->get();

        $this->sendToUsers($users, $notification, $channels);

        Log::info('Targeted notification sent', [
            'criteria' => $criteria,
            'user_count' => $users->count(),
        ]);
    }

    /**
     * Get user's notification preferences for a specific type.
     */
    public function getUserPreferences(User $user, string $notificationTypeSlug): UserNotificationPreference
    {
        $notificationType = NotificationType::where('slug', $notificationTypeSlug)->first();

        if (!$notificationType) {
            throw new \Exception("Notification type '{$notificationTypeSlug}' not found");
        }

        return UserNotificationPreference::firstOrCreate(
            [
                'user_id' => $user->id,
                'notification_type_id' => $notificationType->id,
            ],
            [
                'channels' => $notificationType->default_channels ?? ['database'],
                'is_enabled' => true,
            ]
        );
    }

    /**
     * Update user's notification preferences.
     */
    public function updateUserPreferences(User $user, string $notificationTypeSlug, array $preferences): void
    {
        $userPreference = $this->getUserPreferences($user, $notificationTypeSlug);

        $userPreference->update([
            'channels' => $preferences['channels'] ?? $userPreference->channels,
            'is_enabled' => $preferences['is_enabled'] ?? $userPreference->is_enabled,
        ]);

        // Clear cache
        Cache::forget("user_notification_prefs_{$user->id}_{$notificationTypeSlug}");
    }

    /**
     * Check if user should receive notification based on preferences.
     */
    public function shouldSendToUser(User $user, string $notificationTypeSlug, string $channel = 'database'): bool
    {
        try {
            $preferences = $this->getUserPreferences($user, $notificationTypeSlug);
            return $preferences->isChannelEnabled($channel);
        } catch (\Exception $e) {
            // If preferences don't exist, default to enabled for database channel
            return $channel === 'database';
        }
    }

    /**
     * Get user's unread notifications count.
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(User $user, string $notificationId): void
    {
        $notification = $user->notifications()->find($notificationId);

        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all notifications as read for user.
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    /**
     * Get user's notifications with pagination.
     */
    public function getUserNotifications(User $user, int $perPage = 20, bool $onlyUnread = false)
    {
        $query = $user->notifications();

        if ($onlyUnread) {
            $query->whereNull('read_at');
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new notification type.
     */
    public function createNotificationType(array $data): NotificationType
    {
        return NotificationType::create($data);
    }

    /**
     * Get all active notification types.
     */
    public function getActiveNotificationTypes(): Collection
    {
        return Cache::remember('active_notification_types', 3600, function () {
            return NotificationType::active()->orderBy('priority', 'desc')->get();
        });
    }

    /**
     * Initialize default notification preferences for a user.
     */
    public function initializeUserPreferences(User $user): void
    {
        $notificationTypes = $this->getActiveNotificationTypes();

        foreach ($notificationTypes as $type) {
            UserNotificationPreference::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_type_id' => $type->id,
                ],
                [
                    'channels' => $type->default_channels ?? ['database'],
                    'is_enabled' => true,
                ]
            );
        }
    }

    /**
     * Send event-based notification.
     */
    public function sendEventNotification(string $eventType, array $data, ?User $user = null): void
    {
        $notificationClass = $this->getNotificationClassForEvent($eventType);

        if (!$notificationClass) {
            Log::warning("No notification class found for event: {$eventType}");
            return;
        }

        $notification = new $notificationClass($data);

        if ($user) {
            $this->sendToUser($user, $notification);
        } else {
            // Handle system-wide event notifications
            $this->handleSystemEvent($eventType, $data);
        }
    }

    /**
     * Get notification class for event type.
     */
    private function getNotificationClassForEvent(string $eventType): ?string
    {
        return match($eventType) {
            'payment.successful' => \App\Notifications\PaymentSuccessfulNotification::class,
            'payment.failed' => \App\Notifications\PaymentFailedNotification::class,
            'subscription.expired' => \App\Notifications\SubscriptionExpiredNotification::class,
            'subscription.renewed' => \App\Notifications\SubscriptionRenewedNotification::class,
            'class.started' => \App\Notifications\ClassStartedNotification::class,
            'quiz.completed' => \App\Notifications\QuizCompletedNotification::class,
            'lesson.completed' => \App\Notifications\LessonCompletedNotification::class,
            'message.received' => \App\Notifications\MessageReceivedNotification::class,
            default => null,
        };
    }

    /**
     * Handle system-wide event notifications.
     */
    private function handleSystemEvent(string $eventType, array $data): void
    {
        // Handle events that need to be sent to multiple users
        switch ($eventType) {
            case 'system.maintenance':
                $this->sendSystemAnnouncement(
                    'System Maintenance',
                    $data['message'] ?? 'The system will be undergoing maintenance.',
                    $data['url'] ?? null
                );
                break;

            case 'new.feature':
                $this->sendSystemAnnouncement(
                    'New Feature Available',
                    $data['message'] ?? 'Check out our latest feature!',
                    $data['url'] ?? route('dashboard.main')
                );
                break;

            default:
                Log::info("Unhandled system event: {$eventType}", $data);
                break;
        }
    }

    /**
     * Send notification to all users.
     */
    public function sendToAllUsers(Notification $notification, ?array $channels = null): void
    {
        $users = User::where('is_verified', true)->get();
        $this->sendToUsers($users, $notification, $channels);

        Log::info('Notification sent to all users', [
            'notification_type' => get_class($notification),
            'user_count' => $users->count(),
            'channels' => $channels,
        ]);
    }

    /**
     * Send notification to specific users by IDs.
     */
    public function sendToSpecificUsers(array $userIds, Notification $notification, ?array $channels = null): void
    {
        $users = User::whereIn('id', $userIds)->where('is_verified', true)->get();
        $this->sendToUsers($users, $notification, $channels);

        Log::info('Notification sent to specific users', [
            'notification_type' => get_class($notification),
            'user_ids' => $userIds,
            'user_count' => $users->count(),
            'channels' => $channels,
        ]);
    }

    /**
     * Get recent notifications for admin dashboard.
     */
    public function getRecentNotifications(int $limit = 10)
    {
        return \App\Models\Notification::with(['notifiable', 'notificationType'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}