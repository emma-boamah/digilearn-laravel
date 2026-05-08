<?php

use App\Models\User;
use App\Services\NotificationService;
use App\Notifications\SystemAnnouncementNotification;

$service = app(NotificationService::class);

$title = 'New Feature: AI Essay Grading';
$message = 'You can now set essay-based questions in the quiz builder. The new automated AI system will grade, review, and provide feedback on the user responses automatically!';
$url = '/dashboard'; // Use relative URLs for system announcements as per system norm

try {
    // 1. Get the IDs of all super-admins via Spatie
    $adminIds = User::role('super-admin')->pluck('id')->toArray();

    // 2. Dispatch via the central in-app NotificationService structure
    $service->sendToUsersByCriteria(
        ['user_ids' => $adminIds], 
        new SystemAnnouncementNotification($title, $message, $url),
        ['database', 'mail'] // or just ['database']
    );

    echo "Notification successfully dispatched to " . count($adminIds) . " admin(s) using the in-app NotificationService!\n";
} catch (\Exception $e) {
    echo "Failed to dispatch: " . $e->getMessage() . "\n";
}
