<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassroomSignalEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $roomId;
    public string $type; // offer, answer, ice-candidate, mute-request, hand-raise, screen-share-start, screen-share-stop
    public array $payload;
    public array $sender;
    public ?int $targetUserId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $roomId, string $type, array $payload, array $sender, ?int $targetUserId = null)
    {
        $this->roomId = $roomId;
        $this->type = $type;
        $this->payload = $payload;
        $this->sender = $sender;
        $this->targetUserId = $targetUserId;
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
        return 'classroom.signal';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'roomId' => $this->roomId,
            'type' => $this->type,
            'payload' => $this->payload,
            'sender' => $this->sender,
            'targetUserId' => $this->targetUserId,
            'timestamp' => now()->toISOString(),
        ];
    }
}
