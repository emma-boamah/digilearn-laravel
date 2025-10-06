<?php

namespace App\Broadcasting;

use App\Models\User;

class LessonChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user): array|bool
    {
        // Allow authenticated users to join lesson channels for real-time comments
        return ['id' => $user->id, 'name' => $user->name];
    }
}
