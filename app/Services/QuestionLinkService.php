<?php

namespace App\Services;

use App\Models\PastPaperReference;
use App\Models\QuestionAnswer;
use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * The questions attached to a piece of content — topics, guides, summaries,
 * diagrams and video lessons all share this.
 *
 * The form posts two things: `question_ids` picked from the bank, and
 * `new_questions` written on the spot, each with its own type, options when it
 * is an MCQ, and an optional past paper citation.
 *
 * Validation of that shape lives in the form requests; this only stores it.
 */
class QuestionLinkService
{
    /**
     * Replaces a record's question links.
     *
     * @param  array<string, mixed>  $data  The validated form payload.
     */
    public function sync(Model $record, array $data): void
    {
        $record->questionables()->delete();

        $questionIds = collect($data['question_ids'] ?? [])->map(fn ($id) => (int) $id);

        // A citation belongs to the link, not the question, so the two are kept
        // together until the link row exists to hang it on.
        $citations = [];

        foreach ($data['new_questions'] ?? [] as $row) {
            $question = $this->createQuestion($row, $record->chapter_id);
            $questionIds->push($question->id);

            if ($paper = $this->cleanPastPaper($row['past_paper'] ?? null)) {
                $citations[$question->id] = $paper;
            }
        }

        foreach ($questionIds->unique() as $questionId) {
            $link = $record->questionables()->create([
                'chapter_id' => $record->chapter_id,
                'question_id' => $questionId,
            ]);

            if (isset($citations[$questionId])) {
                $link->pastPaper()->create($citations[$questionId]);
            }
        }
    }

    /**
     * The questions linked to a record, shaped for a detail page: the text, the
     * answer, and the options when it is an MCQ.
     *
     * @return array<int, array<string, mixed>>
     */
    public function linked(Model $record): array
    {
        return $record->questionables()
            ->with('question.answer', 'question.options', 'question.category:id,type', 'pastPaper')
            ->get()
            ->filter(fn ($link) => $link->question)
            ->map(function ($link) {
                $question = $link->question;
                $answer = $question->answer->first();

                return [
                    'text' => $question->plain_question,
                    'type' => $question->category?->type === 'mcqs' ? 'MCQ' : 'Theory',
                    'difficulty' => $question->difficulty_level,
                    'options' => $question->options
                        ->map(fn ($option) => [
                            'title' => $option->title,
                            'correct' => $answer && $answer->question_option_id === $option->id,
                        ])
                        ->values()->all(),
                    'answer' => (string) ($answer?->description ?: ''),
                    'past_paper' => $link->pastPaper?->citation,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * The questions on a record, as the edit form's picker wants them.
     */
    public function forPicker(Model $record): Collection
    {
        return $record->questionables()
            ->with('question:id,question')
            ->get()
            ->filter(fn ($link) => $link->question)
            ->map(fn ($link) => [
                'id' => $link->question->id,
                'text' => $link->question->plain_question,
            ])
            ->values();
    }

    /**
     * Adds one written question to the bank, with its options and answer when
     * it is an MCQ.
     */
    private function createQuestion(array $row, ?int $chapterId): QuestionBank
    {
        $question = QuestionBank::create([
            'chapter_id' => $chapterId,
            'question_categories_id' => $row['type'],
            'question' => trim($row['question']),
        ]);

        if (! $this->isMcq($row['type'])) {
            return $question;
        }

        $created = [];

        foreach ($this->cleanOptions($row['options'] ?? []) as $index => $title) {
            $created[$index] = QuestionOption::create([
                'question_bank_id' => $question->id,
                'title' => $title,
            ]);
        }

        $correct = $created[$row['correct_option']] ?? null;

        QuestionAnswer::create([
            'question_bank_id' => $question->id,
            'question_option_id' => $correct?->id,
            // Mirrored so listings can show the answer without a join.
            'description' => $correct?->title ?? '',
        ]);

        return $question;
    }

    /**
     * Blank rows are ignored, and the keys are preserved so `correct_option`
     * keeps pointing at the right one.
     *
     * @return array<int, string>
     */
    public function cleanOptions(array $options): array
    {
        return array_filter(
            array_map(fn ($option) => trim((string) $option), $options),
            fn ($option) => $option !== ''
        );
    }

    public function isMcq($categoryId): bool
    {
        return $categoryId
            && QuestionCategory::whereKey($categoryId)->value('type') === 'mcqs';
    }

    /**
     * The citation as it should be stored, or null when nothing was entered.
     * `source` is optional, so it never decides whether one was given.
     *
     * @param  bool  $complete  Return null unless the required parts are present.
     */
    public function cleanPastPaper(?array $paper, bool $complete = true): ?array
    {
        if (! $paper) {
            return null;
        }

        $values = [
            'date' => trim((string) ($paper['date'] ?? '')),
            'paper_no' => trim((string) ($paper['paper_no'] ?? '')),
            'marks' => trim((string) ($paper['marks'] ?? '')),
            'source' => trim((string) ($paper['source'] ?? '')) ?: null,
        ];

        // Nothing beyond an optional source means no citation was intended.
        if ($values['date'] === '' && $values['paper_no'] === '' && $values['marks'] === '') {
            return null;
        }

        if ($complete && ($values['date'] === '' || $values['paper_no'] === '' || $values['marks'] === '')) {
            return null;
        }

        return $values;
    }

    /**
     * Detaches a question from everything it is linked to.
     */
    public function detachQuestion(QuestionBank $question): void
    {
        PastPaperReference::whereIn(
            'questionable_type_id',
            $question->questionables()->select('id')
        )->delete();

        $question->questionables()->delete();
    }
}
