<?php

namespace App\Http\Requests\Worksheet;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Building a worksheet from the question bank.
 *
 * A course is the only thing that has to be chosen — everything else narrows
 * the selection, and leaving it empty means "all of them".
 */
class StoreWorksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'course' => ['required', 'string', 'exists:courses,uuid'],
            'chapters' => ['nullable', 'array'],
            'chapters.*' => ['string', 'exists:chapters,uuid'],
            'topics' => ['nullable', 'array'],
            'topics.*' => ['string', 'exists:topics,uuid'],
            'papers' => ['nullable', 'array'],
            'papers.*' => ['string', 'max:20'],
            'years' => ['nullable', 'array'],
            'years.*' => ['integer', 'min:1900', 'max:2200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Give the worksheet a name.',
            'course.required' => 'Choose the course this worksheet is for.',
            'course.exists' => 'That course no longer exists.',
        ];
    }
}
