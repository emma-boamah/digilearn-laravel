<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Lesson channel for real-time comments
Broadcast::channel('lesson.{lessonId}', function ($user, $lessonId) {
    // Allow authenticated users to join lesson channels for real-time comments
    return $user !== null;
});

// Video channel for real-time comments
Broadcast::channel('video.{videoId}', function ($user, $videoId) {
    // Allow authenticated users to join video channels for real-time comments
    return $user !== null;
});

// Course channel for real-time comments
Broadcast::channel('course.{courseId}', function ($user, $courseId) {
    // Allow authenticated users to join course channels for real-time comments
    return $user !== null;
});