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

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->type === 'grace_ending') {
            return $this->graceEndingMail($notifiable);
        }

        return (new MailMessage)
            ->subject('Your ShoutOutGH Subscription Has Expired')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Your **{$this->planName}** subscription has expired.")
            ->line("You have a **3-day grace period** to renew and keep your access. After that, your content access will be paused.")
            ->line("Your learning progress is saved — renew anytime within 90 days to pick up where you left off.")
            ->action('Renew Now', route('pricing'))
            ->line('Thank you for learning with ShoutOutGH!');
    }

    private function graceEndingMail(object $notifiable): MailMessage
    {
        $dayText = $this->graceDaysRemaining === 1 ? 'day' : 'days';

        return (new MailMessage)
            ->subject("Your Access Ends in {$this->graceDaysRemaining} {$dayText} — Renew Now")
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Your grace period for the **{$this->planName}** plan ends in **{$this->graceDaysRemaining} {$dayText}**.")
            ->line("After that, you will lose access to your lessons and quizzes. Your progress is preserved for 90 days.")
            ->action('Renew Now', route('pricing'))
            ->line('Don\'t lose your progress — renew today!');
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
