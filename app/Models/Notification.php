<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\User;

class Notification extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'notification_type_id',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    protected $appends = [
        'title',
        'message',
        'url',
    ];

    /**
     * Get the notifiable entity.
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the notification type.
     */
    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Check if the notification is read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if the notification is unread.
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Get the notification title from data.
     */
    public function getTitleAttribute(): string
    {
        return $this->data['title'] ?? 'Notification';
    }

    /**
     * Get the notification message from data.
     */
    public function getMessageAttribute(): string
    {
        return $this->data['message'] ?? '';
    }

    /**
     * Get the notification URL from data.
     */
    public function getUrlAttribute(): ?string
    {
        return $this->data['url'] ?? null;
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for notifications of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the content type from notification data.
     */
    public function getContentTypeAttribute(): ?string
    {
        return $this->data['content_type'] ?? null;
    }

    /**
     * Get the content ID from notification data.
     */
    public function getContentIdAttribute(): ?int
    {
        return $this->data['content_id'] ?? null;
    }

    /**
     * Check if user has access to the notification content.
     */
    public function userHasAccess(User $user): bool
    {
        $contentType = $this->content_type;
        $contentId = $this->content_id;

        if (!$contentType || !$contentId) {
            return true; // System notifications don't need access control
        }

        switch ($contentType) {
            case 'video':
                return $this->checkVideoAccess($user, $contentId);
            case 'document':
                return $this->checkDocumentAccess($user, $contentId);
            case 'quiz':
                return $this->checkQuizAccess($user, $contentId);
            default:
                return true;
        }
    }

    /**
     * Check if user has access to a video.
     */
    private function checkVideoAccess(User $user, int $videoId): bool
    {
        $video = \App\Models\Video::find($videoId);
        if (!$video) return false;

        // Check if user has active subscription or if video is free
        return $user->currentSubscription || $video->is_free;
    }

    /**
     * Check if user has access to a document.
     */
    private function checkDocumentAccess(User $user, int $documentId): bool
    {
        $document = \App\Models\Document::find($documentId);
        if (!$document) return false;

        // Documents are typically tied to videos, so check video access
        if ($document->video_id) {
            return $this->checkVideoAccess($user, $document->video_id);
        }

        return true; // If not tied to video, assume accessible
    }

    /**
     * Check if user has access to a quiz.
     */
    private function checkQuizAccess(User $user, int $quizId): bool
    {
        $quiz = \App\Models\Quiz::find($quizId);
        if (!$quiz) return false;

        // Check if user has active subscription or if quiz is free
        return $user->currentSubscription || $quiz->is_free;
    }

    /**
     * Get the secure URL for the notification content.
     */
    public function getSecureUrl(): ?string
    {
        $url = $this->url;
        if (!$url) return null;

        // URLs are already obfuscated in the notification classes
        return $url;
    }
}