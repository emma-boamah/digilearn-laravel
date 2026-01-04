<?php

namespace App\Notifications;

use App\Models\Quiz;
use App\Services\UrlObfuscator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewQuizNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Quiz $quiz;

    public function __construct(Quiz $quiz)
    {
        $this->quiz = $quiz;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $url = route('quiz.instructions', ['quizId' => UrlObfuscator::encode($this->quiz->id)]);

        return [
            'title' => 'New Quiz Available',
            'message' => "Test your knowledge with the new quiz: {$this->quiz->title}",
            'url' => $url,
            'content_type' => 'quiz',
            'content_id' => $this->quiz->id,
            'subject' => $this->quiz->subject ? $this->quiz->subject->name : 'General',
            'question_count' => $this->quiz->questions_count ?? 0,
            'duration' => $this->quiz->duration_minutes ? "{$this->quiz->duration_minutes} minutes" : 'No time limit',
            'difficulty' => $this->quiz->difficulty ?? 'Mixed',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('quiz.instructions', ['quizId' => UrlObfuscator::encode($this->quiz->id)]);

        return (new MailMessage)
            ->subject('New Quiz Available on DigiLearn')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("A new quiz has been added: {$this->quiz->title}")
            ->line("Subject: " . ($this->quiz->subject ? $this->quiz->subject->name : 'General'))
            ->line("Questions: " . ($this->quiz->questions_count ?? 0))
            ->line("Duration: " . ($this->quiz->duration_minutes ? "{$this->quiz->duration_minutes} minutes" : 'No time limit'))
            ->action('Take Quiz', $url)
            ->line('Good luck!')
            ->salutation('The DigiLearn Team');
    }
}