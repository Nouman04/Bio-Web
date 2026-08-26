<?php

namespace App\Http\Requests\Topic;

use App\Http\Requests\Concerns\HasQuestionRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Editing a topic.
 *
 * Same shape as creating one, with the extra rule that a topic may not be its
 * own parent — which only applies once the topic exists.
 */
class UpdateTopicRequest extends FormRequest
{
    use HasQuestionRules;

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
            'parent_topic_id' => [
                'nullable',
                'integer',
                Rule::exists('topics', 'id')->whereNot('id', $this->route('topic')?->id),
            ],
            'content' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ] + $this->questionRules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'parent_topic_id.exists' => 'A topic cannot be its own parent.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(fn (Validator $v) => $this->validateQuestions($v));
    }
}
