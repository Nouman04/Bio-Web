<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A completable unit of a chapter: one video, quiz, note, diagram, guide or
 * summary. The content itself lives in its own table — this is the registry
 * that says the unit counts towards progress, and for how much.
 */
class CourseModule extends Model
{
    use SoftDeletes, HasUuid;

    /**
     * The unit types progress is tracked for, mapped to the model each stands
     * for. Adding a type means adding it here and to the table's enum.
     */
    public const TYPES = [
        'video' => VideoLesson::class,
        'quiz' => Quiz::class,
        'note' => Note::class,
        'diagram' => Diagram::class,
        'guide' => Guide::class,
        'summary' => Summary::class,
    ];

    /**
     * Types a reader finishes by reading rather than by doing.
     */
    public const READING_TYPES = ['note', 'diagram', 'guide', 'summary'];

    protected $fillable = [
        'chapter_id',
        'type',
        'moduleable_type',
        'moduleable_id',
        'weight',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The record this module stands for.
     */
    public function moduleable(): MorphTo
    {
        return $this->morphTo();
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserModuleProgress::class);
    }

    /**
     * One user's progress on this module, if they have any.
     */
    public function progressFor(User $user): ?UserModuleProgress
    {
        return $this->progress()->where('user_id', $user->id)->first();
    }

    /**
     * The type name for a model, or null if it is not a tracked unit.
     */
    public static function typeFor(Model $record): ?string
    {
        return array_search($record::class, self::TYPES, true) ?: null;
    }

    /**
     * Only the modules that still count. Content that has been removed is
     * deactivated rather than deleted, so it drops out of the denominator
     * without erasing anyone's completion history.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Whether this unit is finished by reading it rather than by doing it.
     */
    public function isReading(): bool
    {
        return in_array($this->type, self::READING_TYPES, true);
    }

    /**
     * The title of whatever this module points at, for listings.
     */
    public function getTitleAttribute(): string
    {
        return (string) ($this->moduleable?->title ?? 'Untitled');
    }
}
