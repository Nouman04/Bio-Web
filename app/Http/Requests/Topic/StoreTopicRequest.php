<?php

namespace App\Http\Requests\Topic;

use App\Http\Requests\Concerns\HasQuestionRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Creating a topic. The chapter is not validated here — it comes from the URL
 * and is checked by the controller's chain guard.
 */
class StoreTopicRequest extends FormRequest
{
    use HasQuestionRules;

    public function authorize(): bool
    {
        // The route is already behind auth and the admin middleware.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // A parent may live in any chapter, so it is not scoped here.
            'parent_topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'content' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ] + $this->questionRules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(fn (Validator $v) => $this->validateQuestions($v));
    }
}
