<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessfulNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public array $paymentData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $paymentData)
    {
        $this->paymentData = $paymentData;
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
        return (new MailMessage)
            ->subject('Payment Successful - Thank You!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your payment has been processed successfully.')
            ->line('Amount: ' . ($this->paymentData['currency'] ?? 'GHS') . ' ' . ($this->paymentData['amount'] ?? 'N/A'))
            ->line('Transaction ID: ' . ($this->paymentData['transaction_id'] ?? 'N/A'))
            ->action('View Receipt', route('profile.show'))
            ->line('Thank you for choosing DigiLearn!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment Successful',
            'message' => 'Your payment of ' . ($this->paymentData['currency'] ?? 'GHS') . ' ' . ($this->paymentData['amount'] ?? 'N/A') . ' has been processed successfully.',
            'amount' => $this->paymentData['amount'] ?? null,
            'currency' => $this->paymentData['currency'] ?? 'GHS',
            'transaction_id' => $this->paymentData['transaction_id'] ?? null,
            'type' => 'payment_successful',
            'icon' => 'fas fa-check-circle',
            'color' => '#10b981',
            'url' => 'profile',
        ];
    }
}