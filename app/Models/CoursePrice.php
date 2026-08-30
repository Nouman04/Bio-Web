<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\Cashier;

/**
 * One entry in a course's price history.
 *
 * Repricing writes a new row rather than editing the last one, so what a course
 * used to cost is still answerable. The newest row for a billing interval is
 * the price in force — see Course::currentPrice().
 *
 * `price` and `promo_value` are in the smallest currency unit, the way Stripe
 * counts: 1900 is $19.00.
 */
class CoursePrice extends Model
{
    use HasUuid;

    /**
     * How a promo code takes money off: a percentage of the price, or a flat
     * amount in the smallest currency unit.
     */
    public const PROMO_TYPES = [
        'percent' => 'Percentage off',
        'amount' => 'Fixed amount off',
    ];

    protected $fillable = [
        'course_id',
        'billing_interval',
        'price',
        'currency',
        'promo_code',
        'promo_type',
        'promo_value',
        'promo_expires_at',
        'stripe_price_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'promo_value' => 'integer',
            'promo_expires_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The newest price first — the order the history is read in, and the one
     * "current" is picked from.
     */
    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('id');
    }

    /**
     * Whether the offer on this price is still live. A promo with no expiry
     * runs until the price is replaced.
     */
    public function hasLivePromo(): bool
    {
        if (! $this->promo_code || ! $this->promo_value) {
            return false;
        }

        return $this->promo_expires_at === null || $this->promo_expires_at->isFuture();
    }

    /**
     * What a customer actually pays: the price, less a live promo. Never below
     * zero, and never more than the price itself.
     */
    public function getPayableAttribute(): int
    {
        if (! $this->hasLivePromo()) {
            return $this->price;
        }

        $off = $this->promo_type === 'percent'
            ? (int) round($this->price * min($this->promo_value, 100) / 100)
            : $this->promo_value;

        return max(0, $this->price - $off);
    }

    /**
     * The list price as a person would read it: 1900 becomes $19.00.
     */
    public function getFormattedPriceAttribute(): string
    {
        return Cashier::formatAmount($this->price, $this->currency);
    }

    /**
     * What they pay after a live promo, formatted the same way.
     */
    public function getFormattedPayableAttribute(): string
    {
        return Cashier::formatAmount($this->payable, $this->currency);
    }

    /**
     * The offer in the words a person reads — "SPRING25 · 25% off, until 1 Jun
     * 2026" — or null when there is no live offer.
     */
    public function getPromoLabelAttribute(): ?string
    {
        if (! $this->hasLivePromo()) {
            return null;
        }

        $off = $this->promo_type === 'percent'
            ? $this->promo_value . '% off'
            : Cashier::formatAmount($this->promo_value, $this->currency) . ' off';

        return $this->promo_code . ' · ' . $off
            . ($this->promo_expires_at ? ', until ' . $this->promo_expires_at->format('j M Y') : '');
    }
}
