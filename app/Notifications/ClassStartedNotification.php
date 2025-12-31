<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\VirtualClass; // Import the VirtualClass model

class ClassStartedNotification extends Notification
{
    use Queueable;

    public $virtualClass;

    /**
     * Create a new notification instance.
     */
    public function __construct(VirtualClass $virtualClass)
    {
        $this->virtualClass = $virtualClass;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // For MVP, we'll use database notification. In production, you might add 'mail', 'nexmo' (SMS) etc.
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Your Class is Starting Soon!')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('A new virtual class for your grade level (' . $this->virtualClass->grade_level . ') is about to start!')
                    ->line('Topic: ' . ($this->virtualClass->topic ?? 'General Session'))
                    ->action('Join Class Now', url(route('dashboard.classroom.show', $this->virtualClass->room_id)))
                    ->line('We hope to see you there!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'class_id' => $this->virtualClass->id,
            'room_id' => $this->virtualClass->room_id,
            'grade_level' => $this->virtualClass->grade_level,
            'topic' => $this->virtualClass->topic,
            'message' => 'A new class for your grade level is starting!',
            'url' => 'dashboard/classroom/' . $this->virtualClass->room_id,
        ];
    }
}
