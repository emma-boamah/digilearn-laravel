<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
