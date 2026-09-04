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
    public function __construct($errorMessage = 'Email Service Failure')
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
        $raw = (string) $this->errorMessage;
        $lower = strtolower($raw);

        $title = 'Email Delivery Failure';
        $message = $raw;

        if (str_contains($raw, 'LE_102') || str_contains($lower, 'credit') || str_contains($lower, 'exhausted') || str_contains($lower, 'quota') || str_contains($raw, '429') || str_contains($raw, '402') || str_contains($raw, 'TM_5001')) {
            $title = 'Zoho / ZeptoMail Credits Exhausted';
            $message = 'ZeptoMail credits or sending quota have been exhausted. Transactional emails (verification OTP, password resets) cannot be delivered until the account is topped up.';
        } elseif (str_contains($raw, '535') || str_contains($lower, 'authentication failed') || str_contains($lower, 'invalid credentials')) {
            $title = 'SMTP Authentication Failed';
            $message = 'Failed to authenticate with Zoho/ZeptoMail SMTP server. Please verify MAIL_USERNAME and MAIL_PASSWORD in your .env configuration.';
        } elseif (str_contains($lower, 'connection could not be established') || str_contains($lower, 'timed out') || str_contains($lower, 'connection refused')) {
            $title = 'SMTP Connection Failed';
            $message = 'Could not connect to SMTP host (' . config('mail.mailers.smtp.host', 'smtp.zeptomail.com') . ':' . config('mail.mailers.smtp.port', 587) . '). Please verify host reachability and port settings.';
        }

        return [
            'type' => 'system',
            'title' => $title,
            'message' => $message,
            'icon' => 'fas fa-exclamation-triangle',
            'color' => '#ef4444',
            'action_url' => null
        ];
    }
}
