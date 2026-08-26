<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Handing in a quiz.
 *
 * Answers are keyed by quiz_question id: an option id for a multiple choice,
 * free text for a written answer. Both shapes arrive in the same array, so the
 * rule has to accept either.
 */
class SubmitQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The route is behind auth and the student middleware; whether this
        // attempt belongs to the student is checked in the controller.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable'],
            // Set when the countdown hands the paper in rather than the student.
            'auto' => ['nullable', 'boolean'],
        ];
    }
}
