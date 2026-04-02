<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ZeptoMailErrorNotification extends Notification
{
    use Queueable;

    public $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct($errorMessage = 'ZeptoMail API Credit Exhausted')
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'system',
            'title' => 'Email Delivery Failure',
            'message' => 'ZeptoMail failed to send critical email. Error: ' . $this->errorMessage,
            'action_url' => null
        ];
    }
}
