<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Everything that happens to a course, and to the chapter visibility settings
 * that hang off it.
 */
class CourseService
{
    /**
     * The courses list, filtered as the filter card asks.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listing(array $filters = []): Builder
    {
        return Course::query()
            ->with(['category:id,title', 'creator:id,name'])
            ->withCount('chapters')
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query
                ->where(fn (Builder $q) => $q
                    ->where('title', 'like', "%{$search}%")
                    ->orWhereHas('creator', fn ($c) => $c->where('name', 'like', "%{$search}%"))))
            ->when($filters['category'] ?? null, fn (Builder $query, string $uuid) => $query
                ->whereRelation('category', 'uuid', $uuid))
            ->when($filters['created_by'] ?? null, fn (Builder $query, string $uuid) => $query
                ->whereRelation('creator', 'uuid', $uuid));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $author): Course
    {
        $data['created_by'] = $author->id;

        return Course::create($data)->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Course $course, array $data): Course
    {
        $course->update($data);

        return $course->fresh();
    }

    public function delete(Course $course): void
    {
        $course->delete();
    }

    /**
     * Saves the visibility chosen for each chapter, and says how many actually
     * changed.
     *
     * Only chapters belonging to this course are written, so a forged id cannot
     * reach another course's.
     *
     * @param  array<int|string, string>  $visibilities
     */
    public function saveChapterVisibility(Course $course, array $visibilities): int
    {
        $owned = $course->chapters()->pluck('id')->all();
        $changed = 0;

        foreach ($visibilities as $id => $visibility) {
            if (! in_array((int) $id, $owned, true)) {
                continue;
            }

            $changed += Chapter::where('id', $id)
                ->where('visibility', '!=', $visibility)
                ->update(['visibility' => $visibility]);
        }

        return $changed;
    }

    /**
     * The chapters shown on the configuration page.
     */
    public function chaptersFor(Course $course)
    {
        return $course->chapters()->orderBy('chapter_number')->get();
    }
}
