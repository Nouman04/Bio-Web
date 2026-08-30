<?php

namespace App\Models;

use App\Models\Concerns\HasAttachments;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Course extends Model
{
    use SoftDeletes, HasUuid, HasAttachments, Searchable;

    protected $fillable = [
        'created_by',
        'category_id',
        'title',
        'slug',
        'description',
    ];

    /**
     * The terms this course is sold on — one row per billing interval, so a
     * course can offer monthly and yearly side by side.
     */
    public function plans(): HasMany
    {
        return $this->hasMany(CoursePlan::class);
    }

    /**
     * Every price this course has ever been sold at, newest first.
     *
     * Repricing adds a row rather than editing the last one, so this is the
     * history — see CoursePrice.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(CoursePrice::class)->orderByDesc('id');
    }

    /**
     * The price in force on one set of terms: the newest entry for that
     * interval. Null for a course that has never been priced.
     */
    public function currentPrice(string $interval = 'month'): ?CoursePrice
    {
        // Reads the loaded history where there is one, so a listing that
        // eager-loaded `prices` does not go back to the database per row.
        if ($this->relationLoaded('prices')) {
            return $this->prices->firstWhere('billing_interval', $interval);
        }

        return $this->prices()->where('billing_interval', $interval)->first();
    }

    /**
     * The price in force on any terms, preferring monthly the same way plan()
     * does — for a card or a listing that shows one figure.
     */
    public function headlinePrice(): ?CoursePrice
    {
        return $this->currentPrice('month') ?? $this->currentPrice('year');
    }

    /**
     * The plan shown when only one can be: monthly, since that is what the
     * yearly price is discounted against. Falls back to whatever exists.
     */
    public function plan(): HasOne
    {
        // Prefers monthly rather than requiring it: a course sold only by the
        // year must still resolve to the plan it does have.
        return $this->hasOne(CoursePlan::class)
            ->orderByRaw("CASE WHEN billing_interval = 'month' THEN 0 ELSE 1 END")
            ->orderBy('id');
    }

    /**
     * A plan on specific terms, if the course is sold on them.
     */
    public function planFor(string $interval): ?CoursePlan
    {
        return $this->plans->firstWhere('billing_interval', $interval)
            ?? $this->plans()->where('billing_interval', $interval)->first();
    }

    /**
     * Whether this course is sold as a subscription yet, on any terms.
     */
    public function hasStripePlan(): bool
    {
        return $this->plans()->whereNotNull('stripe_price_id')->exists();
    }

    /**
     * A plain-text opening line from the description, for listings and cards.
     * The description is Quill HTML, so entities are decoded before trimming.
     */
    public function getExcerptAttribute(): string
    {
        $text = html_entity_decode(strip_tags((string) $this->description), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), 160);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function worksheets(): HasMany
    {
        return $this->hasMany(Worksheet::class);
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
        ];
    }
}
