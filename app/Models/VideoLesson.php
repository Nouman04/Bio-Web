<?php

namespace App\Models;

use App\Models\Concerns\HasAttachments;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Laravel\Scout\Searchable;

class VideoLesson extends Model
{
    use SoftDeletes, HasUuid, HasAttachments, Searchable;

    protected $fillable = [
        'chapter_id',
        'topic_id',
        'added_by',
        'title',
        'slug',
        'description',
        'external_link',
    ];

    /**
     * The uploaded file, if the lesson was uploaded rather than linked.
     *
     * The path used to be a column on this table; it lives in `attachments`
     * with every other upload now, under the `video` collection.
     */
    public function video(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachmentable')
            ->where('collection', Attachment::VIDEO)
            ->latestOfMany();
    }

    /**
     * The stored path, for the handful of places that want it rather than a
     * URL. Null when the lesson is an external link.
     */
    public function getFilePathAttribute(): ?string
    {
        return $this->video?->file_path;
    }

    /**
     * Where the lesson can actually be watched: the uploaded file if there is
     * one, otherwise the external link.
     */
    public function getVideoUrlAttribute(): ?string
    {
        return $this->video?->url ?: ($this->external_link ?: null);
    }

    /**
     * Whether this lesson is hosted elsewhere rather than uploaded.
     */
    public function getIsExternalAttribute(): bool
    {
        return ! $this->video && (bool) $this->external_link;
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
