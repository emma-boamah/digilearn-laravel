<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UploadTask extends Model
{
    protected $table = 'content_upload_tasks';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'content_type',
        'related_video_id',
        'title',
        'status',
        'progress',
        'step_description',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'progress' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function relatedVideo(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'related_video_id');
    }

    public function updateProgress(int $percent, ?string $step = null): void
    {
        $data = [
            'progress' => min(100, max(0, $percent)),
        ];

        if ($step !== null) {
            $data['step_description'] = $step;
        }

        $this->update($data);
    }

    public function markCompleted(?string $message = 'Upload and processing complete!'): void
    {
        $this->update([
            'status' => 'completed',
            'progress' => 100,
            'step_description' => $message,
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'step_description' => 'Failed: ' . Str::limit($error, 100),
        ]);
    }

    public function markCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
            'step_description' => 'Cancelled by user',
        ]);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['uploading', 'queued', 'processing']);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
