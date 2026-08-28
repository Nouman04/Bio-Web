<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

/**
 * An instructor marking the written answers on one attempt.
 *
 * Marks are keyed by answer id. The per-question ceiling is applied when the
 * marks are stored, since only the service knows what each question is worth.
 */
class GradeAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ownership of the course is checked in the controller.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'marks' => ['required', 'array'],
            'marks.*.marks' => ['required', 'numeric', 'min:0'],
            'marks.*.feedback' => ['nullable', 'string', 'max:2000'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'marks.*.marks.required' => 'Give every written answer a mark.',
            'marks.*.marks.min' => 'Marks cannot be negative.',
        ];
    }
}
