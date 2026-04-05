<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $daysRemaining;
    public string $planName;
    public string $userName;

    /**
     * Create a new message instance.
     */
    public function __construct(int $daysRemaining, string $planName, string $userName)
    {
        $this->daysRemaining = $daysRemaining;
        $this->planName = $planName;
        $this->userName = $userName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = ($this->daysRemaining === 1) 
            ? "Urgent: Your ShoutOutGH Access Ends Tomorrow!"
            : "Reminder: Your Account Access Ends in {$this->daysRemaining} Days";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription.reminder',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
