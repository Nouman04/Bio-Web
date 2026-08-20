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
     * What this course is sold for. Billing lives on its own row rather than in
     * columns on the course.
     */
    public function plan(): HasOne
    {
        return $this->hasOne(CoursePlan::class);
    }

    /**
     * Whether this course is sold as a subscription yet.
     */
    public function hasStripePlan(): bool
    {
        return (bool) $this->plan?->isSellable();
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
