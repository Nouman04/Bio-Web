<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class Topic extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $fillable = [
        'chapter_id',
        'parent_topic_id',
        'title',
        'content',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * The topic this one sits under. It may belong to another chapter, so its
     * chapter is loaded wherever the parent is named.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_topic_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_topic_id');
    }

    /**
     * A plain-text opening line from the content, for listings and cards. The
     * content is Quill HTML, so entities are decoded before trimming.
     */
    public function getExcerptAttribute(): string
    {
        $text = html_entity_decode(strip_tags((string) $this->content), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), 160);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(Summary::class);
    }

    public function questionBank(): HasMany
    {
        return $this->hasMany(QuestionBank::class);
    }

    public function videoLessons(): HasMany
    {
        return $this->hasMany(VideoLesson::class);
    }

    public function guides(): HasMany
    {
        return $this->hasMany(Guide::class);
    }

    public function diagrams(): HasMany
    {
        return $this->hasMany(Diagram::class);
    }

    /**
     * Questions linked to this topic via topics_questions pivot.
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBank::class, 'topics_questions', 'topics_id', 'question_id')
            ->withPivot('referenced_date')
            ->withTimestamps();
    }

    public function topicQuestions(): HasMany
    {
        return $this->hasMany(TopicQuestion::class, 'topics_id');
    }

    public function worksheetQuestions(): HasMany
    {
        return $this->hasMany(WorksheetQuestion::class, 'topics_id');
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
        ];
    }
}
