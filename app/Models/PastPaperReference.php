<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Where a question came from in a past paper.
 *
 * Attached to the link between a question and the record it appears on, not to
 * the question itself — one bank question can be cited from several papers
 * depending on where it is used.
 */
class PastPaperReference extends Model
{
    use HasUuid;

    protected $table = 'past_paper_reference';

    protected $fillable = [
        'questionable_type_id',
        'date',
        'paper_no',
        'question_no',
        'marks',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'marks' => 'decimal:2',
        ];
    }

    /**
     * The question-to-record link this reference belongs to.
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(QuestionableType::class, 'questionable_type_id');
    }

    /**
     * The citation as a person would read it: "Paper 2 · Jun 2023 · 6 marks".
     */
    public function getCitationAttribute(): string
    {
        return collect([
            'Paper ' . $this->paper_no,
            $this->question_no ? 'Q ' . $this->question_no : null,
            $this->date?->format('M Y'),
            rtrim(rtrim((string) $this->marks, '0'), '.') . ' marks',
            $this->source,
        ])->filter()->implode(' · ');
    }
}
