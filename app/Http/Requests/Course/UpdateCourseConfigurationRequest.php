<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The course configuration page: which of its chapters are public.
 */
class UpdateCourseConfigurationRequest extends FormRequest
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
            'chapters' => ['nullable', 'array'],
            'chapters.*' => [Rule::in(['public', 'private'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'chapters.*.in' => 'A chapter can only be public or private.',
        ];
    }
}
