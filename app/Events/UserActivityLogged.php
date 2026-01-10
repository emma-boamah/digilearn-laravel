<?php

namespace App\Events;

use App\Models\UserActivity;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserActivityLogged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public UserActivity $activity;

    /**
     * Create a new event instance.
     */
    public function __construct(UserActivity $activity)
    {
        $this->activity = $activity;
    }
}