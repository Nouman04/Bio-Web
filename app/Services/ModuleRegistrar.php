<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\CourseModule;
use App\Models\Diagram;
use App\Models\Guide;
use App\Models\Note;
use App\Models\Quiz;
use App\Models\Summary;
use App\Models\VideoLesson;
use Illuminate\Database\Eloquent\Model;

/**
 * Keeps course_modules in step with the content tables. A note is written in
 * the notes table; it becomes a trackable unit only once it has a row here.
 *
 * Content is registered as it is created (see ModuleObserver) and backfilled by
 * `php artisan progress:sync-modules`.
 */
class ModuleRegistrar
{
    /**
     * Default weights per type. Every unit counts the same to begin with, which
     * makes a percentage read as "n of m done". Raise one — a final quiz at 3 —
     * where a unit should carry more.
     */
    public const DEFAULT_WEIGHTS = [
        'video' => 1,
        'quiz' => 1,
        'note' => 1,
        'diagram' => 1,
        'guide' => 1,
        'summary' => 1,
    ];

    /**
     * Registers a piece of content as a trackable unit, or updates the row it
     * already has. Returns null for anything that is not a tracked type or has
     * no chapter to belong to.
     */
    public function register(Model $record, ?int $weight = null): ?CourseModule
    {
        $type = CourseModule::typeFor($record);
        $chapterId = $this->chapterIdFor($record);

        if (! $type || ! $chapterId) {
            return null;
        }

        $module = CourseModule::withTrashed()->firstOrNew([
            'moduleable_type' => $record::class,
            'moduleable_id' => $record->id,
        ]);

        $module->fill([
            'chapter_id' => $chapterId,
            'type' => $type,
            // An existing weight is left alone, so a hand-tuned one survives a
            // resync of the content it belongs to.
            'weight' => $weight ?? $module->weight ?? self::DEFAULT_WEIGHTS[$type],
            'is_active' => true,
        ]);

        $module->deleted_at = null;
        $module->save();

        return $module;
    }

    /**
     * Stops a unit counting, without erasing anyone's completion of it. Called
     * when the content behind it is deleted.
     */
    public function deactivate(Model $record): void
    {
        CourseModule::where('moduleable_type', $record::class)
            ->where('moduleable_id', $record->id)
            ->update(['is_active' => false]);
    }

    /**
     * Brings a unit back when its content is restored from the bin.
     */
    public function reactivate(Model $record): void
    {
        CourseModule::where('moduleable_type', $record::class)
            ->where('moduleable_id', $record->id)
            ->update(['is_active' => true]);
    }

    /**
     * Registers everything that exists today, and deactivates modules whose
     * content has since gone. Returns what it did, for the command to report.
     *
     * @return array{registered:int, deactivated:int}
     */
    public function syncAll(): array
    {
        $registered = 0;
        $seen = [];

        foreach ($this->sources() as $query) {
            foreach ($query->cursor() as $record) {
                if ($module = $this->register($record)) {
                    $seen[] = $module->id;
                    $registered++;
                }
            }
        }

        // Anything not seen this pass no longer has live content behind it.
        $deactivated = CourseModule::query()
            ->when($seen !== [], fn ($query) => $query->whereNotIn('id', $seen))
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return ['registered' => $registered, 'deactivated' => $deactivated];
    }

    /**
     * The queries that produce every trackable record.
     *
     * @return array<int, \Illuminate\Database\Eloquent\Builder>
     */
    private function sources(): array
    {
        return [
            VideoLesson::query()->whereNotNull('chapter_id'),
            Note::query()->whereNotNull('chapter_id'),
            Diagram::query()->whereNotNull('chapter_id'),
            Guide::query()->whereNotNull('chapter_id'),
            Summary::query()->whereNotNull('chapter_id'),
            // A quiz reaches its chapter through the quizzes_chapters pivot.
            Quiz::query()->whereHas('chapters'),
        ];
    }

    /**
     * Which chapter a record belongs to. Most carry a chapter_id; a quiz is
     * joined to one through a pivot instead.
     */
    private function chapterIdFor(Model $record): ?int
    {
        if ($record instanceof Quiz) {
            return $record->chapters()->first()?->id
                ?? $record->quizChapters()->value('chapter_id');
        }

        $chapterId = $record->getAttribute('chapter_id');

        // A stale chapter_id would break the foreign key on insert.
        return $chapterId && Chapter::whereKey($chapterId)->exists()
            ? (int) $chapterId
            : null;
    }
}
