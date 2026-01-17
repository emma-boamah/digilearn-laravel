<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UploadProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $uploadId;
    public $progress;
    public $status;
    public $uploadedBytes;
    public $totalBytes;
    public $speed;
    public $timeRemaining;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $uploadId, $progress, $status, $uploadedBytes = null, $totalBytes = null, $speed = null, $timeRemaining = null)
    {
        $this->userId = $userId;
        $this->uploadId = $uploadId;
        $this->progress = $progress;
        $this->status = $status;
        $this->uploadedBytes = $uploadedBytes;
        $this->totalBytes = $totalBytes;
        $this->speed = $speed;
        $this->timeRemaining = $timeRemaining;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('upload-progress.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'upload.progress';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'upload_id' => $this->uploadId,
            'progress' => $this->progress,
            'status' => $this->status,
            'uploaded_bytes' => $this->uploadedBytes,
            'total_bytes' => $this->totalBytes,
            'speed' => $this->speed,
            'time_remaining' => $this->timeRemaining,
            'timestamp' => now()->toISOString(),
        ];
    }
}