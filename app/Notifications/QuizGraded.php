<?php

namespace App\Notifications;

use App\Models\QuizUserAttempt;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the student their written answers have been marked and the report is
 * ready to read.
 */
class QuizGraded extends Notification
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
            'type' => 'quiz.graded',
            'attempt_uuid' => $this->attempt->uuid,
            'quiz' => $this->attempt->quiz?->title,
            'earned' => (float) $this->attempt->earned_marks,
            'total' => (float) $this->attempt->total_marks,
            'passed' => (bool) $this->attempt->passed,
            'title' => 'Your quiz has been marked',
            'body' => sprintf(
                '"%s" scored %s of %s.',
                $this->attempt->quiz?->title ?? 'Your quiz',
                rtrim(rtrim((string) $this->attempt->earned_marks, '0'), '.'),
                rtrim(rtrim((string) $this->attempt->total_marks, '0'), '.')
            ),
            'url' => route('student.quizzes.report', $this->attempt->uuid),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
