<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanChangeNotification extends Notification
{

    public string $oldPlan;
    public string $newPlan;
    public string $changeType;
    public ?float $proratedCharge;
    public array $accessChanges;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $oldPlan, string $newPlan, string $changeType, ?float $proratedCharge = null, array $accessChanges = [])
    {
        $this->oldPlan = $oldPlan;
        $this->newPlan = $newPlan;
        $this->changeType = $changeType;
        $this->proratedCharge = $proratedCharge;
        $this->accessChanges = $accessChanges;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Only in-app for now, email will be added later
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->changeType === 'upgrade' ? 'Plan Upgraded Successfully' : 'Plan Changed';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!');

        if ($this->changeType === 'upgrade') {
            $mail->line("Great news! Your plan has been upgraded from **{$this->oldPlan}** to **{$this->newPlan}**.");

            if ($this->proratedCharge > 0) {
                $mail->line("A prorated charge of GHS " . number_format($this->proratedCharge, 2) . " has been applied to your account.");
            }

            $mail->line('You now have access to additional content and features!');
        } else {
            $mail->line("Your plan has been changed from **{$this->oldPlan}** to **{$this->newPlan}**.");

            if (!empty($this->accessChanges['lost_access'])) {
                $mail->line('Please note that access to some content has been restricted based on your new plan.');
            }
        }

        $mail->action('View Dashboard', route('dashboard.main'))
            ->line('Thank you for using DigiLearn!');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->changeType === 'upgrade' ? 'Plan Upgraded!' : 'Plan Changed';

        $message = $this->buildMessage();

        return [
            'title' => $title,
            'message' => $message,
            'url' => route('dashboard.main'),
            'type' => 'plan_change',
            'icon' => $this->changeType === 'upgrade' ? 'fas fa-arrow-up' : 'fas fa-exchange-alt',
            'color' => $this->changeType === 'upgrade' ? '#10b981' : '#f59e0b',
            'change_type' => $this->changeType,
            'old_plan' => $this->oldPlan,
            'new_plan' => $this->newPlan,
            'prorated_charge' => $this->proratedCharge,
            'access_changes' => $this->accessChanges,
        ];
    }

    /**
     * Build the notification message.
     */
    private function buildMessage(): string
    {
        if ($this->changeType === 'upgrade') {
            $message = "Your plan has been upgraded from {$this->oldPlan} to {$this->newPlan}.";

            if ($this->proratedCharge > 0) {
                $message .= " Prorated charge: GHS " . number_format($this->proratedCharge, 2);
            }

            return $message;
        } else {
            $message = "Your plan has been changed from {$this->oldPlan} to {$this->newPlan}.";

            if (!empty($this->accessChanges['lost_access'])) {
                $lostCount = count($this->accessChanges['lost_access']);
                $message .= " Access to {$lostCount} content " . ($lostCount === 1 ? 'area' : 'areas') . " has been restricted.";
            }

            return $message;
        }
    }
}