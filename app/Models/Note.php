<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Scout\Searchable;

class Note extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $fillable = [
        'chapter_id',
        'topic_id',
        'summary_id',
        'title',
        'type',
        'content',
    ];

    /**
     * The chapter summary this note was written from — only set on notes of
     * type `summary`.
     */

    /**
     * A plain-text opening line from the content, for listings and cards. The
     * content is Quill HTML, so entities are decoded before trimming.
     */
    public function getExcerptAttribute(): string
    {
        $text = html_entity_decode(strip_tags((string) $this->content), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), 160);
    }

    public function summary(): BelongsTo
    {
        return $this->belongsTo(Summary::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Flashcard decks built from this note.
     */
    public function flashcards(): MorphMany
    {
        return $this->morphMany(Flashcard::class, 'flashcardable');
    }

    public function questionables()
    {
        return $this->morphMany(QuestionableType::class, 'questionable');
    }

    public function attachments()
    {
        // The attachments table stores `attachmentable_type` / `attachmentable_id`.
        return $this->morphMany(Attachment::class, 'attachmentable');
    }



    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'content' => $this->content,
        ];
    }
}
