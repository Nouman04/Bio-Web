<?php

namespace App\Http\Requests\Course;

use App\Models\CoursePrice;
use App\Services\StripeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Repricing a course, and the promo code that may come with it.
 *
 * The form collects money the way a person writes it — 19.00 — and Stripe
 * counts in the smallest currency unit, so both amounts are converted to cents
 * here rather than in the service.
 */
class UpdateCoursePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The route already carries `can:edit course`.
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // An empty promo code means no offer at all, whatever else was
            // typed alongside it.
            'promo_code' => trim((string) $this->input('promo_code')) ?: null,
            'promo_expires_at' => $this->input('promo_expires_at') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0.5', 'max:999999'],
            'billing_interval' => ['required', Rule::in(StripeService::INTERVALS)],

            'promo_code' => ['nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9_-]+$/'],
            'promo_type' => ['nullable', Rule::in(array_keys(CoursePrice::PROMO_TYPES))],
            'promo_value' => ['nullable', 'numeric', 'min:0'],
            'promo_expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'promo_code.regex' => 'A promo code can only use letters, numbers, dashes and underscores.',
            'promo_expires_at.after' => 'The promo has to expire in the future.',
        ];
    }

    /**
     * A promo code without a discount does nothing, and a percentage over 100
     * is not a discount but a refund. Neither is worth storing.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->input('promo_code')) {
                    return;
                }

                if (! $this->input('promo_value')) {
                    $validator->errors()->add('promo_value', 'Say how much the promo code takes off.');

                    return;
                }

                if ($this->input('promo_type') === 'percent' && (float) $this->input('promo_value') > 100) {
                    $validator->errors()->add('promo_value', 'A percentage discount cannot be more than 100.');
                }

                if ($this->input('promo_type') === 'amount'
                    && (float) $this->input('promo_value') > (float) $this->input('price')) {
                    $validator->errors()->add('promo_value', 'The discount cannot be more than the price.');
                }
            },
        ];
    }

    /**
     * The terms as the service wants them: whole cents, and the promo dropped
     * entirely when no code was given.
     *
     * @return array<string, mixed>
     */
    public function terms(): array
    {
        $validated = $this->validated();
        $code = $validated['promo_code'] ?? null;
        $type = $validated['promo_type'] ?? 'percent';

        return [
            'price' => $this->cents($validated['price']),
            'billing_interval' => $validated['billing_interval'],
            'promo_code' => $code ? strtoupper($code) : null,
            'promo_type' => $code ? $type : null,
            // A percentage is a plain number; a fixed amount is money, so it
            // is counted the same way the price is.
            'promo_value' => $code
                ? ($type === 'percent'
                    ? (int) round((float) $validated['promo_value'])
                    : $this->cents($validated['promo_value']))
                : null,
            'promo_expires_at' => $code ? ($validated['promo_expires_at'] ?? null) : null,
        ];
    }

    private function cents(mixed $amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
