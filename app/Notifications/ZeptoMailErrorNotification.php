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
        $message = $this->errorMessage;

        // Simplify ZeptoMail "Credit exhausted" message
        if (str_contains($message, 'LE_102') || str_contains($message, 'Credit exhausted')) {
            $message = 'ZeptoMail credits have been exhausted. No transactional emails can be sent until the account is topped up.';
        }

        return [
            'type' => 'system',
            'title' => 'Email Delivery Failure',
            'message' => $message,
            'icon' => 'fas fa-exclamation-triangle',
            'color' => '#ef4444',
            'action_url' => null
        ];
    }
}
