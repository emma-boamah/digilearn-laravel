<?php

namespace App\Notifications;

use App\Models\Video;
use App\Services\UrlObfuscator;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewVideoNotification extends Notification
{

    public Video $video;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $url = '/dashboard/lesson/' . UrlObfuscator::encode($this->video->id);

        return [
            'title' => 'New Video Lesson Available',
            'message' => "Check out the new video lesson: {$this->video->title}",
            'url' => $url,
            'content_type' => 'video',
            'content_id' => $this->video->id,
            'thumbnail' => $this->video->thumbnail_url,
            'subject' => $this->video->subject ? $this->video->subject->name : 'General',
            'duration' => $this->video->duration,
            'instructor' => $this->video->instructor_name ?: 'DigiLearn Team',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('dashboard.lesson.view', ['lessonId' => UrlObfuscator::encode($this->video->id)]);

        return (new MailMessage)
            ->subject('New Video Lesson Available on DigiLearn')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("A new video lesson has been added: {$this->video->title}")
            ->line('Subject: ' . ($this->video->subject->name ?? 'General'))
            ->line("Duration: {$this->video->duration}")
            ->action('Watch Now', $url)
            ->line('Happy learning!')
            ->salutation('The DigiLearn Team');
    }
}