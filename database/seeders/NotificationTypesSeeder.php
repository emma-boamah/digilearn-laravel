<?php

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notificationTypes = [
            // System Announcements
            [
                'slug' => 'system_announcements',
                'name' => 'System Announcements',
                'description' => 'Important announcements from the DigiLearn team',
                'icon' => 'fas fa-bullhorn',
                'color' => '#3b82f6',
                'is_system' => true,
                'is_active' => true,
                'default_channels' => ['database', 'mail'],
                'priority' => 'critical',
                'default_message' => 'Please be advised of an important system update. Let us know if you have any questions.',
            ],

            // Payment Notifications
            [
                'slug' => 'payment_successful',
                'name' => 'Payment Confirmations',
                'description' => 'Notifications when payments are processed successfully',
                'icon' => 'fas fa-check-circle',
                'color' => '#10b981',
                'is_system' => false,
                'is_active' => true,
                'default_channels' => ['database', 'mail'],
                'priority' => 'high',
                'default_message' => 'Your recent payment was processed successfully. Thank you for your purchase!',
            ],
            [
                'slug' => 'payment_failed',
                'name' => 'Payment Failures',
                'description' => 'Notifications when payments fail',
                'icon' => 'fas fa-times-circle',
                'color' => '#ef4444',
                'is_system' => false,
                'is_active' => true,
                'default_channels' => ['database', 'mail'],
                'priority' => 'high',
                'default_message' => 'Your recent payment has failed. Please update your payment method to continue accessing your content.',
            ],

            // Subscription Notifications
            [
                'slug' => 'subscription_expired',
                'name' => 'Subscription Expiry',
                'description' => 'Notifications when subscriptions are about to expire',
                'icon' => 'fas fa-clock',
                'color' => '#f59e0b',
                'is_system' => false,
                'is_active' => true,
                'default_channels' => ['database', 'mail'],
                'priority' => 'high',
                'default_message' => 'Your subscription is set to expire soon. Please renew it to ensure uninterrupted access.',
            ],
            [
                'slug' => 'subscription_renewed',
                'name' => 'Subscription Renewals',
                'description' => 'Notifications when subscriptions are renewed',
                'icon' => 'fas fa-sync',
                'color' => '#10b981',
                'is_system' => false,
                'is_active' => true,
                'default_channels' => ['database', 'mail'],
                'priority' => 'medium',
                'default_message' => 'Your subscription has been successfully renewed. Thank you for staying with us!',
            ],

            // Learning Progress Notifications
            [
                'slug' => 'lesson_completed',
                'name' => 'Lesson Completions',
                'description' => 'Notifications when lessons are completed',
                'icon' => 'fas fa-graduation-cap',
                'color' => '#8b5cf6',
                'is_system' => false,
                'is_active' => true,
                'default_channels' => ['database'],
                'priority' => 'low',
                'default_message' => 'Congratulations on completing this lesson! Keep up the great work.',
            ],
            [
                'slug' => 'quiz_completed',
                'name' => 'Quiz Results',
                'description' => 'Notifications when quizzes are completed',
                'icon' => 'fas fa-brain',
                'color' => '#06b6d4',
                'is_system' => false,
                'is_active' => true,
                'default_channels' => ['database'],
                'priority' => 'medium',
                'default_message' => 'Your recent quiz results are now available for review.',
            ],

            // Class Notifications
            [
                'slug' => 'class_started',
                'name' => 'Virtual Classes',
                'description' => 'Notifications about virtual class sessions',
                'icon' => 'fas fa-video',
                'color' => '#ec4899',
                'is_system' => false,
                'is_active' => true,
                'default_channels' => ['database', 'mail'],
                'priority' => 'high',
                'default_message' => 'A virtual class you are enrolled in is starting soon. Please join the session.',
            ],

            // Message Notifications
            [
                'slug' => 'message_received',
                'name' => 'Messages',
                'description' => 'Notifications for new messages',
                'icon' => 'fas fa-envelope',
                'color' => '#6366f1',
                'is_system' => false,
                'is_active' => true,
                'default_channels' => ['database'],
                'priority' => 'medium',
                'default_message' => 'You have received a new message.',
            ],

            // Achievement Notifications
            [
                'slug' => 'achievement_unlocked',
                'name' => 'Achievements',
                'description' => 'Notifications when achievements are unlocked',
                'icon' => 'fas fa-trophy',
                'color' => '#f59e0b',
                'is_system' => false,
                'is_active' => true,
                'default_channels' => ['database'],
                'priority' => 'medium',
                'default_message' => 'Congratulations! You have unlocked a new achievement.',
            ],
        ];

        foreach ($notificationTypes as $type) {
            NotificationType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}