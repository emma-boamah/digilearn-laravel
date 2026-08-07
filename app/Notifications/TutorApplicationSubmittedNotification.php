<?php

namespace App\Notifications;

use App\Models\TutorProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TutorApplicationSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public TutorProfile $tutorProfile;

    /**
     * Create a new notification instance.
     */
    public function __construct(TutorProfile $tutorProfile)
    {
        $this->tutorProfile = $tutorProfile;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $applicantName = $this->tutorProfile->user->name ?? 'A new applicant';
        $legalName = $this->tutorProfile->legal_name ?? $applicantName;
        $headline = $this->tutorProfile->tagline ?? 'Tutor Application';
        $url = route('admin.tutors.show', $this->tutorProfile->id);

        return (new MailMessage)
            ->subject("🔔 New Tutor Application: {$legalName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new tutor application has been submitted by **{$legalName}** ({$headline}).")
            ->line("Hourly Rate Requested: GHS " . number_format($this->tutorProfile->hourly_rate, 2) . "/hr")
            ->line("Please inspect their biography, uploaded ID documents, certificates, and sample teaching video.")
            ->action('Review Tutor Application', $url)
            ->line('Thank you for managing DigiLearn!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $applicantName = $this->tutorProfile->user->name ?? 'Applicant';
        $url = route('admin.tutors.show', $this->tutorProfile->id);

        return [
            'id' => (string) Str::uuid(),
            'title' => 'New Tutor Application',
            'message' => "{$applicantName} submitted a new tutor application for review.",
            'url' => $url,
            'tutor_profile_id' => $this->tutorProfile->id,
            'type' => 'tutor_application',
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $applicantName = $this->tutorProfile->user->name ?? 'Applicant';
        $url = route('admin.tutors.show', $this->tutorProfile->id);

        return new BroadcastMessage([
            'id' => (string) Str::uuid(),
            'title' => 'New Tutor Application',
            'message' => "{$applicantName} submitted a new tutor application for review.",
            'url' => $url,
            'tutor_profile_id' => $this->tutorProfile->id,
            'type' => 'tutor_application',
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
