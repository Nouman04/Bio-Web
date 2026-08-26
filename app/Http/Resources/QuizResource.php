<?php

namespace App\Http\Resources;

use App\Models\Quiz as Record;
use Illuminate\Http\Request;

/**
 * One quiz in a chapter listing.
 *
 * The student's own attempts are not part of this: they are per-user state the
 * controller loads in one query and keeps beside the collection, keyed by id.
 *
 * @mixin Record
 */
class QuizResource extends AppResource
{
    /**
     * The three kinds a quiz can be, in the words a student reads.
     */
    public const TYPE_LABELS = [
        'mcqs' => 'Multiple choice',
        'theory' => 'Theory',
        'mixed' => 'Mixed',
    ];

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Kept so the view can look this quiz up in the attempts map.
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title ?: 'Untitled quiz',
            'excerpt' => $this->excerpt,
            'type' => $this->type,
            'type_label' => self::TYPE_LABELS[$this->type] ?? 'Quiz',
            'duration' => $this->duration,
            'passing_score' => $this->passing_score,
            'questions_count' => (int) ($this->questions_count ?? 0),
            // Null rather than 0 when a quiz has no questions on it yet, so the
            // view can tell "not built" apart from "worth nothing".
            'total_marks' => $this->total_marks !== null ? (float) $this->total_marks : null,
        ];
    }
}
