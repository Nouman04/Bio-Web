<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Scout\Searchable;

/**
 * One thing a student has bookmarked. Everything saved shows up on the
 * resources page, which is why this knows how to name and link each kind.
 */
class SavedContent extends Model
{
    use HasUuid, Searchable;

    /**
     * The kinds that can be saved, keyed by the short name the UI posts.
     * Anything not listed here cannot be bookmarked.
     */
    public const TYPES = [
        'note' => Note::class,
        'flashcard' => Flashcard::class,
        'diagram' => Diagram::class,
        'summary' => Summary::class,
        'video' => VideoLesson::class,
        'guide' => Guide::class,
    ];

    /**
     * The student route each kind is read on, and how the resources page
     * labels and illustrates it.
     */
    public const PRESENTATION = [
        'note' => ['route' => 'student.chapters.notes.show', 'key' => 'noteId', 'label' => 'Study Note', 'icon' => 'sticky_note_2'],
        'flashcard' => ['route' => 'student.chapters.flashcards.show', 'key' => 'flashcardId', 'label' => 'Flashcard Set', 'icon' => 'style'],
        'diagram' => ['route' => 'student.chapters.diagrams.show', 'key' => 'diagramId', 'label' => 'Diagram', 'icon' => 'account_tree'],
        'summary' => ['route' => 'student.chapters.summaries.show', 'key' => 'summaryId', 'label' => 'Summary', 'icon' => 'description'],
        'video' => ['route' => 'student.chapters.videos.show', 'key' => 'videoId', 'label' => 'Video Lesson', 'icon' => 'play_circle'],
        'guide' => ['route' => 'student.chapters.guides.show', 'key' => 'guideId', 'label' => 'Guide', 'icon' => 'menu_book'],
    ];

    protected $fillable = [
        'user_id',
        'contentable_type',
        'contentable_id',
    ];

    public function contentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The short key for a model, or null if that kind cannot be saved.
     */
    public static function keyFor(Model $record): ?string
    {
        return array_search($record::class, self::TYPES, true) ?: null;
    }

    /**
     * The short key for this row's content.
     */
    public function getTypeKeyAttribute(): ?string
    {
        return array_search($this->contentable_type, self::TYPES, true) ?: null;
    }

    /**
     * Where this saved item is read. Null when the content has since been
     * deleted, or lost the chapter it belonged to.
     */
    public function getUrlAttribute(): ?string
    {
        $record = $this->contentable;
        $key = $this->type_key;
        $chapter = $record?->chapter;

        if (! $record || ! $key || ! $chapter?->course) {
            return null;
        }

        $shape = self::PRESENTATION[$key];

        return route($shape['route'], [
            'courseId' => $chapter->course->uuid,
            'chapterId' => $chapter->uuid,
            $shape['key'] => $record->uuid,
        ]);
    }

    public function getLabelAttribute(): string
    {
        return self::PRESENTATION[$this->type_key]['label'] ?? 'Resource';
    }

    public function getIconAttribute(): string
    {
        return self::PRESENTATION[$this->type_key]['icon'] ?? 'bookmark';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'contentable_type' => $this->contentable_type,
        ];
    }
}
