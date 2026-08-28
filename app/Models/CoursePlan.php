<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Cashier;

/**
 * The subscription a course is sold on, and the Stripe product and price it
 * maps to. One per course.
 */
class CoursePlan extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'course_id',
        'stripe_product_id',
        'stripe_price_id',
        'price',
        'currency',
        'billing_interval',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Whether this plan is live in Stripe and can be checked out.
     */
    public function isSellable(): bool
    {
        return filled($this->stripe_price_id);
    }

    /**
     * The price as a person would read it: 1900 becomes $19.00.
     */
    public function getFormattedPriceAttribute(): ?string
    {
        if ($this->price === null) {
            return null;
        }

        return Cashier::formatAmount($this->price, $this->currency);
    }
}
