<?php

namespace App\Http\Requests\Concerns;

use App\Services\QuestionLinkService;
use Illuminate\Validation\Validator;

/**
 * Validation for the question widget shared by topics, guides, summaries,
 * diagrams and video lessons.
 *
 * The shape is checked here; storing it is QuestionLinkService's job.
 */
trait HasQuestionRules
{
    /**
     * @return array<string, array<int, string>>
     */
    protected function questionRules(): array
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
            // everything but the source is required — checked below, because
            // `required_with` would not catch a half-filled block.
            'new_questions.*.past_paper' => ['nullable', 'array'],
            'new_questions.*.past_paper.date' => ['nullable', 'date'],
            'new_questions.*.past_paper.paper_no' => ['nullable', 'string', 'max:50'],
            'new_questions.*.past_paper.marks' => ['nullable', 'numeric', 'min:0'],
            'new_questions.*.past_paper.source' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * The rules that only make sense once the rest has passed: an MCQ needs at
     * least two options and one marked correct, and a citation is all-or-
     * nothing apart from its source.
     */
    protected function validateQuestions(Validator $validator): void
    {
        $questions = app(QuestionLinkService::class);

        foreach ($this->input('new_questions', []) as $index => $row) {
            $this->validateCitation($validator, $questions, $row['past_paper'] ?? null, $index);

            if (! $questions->isMcq($row['type'] ?? null)) {
                continue;
            }

            $options = $questions->cleanOptions($row['options'] ?? []);

            if (count($options) < 2) {
                $validator->errors()->add(
                    "new_questions.{$index}.options",
                    'A new MCQ needs at least two options.'
                );

                continue;
            }

            if (! array_key_exists($row['correct_option'] ?? null, $options)) {
                $validator->errors()->add(
                    "new_questions.{$index}.correct_option",
                    'Choose which option is the correct answer.'
                );
            }
        }
    }

    private function validateCitation(Validator $validator, QuestionLinkService $questions, ?array $paper, int $index): void
    {
        $paper = $questions->cleanPastPaper($paper, false);

        if ($paper === null) {
            return;
        }

        $required = ['date' => 'a date', 'paper_no' => 'a paper number', 'marks' => 'the marks'];

        foreach ($required as $field => $name) {
            if (($paper[$field] ?? '') === '') {
                $validator->errors()->add(
                    "new_questions.{$index}.past_paper.{$field}",
                    "A past paper reference needs {$name}."
                );
            }
        }
    }
}
