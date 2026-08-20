<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ChapterController extends Controller
{
    /**
     * Display the chapters belonging to a single course. The grid itself is
     * loaded by DataTables from the `courses.chapters.data` endpoint below.
     */
    public function index(Request $request, Course $course)
    {
        $courseModel = $course;

        return view('chapters.index', [
            'courseId' => $courseModel,
            'courseTitle' => $courseModel->title,
            'stats' => $this->stats($courseModel),
            'filters' => [
                'title' => $request->input('title', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the chapter list of one course.
     */
    public function data(Request $request, Course $course): JsonResponse
    {
        $chapters = Chapter::query()->where('course_id', $course->id);

        // Filters from the filter card above the table.
        $chapters->when(
            $request->input('search_term'),
            fn ($query, $title) => $query->where('title', 'like', "%{$title}%")
        );

        $chapters->when(
            $request->input('status'),
            fn ($query, $status) => $query->where('status', $status)
        );

        $table = DataTables::eloquent($chapters)
            ->addColumn('number_cell', fn (Chapter $chapter) => view('chapters.partials.number-cell', compact('chapter'))->render())
            ->addColumn('title_cell', fn (Chapter $chapter) => view('chapters.partials.title-cell', ['chapter' => $chapter, 'courseId' => $course])->render())
            ->addColumn('status_cell', fn (Chapter $chapter) => view('chapters.partials.status-cell', compact('chapter'))->render())
            ->addColumn('action', fn (Chapter $chapter) => view('chapters.partials.actions', ['chapter' => $chapter, 'courseId' => $course])->render())
            ->orderColumn('number_cell', 'chapter_number $1')
            ->orderColumn('title_cell', 'title $1')
            ->orderColumn('status_cell', 'status $1')
            ->rawColumns(['number_cell', 'title_cell', 'status_cell', 'action'])
            ->only(['number_cell', 'title_cell', 'status_cell', 'action'])
            // Rides along on the DataTables payload so the stat cards stay in
            // step with the grid without a second round trip.
            ->with('stats', $this->stats($course))
            ->toJson();

        // Never let a proxy or the browser replay an old page of rows.
        return $table->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Display the management dashboard hub for a single chapter.
     */
    public function dashboard(Course $course, Chapter $chapter)
    {
        $courseModel = $course;
        $chapterModel = $chapter;

        abort_if($chapterModel->course_id !== $courseModel->id, 404);

        return view('chapters.dashboard', [
            'courseId' => $courseModel,
            'courseTitle' => $courseModel->title,
            'chapter' => [
                // The public identifier: the tiles below build URLs from it.
                'id' => $chapterModel->uuid,
                'num' => $chapterModel->chapter_number,
                'title' => $chapterModel->title,
                'desc' => $chapterModel->description,
                'status' => $chapterModel->status,
            ],
        ]);
    }

    /**
     * Chapter counts for the stat cards. Unfiltered on purpose — the cards
     * describe the course, not the current filter.
     */
    private function stats(Course $course): array
    {
        $total = Chapter::where('course_id', $course->id)->count();
        $drafts = Chapter::where('course_id', $course->id)->where('status', 'Draft')->count();

        return [
            'total' => $total,
            'published' => $total - $drafts,
            'drafts' => $drafts,
        ];
    }

    /**
     * Store a newly created chapter under the given course.
     */
    public function store(Request $request, Course $course)
    {
        $data = $this->validated($request, $course);

        $chapter = Chapter::create($data);

        return $this->respond($request, $course, $chapter->fresh(), 'Chapter created successfully.', 201);
    }

    /**
     * Update the given chapter.
     */
    public function update(Request $request, Course $course, Chapter $chapter)
    {
        $chapter->update($this->validated($request, $course));

        return $this->respond($request, $course, $chapter->fresh(), 'Chapter updated successfully.');
    }

    /**
     * Soft delete the given chapter.
     */
    public function destroy(Request $request, Course $course, Chapter $chapter)
    {
        $chapter->delete();

        return $this->respond($request, $course, null, 'Chapter deleted successfully.');
    }

    /**
     * Shared validation. The course comes from the URL, and the modals post the
     * chapter number as `num` and the description as `desc`.
     */
    private function validated(Request $request, Course $course): array
    {
        $request->merge([
            'course_id' => $course->id,
            'chapter_number' => $request->input('chapter_number', $request->input('num')),
            'description' => $request->input('description', $request->input('desc')),
        ]);

        return $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'chapter_number' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string'],
            'status' => ['required', 'in:Draft,Published'],
        ]);
    }

    /**
     * JSON for fetch/AJAX callers, a redirect back to the chapter list for plain
     * form posts.
     */
    private function respond(Request $request, Course $course, ?Chapter $chapter, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'data' => $chapter,
            ], fn ($value) => $value !== null), $status);
        }

        return redirect()->route('courses.chapters', $course)->with('success', $message);
    }
}
