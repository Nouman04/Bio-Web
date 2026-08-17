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
        ];
    }

    /**
     * An MCQ written in the widget needs at least two options and one marked
     * correct. Theory questions ignore options entirely.
     */
    protected function validateNewQuestions(array $data): void
    {
        foreach ($data['new_questions'] ?? [] as $index => $row) {
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

        foreach ($data['new_questions'] ?? [] as $row) {
            $questionIds->push($this->createQuestion($row, $record->chapter_id)->id);
        }

        foreach ($questionIds->unique() as $questionId) {
            $record->questionables()->create([
                'chapter_id' => $record->chapter_id,
                'question_id' => $questionId,
            ]);
        }
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
