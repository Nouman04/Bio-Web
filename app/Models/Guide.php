<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Guide extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    /**
     * The kinds of guide, matching the column's enum, with the label each is
     * shown under.
     */
    public const TYPE_LABELS = [
        'theory_guides' => 'Theory Guide',
        'atp_guides' => 'ATP Guide',
    ];

    /**
     * This guide's kind, as a person would read it.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? 'Guide';
    }

    /**
     * A plain-text opening line from the content, for listings and cards.
     */
    public function getExcerptAttribute(): string
    {
        $text = html_entity_decode(strip_tags((string) $this->content), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), 160);
    }

    protected $fillable = [
        'chapter_id',
        'topic_id',
        'added_by',
        'type',
        'title',
        'slug',
        'content',
    ];

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
            'content' => $this->content,
        ];
    }
}
