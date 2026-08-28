<?php

namespace App\Models;

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
    use SoftDeletes, HasUuid, Searchable;

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
