<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\UserModuleProgress;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * What the reader did with a module, reported from the page they are on. The
 * rules for what any of it means live in ProgressService — this only validates
 * and hands over.
 */
class ProgressController extends Controller
{
    public function __construct(private readonly ProgressService $progress)
    {
    }

    /**
     * How far through a video the player has got. Sent periodically while it
     * plays, so a reader who leaves half way keeps their position.
     */
    public function watched(Request $request, CourseModule $module): JsonResponse
    {
        $data = $request->validate([
            'percent' => ['required', 'integer', 'between:0,100'],
        ]);

        $row = $this->progress->watched($request->user(), $module, $data['percent']);

        return $this->state($module, $row);
    }

    /**
     * How long a note, diagram, guide or summary has been open. Below the
     * threshold nothing is recorded and the response says so.
     */
    public function viewed(Request $request, CourseModule $module): JsonResponse
    {
        $data = $request->validate([
            'seconds' => ['required', 'integer', 'min:0'],
        ]);

        $row = $this->progress->viewed($request->user(), $module, $data['seconds']);

        return $this->state($module, $row);
    }

    /**
     * The "Mark as complete" checkbox, which also unticks.
     */
    public function manual(Request $request, CourseModule $module): JsonResponse
    {
        $data = $request->validate([
            'completed' => ['required', 'boolean'],
        ]);

        $row = $this->progress->setManual($request->user(), $module, $data['completed']);

        return $this->state($module, $row);
    }

    /**
     * The answer every endpoint gives: where this module stands, and where the
     * chapter and course stand now, so the page can move its bars without a
     * reload.
     */
    private function state(CourseModule $module, ?UserModuleProgress $row): JsonResponse
    {
        $user = request()->user();
        $chapter = $module->chapter;

        return response()->json([
            'completed' => (bool) $row?->is_completed,
            'progress' => (int) ($row?->progress ?? 0),
            'completed_via' => $row?->completed_via,
            'chapter' => $chapter ? $this->progress->chapterProgress($user, $chapter) : null,
            'course' => $chapter?->course
                ? $this->progress->courseProgress($user, $chapter->course)
                : null,
        ]);
    }
}
