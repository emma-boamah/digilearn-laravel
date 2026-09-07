<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassroomWhiteboardEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $roomId;
    public string $action; // 'draw', 'clear', 'add-object', 'remove-object', 'load-state', 'cursor'
    public array $data;
    public array $sender;

    /**
     * Create a new event instance.
     */
    public function __construct(string $roomId, string $action, array $data, array $sender)
    {
        $this->roomId = $roomId;
        $this->action = $action;
        $this->data = $data;
        $this->sender = $sender;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('classroom.' . $this->roomId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'classroom.whiteboard';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'roomId' => $this->roomId,
            'action' => $this->action,
            'data' => $this->data,
            'sender' => $this->sender,
            'timestamp' => now()->toISOString(),
        ];
    }
}
