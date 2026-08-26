<?php

namespace App\Notifications;

use App\Models\QuizUserAttempt;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the course instructor that a student has handed in written answers
 * and the paper is waiting to be marked.
 */
class QuizAwaitingReview extends Notification
{
    use SendsOverPusher;

    public function __construct(public readonly QuizUserAttempt $attempt)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'quiz.awaiting_review',
            'attempt_uuid' => $this->attempt->uuid,
            'quiz' => $this->attempt->quiz?->title,
            'student' => $this->attempt->user?->name,
            'submitted_at' => $this->attempt->submitted_at?->toIso8601String(),
            'title' => 'A quiz needs marking',
            'body' => trim(sprintf(
                '%s submitted "%s".',
                $this->attempt->user?->name ?? 'A student',
                $this->attempt->quiz?->title ?? 'a quiz'
            )),
            'url' => route('quizzes.review.show', $this->attempt->uuid),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
