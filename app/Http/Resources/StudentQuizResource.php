<?php

namespace App\Http\Resources;

use App\Models\Quiz as Record;
use App\Services\StudentQuizListService;
use Illuminate\Http\Request;

/**
 * One row in the student's quiz list: a quiz set for them, and where their
 * latest attempt at it stands.
 *
 * A quiz they have never sat is a row like any other — it simply has no
 * attempt, which is what `not_started` means. The listing eager-loads only this
 * student's attempts, newest first, so the first of them is the one the row
 * speaks for.
 *
 * @mixin Record
 */
class StudentQuizResource extends AppResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attempt = $this->userAttempts->first();
        $chapter = $this->chapters->first();
        $status = $attempt?->status ?? 'not_started';

        return [
            'quiz_id' => $this->id,
            'quiz_uuid' => $this->uuid,
            'title' => $this->title,
            'type' => $this->type,

            // Null until it has been sat: the report is about an attempt, and
            // there is not one yet.
            'attempt_uuid' => $attempt?->uuid,
            'attempts' => (int) ($this->attempts_count ?? 0),

            'status' => $status,
            'status_label' => $this->label($status, $attempt?->passed),
            'marked' => in_array($status, StudentQuizListService::MARKED, true),
            'passed' => $attempt?->passed === null ? null : (bool) $attempt->passed,
            'earned_marks' => $attempt?->earned_marks === null ? null : (float) $attempt->earned_marks,
            'total_marks' => $attempt?->total_marks === null ? null : (float) $attempt->total_marks,
            'sat_at' => $attempt?->submitted_at ?? $attempt?->started_at,

            'course' => $chapter?->course?->title,
            'chapter' => $chapter?->title,

            // Where the quiz is actually sat. Null when it hangs off no chapter,
            // which would leave nothing to route through.
            'url' => $chapter && $chapter->course
                ? route('student.chapters.quizzes.show', [
                    'courseId' => $chapter->course->uuid,
                    'chapterId' => $chapter->uuid,
                    'quizId' => $this->uuid,
                ])
                : null,
        ];
    }

    /**
     * The state of the quiz in the words a student reads.
     */
    private function label(string $status, mixed $passed): string
    {
        return match ($status) {
            'not_started' => 'Not started',
            'in_progress' => 'In progress',
            'pending_review' => 'Awaiting marking',
            'self_marked' => 'Mark your own answers',
            'expired' => 'Time ran out',
            default => $passed ? 'Passed' : 'Not passed',
        };
    }
}
