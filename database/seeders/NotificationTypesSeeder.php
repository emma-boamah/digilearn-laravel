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