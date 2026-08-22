<?php

namespace App\Http\Controllers\Concerns;

use App\Models\QuestionAnswer;
use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Shared handling for the question widget that appears on topics, guides,
 * summaries, diagrams and video lessons.
 *
 * The widget posts two things: `question_ids` picked from the bank, and
 * `new_questions` written on the spot — each with its own type, and options
 * plus a correct answer when that type is MCQ.
 */
trait LinksQuestions
{
    /**
     * Validation rules for both halves of the widget.
     */
    protected function questionLinkRules(): array
    {
        return [
            'question_ids' => ['nullable', 'array'],
            'question_ids.*' => ['integer', 'exists:question_bank,id'],
            'new_questions' => ['nullable', 'array'],
            'new_questions.*.question' => ['required', 'string', 'max:1000'],
            'new_questions.*.type' => ['required', 'integer', 'exists:question_categories,id'],
            'new_questions.*.options' => ['nullable', 'array'],
            'new_questions.*.options.*' => ['nullable', 'string', 'max:1000'],
            'new_questions.*.correct_option' => ['nullable', 'integer', 'min:0'],

            // A past paper citation is optional as a whole. Once one is given,
            // everything but the source is required — enforced below, because
            // `required_with` alone would not catch a half-filled block.
            'new_questions.*.past_paper' => ['nullable', 'array'],
            'new_questions.*.past_paper.date' => ['nullable', 'date'],
            'new_questions.*.past_paper.paper_no' => ['nullable', 'string', 'max:50'],
            'new_questions.*.past_paper.marks' => ['nullable', 'numeric', 'min:0'],
            'new_questions.*.past_paper.source' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * An MCQ written in the widget needs at least two options and one marked
     * correct. Theory questions ignore options entirely.
     */
    protected function validateNewQuestions(array $data): void
    {
        foreach ($data['new_questions'] ?? [] as $index => $row) {
            $this->validatePastPaper($row['past_paper'] ?? null, $index);

            if (! $this->isMcqCategory($row['type'] ?? null)) {
                continue;
            }

            $options = $this->cleanQuestionOptions($row['options'] ?? []);

            if (count($options) < 2) {
                throw ValidationException::withMessages([
                    "new_questions.{$index}.options" => 'A new MCQ needs at least two options.',
                ]);
            }

            if (! array_key_exists($row['correct_option'] ?? null, $options)) {
                throw ValidationException::withMessages([
                    "new_questions.{$index}.correct_option" => 'Choose which option is the correct answer.',
                ]);
            }
        }
    }

    /**
     * Replaces the record's question links: questions picked from the bank come
     * through as ids, freshly written ones are created first.
     */
    protected function syncQuestionLinks(Model $record, array $data): void
    {
        $record->questionables()->delete();

        $questionIds = collect($data['question_ids'] ?? [])->map(fn ($id) => (int) $id);

        // A new question may carry a past paper citation. The citation belongs
        // to the *link*, not the question, so the two are kept together here
        // until the link row exists to hang it on.
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
     * A citation is either absent or complete. Half of one is a mistake worth
     * reporting rather than quietly storing.
     */
    private function validatePastPaper(?array $paper, int $index): void
    {
        $paper = $this->cleanPastPaper($paper, false);

        if ($paper === null) {
            return;
        }

        foreach (['date' => 'a date', 'paper_no' => 'a paper number', 'marks' => 'the marks'] as $field => $name) {
            if (($paper[$field] ?? '') === '' || $paper[$field] === null) {
                throw ValidationException::withMessages([
                    "new_questions.{$index}.past_paper.{$field}" => "A past paper reference needs {$name}.",
                ]);
            }
        }
    }

    /**
     * The citation as it should be stored, or null when nothing was entered.
     *
     * `source` is optional, so it never counts towards deciding whether a
     * citation was given at all.
     *
     * @param  bool  $complete  Return null unless the required parts are present.
     */
    private function cleanPastPaper(?array $paper, bool $complete = true): ?array
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
     * Adds one written question to the bank, with its options and answer when
     * it is an MCQ.
     */
    /**
     * The questions linked to a record, shaped for a detail page: the text, the
     * answer, and the options when it is an MCQ.
     */
    protected function linkedQuestions(Model $record): array
    {
        return $record->questionables()
            ->with('question.answer', 'question.options', 'question.category:id,type')
            ->get()
            ->filter(fn ($link) => $link->question)
            ->map(function ($link) {
                $question = $link->question;
                $type = $question->category?->type;
                $answer = $question->answer->first();

                return [
                    'text' => $question->question,
                    'type' => $type === 'mcqs' ? 'MCQ' : 'Theory',
                    'difficulty' => $question->difficulty_level,
                    'options' => $question->options
                        ->map(fn ($option) => [
                            'title' => $option->title,
                            'correct' => $answer && $answer->question_option_id === $option->id,
                        ])
                        ->values()->all(),
                    'answer' => (string) ($answer?->description ?: ''),
                ];
            })
            ->values()
            ->all();
    }

    private function createQuestion(array $row, ?int $chapterId): QuestionBank
    {
        $question = QuestionBank::create([
            'chapter_id' => $chapterId,
            'question_categories_id' => $row['type'],
            'question' => trim($row['question']),
        ]);

        if (! $this->isMcqCategory($row['type'])) {
            return $question;
        }

        $created = [];
        foreach ($this->cleanQuestionOptions($row['options'] ?? []) as $index => $title) {
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
     */
    private function cleanQuestionOptions(array $options): array
    {
        return array_filter(
            array_map(fn ($option) => is_string($option) ? trim($option) : '', $options),
            fn ($option) => $option !== ''
        );
    }

    private function isMcqCategory(?int $categoryId): bool
    {
        return $categoryId
            && QuestionCategory::where('id', $categoryId)->value('type') === 'mcqs';
    }
}
