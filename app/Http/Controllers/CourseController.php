<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CourseController extends Controller
{
    /**
     * Display a listing of courses. The table itself is loaded by DataTables
     * from the `courses.data` endpoint below.
     */
    public function index(Request $request)
    {
        return view('courses.index', [
            'categories' => Category::orderBy('title')->get(['id', 'uuid', 'title']),
            'instructors' => User::whereIn('id', Course::select('created_by'))
                ->orderBy('name')
                ->get(['id', 'uuid', 'name']),
            'filters' => [
                'search' => $request->input('search', ''),
                'category' => $request->input('category', ''),
                'created_by' => $request->input('created_by', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the courses list.
     */
    public function data(Request $request): JsonResponse
    {
        $courses = Course::query()
            ->with(['category:id,title', 'creator:id,name'])
            ->withCount('chapters');

        // Filters from the filter card above the table. DataTables reserves the
        // `search` key for its own box, so ours arrives as `search_term`.
        $courses->when($request->input('search_term'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('creator', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        });

        $courses->when($request->input('category'), fn ($query, $uuid) => $query->whereRelation('category', 'uuid', $uuid));
        $courses->when($request->input('created_by'), fn ($query, $uuid) => $query->whereRelation('creator', 'uuid', $uuid));

        $table = DataTables::eloquent($courses)
            ->addColumn('title_cell', fn (Course $course) => view('courses.partials.title-cell', compact('course'))->render())
            ->addColumn('category_name', fn (Course $course) => view('courses.partials.category-cell', compact('course'))->render())
            ->addColumn('creator_name', fn (Course $course) => view('courses.partials.creator-cell', compact('course'))->render())
            ->addColumn('chapters_cell', fn (Course $course) => view('courses.partials.chapters-cell', compact('course'))->render())
            ->addColumn('action', fn (Course $course) => view('courses.partials.actions', compact('course'))->render())
            ->orderColumn('title_cell', 'title $1')
            ->orderColumn('chapters_cell', 'chapters_count $1')
            ->rawColumns(['title_cell', 'category_name', 'creator_name', 'chapters_cell', 'action'])
            ->only(['title_cell', 'category_name', 'creator_name', 'chapters_cell', 'action'])
            ->toJson();

        // Never let a proxy or the browser replay an old page of rows.
        return $table->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['created_by'] = $request->user()->id;

        $course = Course::create($data);

        return $this->respond($request, $course->fresh(), 'Course created successfully.', 201);
    }

    /**
     * Update the given course.
     */
    public function update(Request $request, Course $course)
    {
        $course->update($this->validated($request, $course));

        return $this->respond($request, $course->fresh(), 'Course updated successfully.');
    }

    /**
     * Soft delete the given course.
     */
    public function destroy(Request $request, Course $course)
    {
        $course->delete();

        return $this->respond($request, null, 'Course deleted successfully.');
    }

    /**
     * The course's configuration page — currently which of its chapters are
     * public and which are private.
     */
    public function configuration(Course $course)
    {
        return view('courses.configuration', [
            'course' => $course,
            'chapters' => $course->chapters()->orderBy('chapter_number')->get(),
        ]);
    }

    /**
     * Save the visibility chosen for each chapter. Only chapters that belong to
     * this course are written, so a forged id cannot reach another course's.
     */
    public function updateConfiguration(Request $request, Course $course)
    {
        $data = $request->validate([
            'chapters' => ['nullable', 'array'],
            'chapters.*' => [Rule::in(['public', 'private'])],
        ], [
            'chapters.*.in' => 'A chapter can only be public or private.',
        ]);

        $chapters = $course->chapters()->pluck('id')->all();
        $changed = 0;

        foreach ($data['chapters'] ?? [] as $id => $visibility) {
            if (! in_array((int) $id, $chapters, true)) {
                continue;
            }

            $changed += Chapter::where('id', $id)
                ->where('visibility', '!=', $visibility)
                ->update(['visibility' => $visibility]);
        }

        return $this->respond(
            $request,
            $course->fresh(),
            $changed === 1
                ? '1 chapter updated.'
                : ($changed ? "{$changed} chapters updated." : 'No changes to save.')
        );
    }

    /**
     * Shared validation. On update the slug ignores the course's own row, and
     * a blank slug falls back to one derived from the title.
     */
    private function validated(Request $request, ?Course $course = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('title')),
        ]);

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses', 'slug')->ignore($course?->id),
            ],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
        ]);
    }

    /**
     * JSON for fetch/AJAX callers, a redirect back to the listing for plain
     * form posts.
     */
    private function respond(Request $request, ?Course $course, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'data' => $course,
            ], fn ($value) => $value !== null), $status);
        }

        return redirect()->route('courses')->with('success', $message);
    }
}
