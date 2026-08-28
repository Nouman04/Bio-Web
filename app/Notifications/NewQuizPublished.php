<?php

namespace App\Notifications;

use App\Models\Chapter;
use App\Models\Quiz;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a subscribed student that a new quiz has been set on a course they
 * are studying.
 *
 * Only published quizzes are announced — a draft is not something anyone can
 * sit yet.
 */
class NewQuizPublished extends Notification
{
    use SendsOverPusher;

    public function __construct(
        public readonly Quiz $quiz,
        public readonly ?Chapter $chapter = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $course = $this->chapter?->course;

        return [
            'type' => 'quiz.published',
            'quiz_uuid' => $this->quiz->uuid,
            'quiz' => $this->quiz->title,
            'chapter' => $this->chapter?->title,
            'course' => $course?->title,
            'title' => 'A new quiz is available',
            'body' => trim(sprintf(
                '"%s" has been added to %s.',
                $this->quiz->title,
                $this->chapter?->title ?? ($course?->title ?? 'your course')
            )),
            // Straight to the paper when we know where it lives; the student's
            // own quiz list otherwise.
            'url' => $course && $this->chapter
                ? route('student.chapters.quizzes.show', [
                    'courseId' => $course->uuid,
                    'chapterId' => $this->chapter->uuid,
                    'quizId' => $this->quiz->uuid,
                ])
                : route('student.quizzes'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
