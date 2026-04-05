<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $planName;
    public string $type; // 'expired', 'grace_ending'
    public ?int $graceDaysRemaining;

    /**
     * Create a new notification instance.
     *
     * @param string $planName
     * @param string $type  'expired' or 'grace_ending'
     * @param int|null $graceDaysRemaining  Days left in grace period (null for expired)
     */
    public function __construct(string $planName, string $type = 'expired', ?int $graceDaysRemaining = null)
    {
        $this->planName = $planName;
        $this->type = $type;
        $this->graceDaysRemaining = $graceDaysRemaining;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): \Illuminate\Mail\Mailable
    {
        if ($this->type === 'grace_ending') {
            return (new \App\Mail\SubscriptionReminderMail(
                $this->graceDaysRemaining ?? 0,
                $this->planName,
                $notifiable->name
            ));
        }

        return (new \App\Mail\SubscriptionExpiredMail($this->planName, $notifiable->name));
    }

    public function toArray(object $notifiable): array
    {
        if ($this->type === 'grace_ending') {
            return [
                'title' => 'Grace Period Ending Soon',
                'message' => "Your grace period for {$this->planName} ends in {$this->graceDaysRemaining} day(s). Renew now to keep access.",
                'plan_name' => $this->planName,
                'url' => route('pricing'),
                'type' => 'grace_period_ending',
                'icon' => 'fas fa-exclamation-triangle',
                'color' => '#f59e0b',
            ];
        }

        return [
            'title' => 'Subscription Expired',
            'message' => "Your {$this->planName} subscription has expired. You have 3 days of grace period remaining.",
            'plan_name' => $this->planName,
            'url' => route('pricing'),
            'type' => 'subscription_expired',
            'icon' => 'fas fa-times-circle',
            'color' => '#dc2626',
        ];
    }
}
