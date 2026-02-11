<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $daysRemaining;
    public $planName;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $daysRemaining, string $planName)
    {
        $this->daysRemaining = $daysRemaining;
        $this->planName = $planName;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = "Your Subscription Expires in {$this->daysRemaining} Days";
        
        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Your **{$this->planName}** subscription will expire in **{$this->daysRemaining} days**.")
            ->line("To avoid any interruption to your access, please renew your subscription soon.")
            ->action('Renew Subscription', route('pricing'))
            ->line('Thank you for using DigiLearn!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Subscription Expiring Soon',
            'message' => "Your {$this->planName} subscription expires in {$this->daysRemaining} days.",
            'days_remaining' => $this->daysRemaining,
            'plan_name' => $this->planName,
            'url' => route('pricing'),
            'type' => 'subscription_expiring',
            'icon' => 'fas fa-clock',
            'color' => '#ef4444', // Red color for urgency
        ];
    }
}
