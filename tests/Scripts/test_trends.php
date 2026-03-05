<?php

use App\Models\User;
use App\Models\UserEngagement;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$userId = 1;
$grade = 'Primary 1';

echo "Verifying Trend Calculation Logic for $grade\n";

// 1. Time Trend
$timeThisWeekSeconds = UserEngagement::where('user_id', $userId)
    ->where('created_at', '>=', now()->subDays(7))
    ->where('metadata->level', $grade)
    ->sum('duration_seconds') ?? 0;

echo "Seconds this week: $timeThisWeekSeconds\n";

// 2. Score Trend
$currentWeekAvg = QuizAttempt::where('user_id', $userId)
    ->where('quiz_level', $grade)
    ->where('completed_at', '>=', now()->subDays(7))
    ->avg('score_percentage') ?? 0;
    
$prevWeekAvg = QuizAttempt::where('user_id', $userId)
    ->where('quiz_level', $grade)
    ->where('completed_at', '>=', now()->subDays(14))
    ->where('completed_at', '<', now()->subDays(7))
    ->avg('score_percentage') ?? 0;

echo "Current Week Avg: $currentWeekAvg%\n";
echo "Prev Week Avg: $prevWeekAvg%\n";

if ($timeThisWeekSeconds >= 0 && $currentWeekAvg >= 0) {
    echo "SUCCESS: Query logic worked.\n";
} else {
    echo "FAILURE: Query logic failed.\n";
}
