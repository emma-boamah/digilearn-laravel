<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Monitor authentication logs every 6 hours and send alerts
Schedule::command('auth:monitor --hours=24 --alert --silent --email=contact@shoutoutgh.com')
        ->everySixHours()
        ->withoutOverlapping()
        ->runInBackground();

// Schedule video cleanup to run daily at 2 AM
Schedule::command('videos:cleanup-expired')->dailyAt('02:00');

// Update lesson completions for users who meet progression thresholds
// Run daily at 2:30 AM
Schedule::command('lesson-completions:update')
        ->dailyAt('02:30')
        ->withoutOverlapping()
        ->runInBackground();

// Clear email verification cache weekly on Sundays at 3 AM
Schedule::command('cache:clear-email-verification')
        ->weekly()
        ->sundays()
        ->at('03:00')
        ->withoutOverlapping()
        ->runInBackground();

// Clean up invalid avatar files weekly on Sundays at 4 AM
Schedule::command('cleanup:invalid-avatars')
        ->weekly()
        ->sundays()
        ->at('04:00')
        ->withoutOverlapping()
        ->runInBackground();

// Monitor storage usage every hour
Schedule::command('storage:monitor')
        ->hourly()
        ->withoutOverlapping()
        ->runInBackground();

// Monitor storage usage with more frequent checks during peak hours (9 AM - 6 PM)
Schedule::command('storage:monitor')
        ->daily()
        ->when(function () {
            $hour = now()->hour;
            return $hour >= 9 && $hour <= 18; // 9 AM to 6 PM
        })
        ->everyFifteenMinutes()
        ->withoutOverlapping()
        ->runInBackground();

// Expire subscriptions and manage grace periods daily at midnight
Schedule::command('subscription:expire')
        ->dailyAt('00:05')
        ->withoutOverlapping()
        ->runInBackground();

// Monitor subscription expiry daily at 9 AM
Schedule::command('subscription:monitor-expiry')
        ->dailyAt('09:00')
        ->withoutOverlapping()
        ->runInBackground();
