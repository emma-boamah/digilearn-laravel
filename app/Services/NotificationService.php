<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationType;
use App\Models\UserNotificationPreference;
use App\Models\UserPreference;
use App\Models\Video;
use App\Models\Document;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        $users = User::whereNotNull('email_verified_at')->get();

        $notification = new \App\Notifications\SystemAnnouncementNotification($title, $message, $this->normalizeUrl($url));

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

        $users = $query->whereNotNull('email_verified_at')->get();

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
    public function getUserNotifications(User $user, int $perPage = 20, bool $onlyUnread = false, ?string $type = null)
    {
        $query = $user->notifications();

        if ($onlyUnread) {
            $query->whereNull('read_at');
        }

        if ($type) {
            if ($type === 'announcement') {
                $query->where(function ($q) {
                    $q->where('type', 'like', '%SystemAnnouncement%')
                      ->orWhere('type', 'like', '%AdminNotification%')
                      ->orWhere('type', 'like', '%StorageAlert%')
                      ->orWhere('type', 'like', '%ClassStarted%');
                });
            } else {
                $query->where('type', 'like', "%{$type}%");
            }
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
        $users = User::whereNotNull('email_verified_at')->get();
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
        $users = User::whereIn('id', $userIds)->whereNotNull('email_verified_at')->get();
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

    /**
     * Helper to determine level group from grade string.
     */
    private function getLevelGroupFromGrade(?string $grade): ?string
    {
        if (!$grade) return null;

        $slug = strtolower(str_replace(' ', '-', $grade));
        
        if (str_contains($slug, 'primary-1') || str_contains($slug, 'primary-2') || str_contains($slug, 'primary-3')) return 'primary-lower';
        if (str_contains($slug, 'primary-4') || str_contains($slug, 'primary-5') || str_contains($slug, 'primary-6')) return 'primary-upper';
        if (str_contains($slug, 'jhs')) return 'jhs';
        if (str_contains($slug, 'shs')) return 'shs';
        if (str_contains($slug, 'uni') || str_contains($slug, 'tertiary')) return 'university';
        
        return null;
    }

    /**
     * Get users interested in a specific grade level.
     */
    private function getUsersForGradeLevel(?string $gradeLevel): Collection
    {
        $query = User::whereNotNull('email_verified_at');
        
        $levelGroup = $this->getLevelGroupFromGrade($gradeLevel);

        if ($levelGroup) {
            $grades = $this->getGradesForLevelGroup($levelGroup);
            // Include the specific gradeLevel as well to ensure coverage
            if ($gradeLevel && !in_array($gradeLevel, $grades)) {
                $grades[] = $gradeLevel;
            }
            
            $query->whereIn('grade', $grades);
        }

        // Exclude users who have opted out of this specific grade level
        $query->whereNotExists(function ($q) use ($gradeLevel) {
            $q->select(DB::raw(1))
              ->from('user_preferences')
              ->whereColumn('user_preferences.user_id', 'users.id')
              ->where('user_preferences.preference_type', 'opt_out_grade_notification')
              ->where('user_preferences.preference_value', $gradeLevel);
        });

        return $query->get();
    }

    /**
     * Get grades for a level group.
     */
    private function getGradesForLevelGroup(string $levelGroup): array
    {
        $levelMappings = [
            'primary-lower' => ['Primary 1', 'Primary 2', 'Primary 3'],
            'primary-upper' => ['Primary 4', 'Primary 5', 'Primary 6'],
            'jhs' => ['JHS 1', 'JHS 2', 'JHS 3'],
            'shs' => ['SHS 1', 'SHS 2', 'SHS 3'],
            'university' => ['University Year 1', 'University Year 2', 'University Year 3', 'University Year 4'],
        ];

        return $levelMappings[$levelGroup] ?? [];
    }

    /**
     * Set user's grade notification preference (opt-out).
     */
    public function setGradeNotificationPreference(User $user, string $grade, bool $optOut): void
    {
        if ($optOut) {
            UserPreference::firstOrCreate([
                'user_id' => $user->id,
                'preference_type' => 'opt_out_grade_notification',
                'preference_value' => $grade
            ]);
        } else {
            UserPreference::where('user_id', $user->id)
                ->where('preference_type', 'opt_out_grade_notification')
                ->where('preference_value', $grade)
                ->delete();
        }
    }

    /**
     * Send notification for new video content.
     */
    public function notifyNewVideo(Video $video): void
    {
        $users = $this->getUsersForGradeLevel($video->grade_level);

        $notification = new \App\Notifications\NewVideoNotification($video);

        $sentCount = 0;
        foreach ($users as $user) {
            // Check user preferences before sending - use system_announcements for new content
            if ($this->shouldSendToUser($user, 'system_announcements', 'database')) {
                $this->sendToUser($user, $notification);
                $sentCount++;
            }
        }

        Log::info('New video notification sent', [
            'video_id' => $video->id,
            'video_title' => $video->title,
            'total_users' => $users->count(),
            'notifications_sent' => $sentCount,
        ]);
    }

    /**
     * Send notification for new document content.
     */
    public function notifyNewDocument(Document $document): void
    {
        $users = $this->getUsersForGradeLevel($document->grade_level);

        $notification = new \App\Notifications\NewDocumentNotification($document);

        foreach ($users as $user) {
            // Check user preferences before sending - using system_announcements for consistency
            if ($this->shouldSendToUser($user, 'system_announcements', 'database')) {
                $this->sendToUser($user, $notification);
            }
        }

        Log::info('New document notification sent', [
            'document_id' => $document->id,
            'document_title' => $document->title,
            'user_count' => $users->count(),
        ]);
    }

    /**
     * Send notification for new quiz content.
     */
    public function notifyNewQuiz(Quiz $quiz): void
    {
        $users = $this->getUsersForGradeLevel($quiz->grade_level);

        $notification = new \App\Notifications\NewQuizNotification($quiz);

        foreach ($users as $user) {
            // Check user preferences before sending - using system_announcements for consistency
            if ($this->shouldSendToUser($user, 'system_announcements', 'database')) {
                $this->sendToUser($user, $notification);
            }
        }

        Log::info('New quiz notification sent', [
            'quiz_id' => $quiz->id,
            'quiz_title' => $quiz->title,
            'user_count' => $users->count(),
        ]);
    }

    /**
     * Normalize URL to ensure it's a relative path only.
     */
    protected function normalizeUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        // Already relative
        if (str_starts_with($url, '/')) {
            return $url;
        }

        // Strip protocol + host if someone bypasses validation
        $parsed = parse_url($url);

        return $parsed['path'] ?? '/';
    }
}