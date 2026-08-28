<?php

namespace App\Http\Resources;

use App\Models\QuizUserAttempt as Record;
use App\Services\StudentQuizListService;
use Illuminate\Http\Request;

/**
 * One row in the student's quiz list: a quiz, and where their latest attempt
 * at it stands.
 *
 * How many times they have sat it is not part of this — it is a per-student
 * count the controller loads in one query and keeps beside the collection,
 * keyed by quiz id.
 *
 * @mixin Record
 */
class QuizAttemptResource extends AppResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $chapter = $this->quiz?->chapters->first();

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'quiz_id' => $this->quiz_id,
            'title' => $this->quiz?->title ?: 'Quiz',
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'marked' => in_array($this->status, StudentQuizListService::MARKED, true),
            'passed' => $this->passed === null ? null : (bool) $this->passed,
            'earned_marks' => $this->earned_marks === null ? null : (float) $this->earned_marks,
            'total_marks' => $this->total_marks === null ? null : (float) $this->total_marks,
            'sat_at' => $this->submitted_at ?? $this->started_at,
            'course' => $chapter?->course?->title,
            'chapter' => $chapter?->title,
        ];
    }

    /**
     * The state of the paper in the words a student reads.
     */
    private function statusLabel(): string
    {
        return match ($this->status) {
            'in_progress' => 'In progress',
            'pending_review' => 'Awaiting marking',
            'expired' => 'Time ran out',
            default => $this->passed ? 'Passed' : 'Not passed',
        };
    }
}
