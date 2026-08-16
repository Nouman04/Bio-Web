<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class VideoLesson extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $fillable = [
        'chapter_id',
        'topic_id',
        'added_by',
        'title',
        'slug',
        'description',
        'file_path',
        'external_link',
    ];

    /**
     * Where the lesson can actually be watched: the uploaded file if there is
     * one, otherwise the external link.
     *
     * Built with asset() rather than Storage::url(), because the latter is
     * pinned to APP_URL and breaks whenever the app is served on another port
     * or from a subdirectory.
     */
    public function getVideoUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }

        return $this->external_link ?: null;
    }

    /**
     * Whether this lesson is hosted elsewhere rather than uploaded.
     */
    public function getIsExternalAttribute(): bool
    {
        return ! $this->file_path && (bool) $this->external_link;
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function questionables()
    {
        return $this->morphMany(QuestionableType::class, 'questionable');
    }

    /**
     * Flashcard decks built from this record.
     */
    public function flashcards()
    {
        return $this->morphMany(Flashcard::class, "flashcardable");
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
