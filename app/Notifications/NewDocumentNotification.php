<?php

namespace App\Notifications;

use App\Models\Document;
use App\Services\UrlObfuscator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDocumentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Document $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $url = route('dashboard.lesson.document', [
            'lessonId' => UrlObfuscator::encode($this->document->video_id),
            'type' => $this->document->type
        ]);

        return [
            'title' => 'New Study Material Available',
            'message' => "New {$this->document->type} document added: {$this->document->title}",
            'url' => $url,
            'content_type' => 'document',
            'content_id' => $this->document->id,
            'document_type' => $this->document->type,
            'subject' => $this->document->video ? ($this->document->video->subject ? $this->document->video->subject->name : 'General') : 'General',
            'file_size' => $this->document->file_size,
            'lesson_title' => $this->document->video ? $this->document->video->title : 'Unknown Lesson',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('dashboard.lesson.document', [
            'lessonId' => UrlObfuscator::encode($this->document->video_id),
            'type' => $this->document->type
        ]);

        return (new MailMessage)
            ->subject('New Study Material Available on DigiLearn')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("A new {$this->document->type} document has been added for: " . ($this->document->video ? $this->document->video->title : 'Unknown Lesson'))
            ->line("Document: {$this->document->title}")
            ->action('View Document', $url)
            ->line('Happy studying!')
            ->salutation('The DigiLearn Team');
    }
}