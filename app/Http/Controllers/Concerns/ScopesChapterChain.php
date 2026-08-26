<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Database\Eloquent\Model;

/**
 * Guards the course › chapter › record chain.
 *
 * Route model binding resolves each part on its own, so nothing stops a URL
 * pairing a chapter with the wrong course. This is an HTTP concern — the answer
 * is a 404 — which is why it lives with the controllers rather than a service.
 */
trait ScopesChapterChain
{
    /**
     * @param  Model|null  $record  Anything with a `chapter_id`.
     */
    protected function scope(Course $course, Chapter $chapter, ?Model $record = null): void
    {
        abort_if($chapter->course_id !== $course->id, 404);

        abort_if($record && $record->chapter_id !== $chapter->id, 404);
    }
}
