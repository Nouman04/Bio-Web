<?php

namespace App\Models;

use App\Http\Controllers\FlashcardController;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Flashcard extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $fillable = [
        'title',
        'chapter_id',
        'flashcardable_id',
        'flashcardable_type',
    ];

    /**
     * The content this deck was built from — a note, video lesson, guide,
     * summary, diagram, topic, and so on.
     */
    public function flashcardable(): MorphTo
    {
        return $this->morphTo();
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * The questions on the deck, in the order they were arranged.
     */
    public function assessments(): MorphMany
    {
        return $this->morphMany(Assessment::class, 'assessmentable')->orderBy('order');
    }

    /**
     * The short key the UI posts for this deck's source type — `note`,
     * `video_lesson`, and so on. Null for a standalone deck.
     */
    public function getSourceKeyAttribute(): ?string
    {
        $key = array_search($this->flashcardable_type, FlashcardController::SOURCES, true);

        return $key === false ? null : $key;
    }

    /**
     * Label of the linked record, for pre-filling the edit modal's picker.
     */
    public function getSourceLabelAttribute(): ?string
    {
        $source = $this->flashcardable;

        if (! $source) {
            return null;
        }

        $value = strip_tags((string) ($source->title ?? $source->content ?? ''));
        $value = trim(preg_replace('/\s+/u', ' ', $value));

        return Str::limit($value, 70) ?: "#{$source->id}";
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
