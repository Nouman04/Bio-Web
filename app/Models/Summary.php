<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Summary extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    /**
     * Words a minute, for the "n min read" line. On the slow side of average,
     * because this is study material rather than prose.
     */
    private const READING_SPEED = 200;

    /**
     * A plain-text opening line from the content, for listings and cards. The
     * content is Quill HTML, so entities are decoded before trimming.
     */
    public function getExcerptAttribute(): string
    {
        return Str::limit($this->plainContent(), 180);
    }

    /**
     * Roughly how long this takes to read, in whole minutes — never zero, so a
     * one-line summary still reads as "1 min read".
     */
    public function getReadingMinutesAttribute(): int
    {
        $words = str_word_count($this->plainContent());

        return max(1, (int) ceil($words / self::READING_SPEED));
    }

    private function plainContent(): string
    {
        $text = html_entity_decode(strip_tags((string) $this->content), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    protected $fillable = [
        'chapter_id',
        'topic_id',
        'added_by',
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

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
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
