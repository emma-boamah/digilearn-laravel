<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StorageAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public float $usagePercentage;
    public string $usedSpace;
    public string $totalSpace;
    public string $alertType;
    public string $path;

    /**
     * Create a new notification instance.
     */
    public function __construct(float $usagePercentage, string $usedSpace, string $totalSpace, string $alertType = 'warning', string $path = 'storage')
    {
        $this->usagePercentage = $usagePercentage;
        $this->usedSpace = $usedSpace;
        $this->totalSpace = $totalSpace;
        $this->alertType = $alertType;
        $this->path = $path;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $alertTitles = [
            'warning' => 'Storage Warning - High Usage Detected',
            'critical' => 'Storage Critical - Action Required',
            'emergency' => 'Storage Emergency - Immediate Action Required',
            'recovery' => 'Storage Recovery - Usage Normalized'
        ];

        $alertMessages = [
            'warning' => 'The system storage usage has reached ' . $this->usagePercentage . '%.',
            'critical' => 'The system storage usage has reached a critical level of ' . $this->usagePercentage . '%.',
            'emergency' => 'The system storage usage has reached an emergency level of ' . $this->usagePercentage . '%. Immediate action is required.',
            'recovery' => 'The system storage usage has returned to normal levels at ' . $this->usagePercentage . '%.'
        ];

        $subject = $alertTitles[$this->alertType] ?? 'Storage Alert';
        $message = $alertMessages[$this->alertType] ?? 'Storage usage alert.';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($message)
            ->line('Location: ' . $this->path)
            ->line('Current usage: ' . $this->usedSpace . ' of ' . $this->totalSpace);

        if (in_array($this->alertType, ['warning', 'critical', 'emergency'])) {
            $mail->line('Please take action to free up space or increase storage capacity.')
                 ->action('View System Health', route('admin.dashboard'));
        } else {
            $mail->line('The storage situation has been resolved.');
        }

        $mail->line('This is an automated alert from the DigiLearn system.');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $alertTitles = [
            'warning' => 'Storage Warning - High Usage Detected',
            'critical' => 'Storage Critical - Action Required',
            'emergency' => 'Storage Emergency - Immediate Action Required',
            'recovery' => 'Storage Recovery - Usage Normalized'
        ];

        $alertMessages = [
            'warning' => "System storage usage has reached {$this->usagePercentage}%. Current usage: {$this->usedSpace} of {$this->totalSpace}. Please take action to free up space.",
            'critical' => "System storage usage has reached a critical level of {$this->usagePercentage}%. Current usage: {$this->usedSpace} of {$this->totalSpace}. Immediate action required.",
            'emergency' => "System storage usage has reached an emergency level of {$this->usagePercentage}%. Current usage: {$this->usedSpace} of {$this->totalSpace}. Immediate action required to prevent system issues.",
            'recovery' => "System storage usage has returned to normal levels at {$this->usagePercentage}%. Current usage: {$this->usedSpace} of {$this->totalSpace}."
        ];

        $alertIcons = [
            'warning' => 'fas fa-exclamation-triangle',
            'critical' => 'fas fa-exclamation-circle',
            'emergency' => 'fas fa-exclamation-triangle',
            'recovery' => 'fas fa-check-circle'
        ];

        $alertColors = [
            'warning' => '#f59e0b',   // amber-500
            'critical' => '#f97316',  // orange-500
            'emergency' => '#dc2626', // red-600
            'recovery' => '#10b981'   // emerald-500
        ];

        return [
            'title' => $alertTitles[$this->alertType] ?? 'Storage Alert',
            'message' => $alertMessages[$this->alertType] ?? 'Storage usage alert.',
            'url' => '/admin',
            'type' => 'storage_alert',
            'alert_type' => $this->alertType,
            'icon' => $alertIcons[$this->alertType] ?? 'fas fa-info-circle',
            'color' => $alertColors[$this->alertType] ?? '#6b7280',
            'path' => $this->path,
            'usage_percentage' => $this->usagePercentage,
            'used_space' => $this->usedSpace,
            'total_space' => $this->totalSpace,
        ];
    }
}