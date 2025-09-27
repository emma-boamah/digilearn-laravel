<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected ?string $url;
    protected $notificationType;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, ?string $url = null, $notificationType = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->notificationType = $notificationType;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Get user preferences for channels
        $channels = ['database']; // Default to database

        if ($this->notificationType) {
            $preferences = $notifiable->notificationPreferences()
                ->where('notification_type_id', $this->notificationType->id)
                ->first();

            if ($preferences && $preferences->is_enabled) {
                $channels = $preferences->channels ?? ['database'];
            }
        }

        return $channels;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'notification_type_id' => $this->notificationType?->id,
            'type' => 'admin',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->message);

        if ($this->url) {
            $mail->action('View Details', $this->url);
        }

        $mail->line('Thank you for using our platform!')
            ->salutation('Best regards, ' . config('app.name') . ' Team');

        return $mail;
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'notification_type_id' => $this->notificationType?->id,
            'type' => 'admin',
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'notification_type_id' => $this->notificationType?->id,
            'type' => 'admin',
        ];
    }
}