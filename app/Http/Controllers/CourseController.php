<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseConfigurationRequest;
use App\Http\Requests\Course\UpdateCoursePriceRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Models\CoursePrice;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use App\Services\CourseService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

/**
 * Courses: the listing, the CRUD behind it, and the per-course configuration
 * page.
 *
 * This decides what the request asked for and what to send back; CourseService
 * decides what it means for the database.
 */
class CourseController extends Controller
{
    public function __construct(private readonly CourseService $courses)
    {
    }

    /**
     * The listing page. The table itself is loaded by DataTables from the
     * `courses.data` endpoint below.
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
     *
     * DataTables owns this response shape — it renders Blade partials into
     * cells rather than returning models — so an API resource has nothing to
     * describe here.
     */
    public function data(Request $request): JsonResponse
    {
        // DataTables reserves `search` for its own box, so ours arrives as
        // `search_term`.
        $courses = $this->courses->listing([
            'search' => $request->input('search_term'),
            'category' => $request->input('category'),
            'created_by' => $request->input('created_by'),
        ]);

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

    public function store(StoreCourseRequest $request)
    {
        $course = $this->courses->create($request->validated(), $request->user(), $request->file('image'));

        return $this->respond($request, $course, 'Course created successfully.', 201);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course = $this->courses->update($course, $request->validated(), $request->file('image'));

        return $this->respond($request, $course, 'Course updated successfully.');
    }

    public function destroy(Request $request, Course $course)
    {
        $this->courses->delete($course);

        return $this->respond($request, null, 'Course deleted successfully.');
    }

    /**
     * What the course costs today, and what it has cost before.
     *
     * The pricing modal reads this when it opens: the fields are filled from
     * the current price, and the history below it is the record of every change
     * since — which is the point of keeping one.
     */
    public function pricing(Course $course): JsonResponse
    {
        $course->load('prices.author:id,name');

        return response()->json([
            'current' => collect(StripeService::INTERVALS)
                ->mapWithKeys(fn (string $interval) => [
                    $interval => $this->pricePayload($course->currentPrice($interval)),
                ]),
            'history' => $course->prices
                ->map(fn (CoursePrice $price) => $this->pricePayload($price) + [
                    'set_by' => $price->author?->name,
                    'set_at' => $price->created_at?->format('j M Y, g:ia'),
                ])
                ->values(),
        ]);
    }

    /**
     * Sets a new price. The old one is kept — see CourseService::reprice().
     */
    public function updatePrice(UpdateCoursePriceRequest $request, Course $course)
    {
        $price = $this->courses->reprice($course, $request->terms(), $request->user());

        return $this->respond(
            $request,
            $course->fresh(),
            "Price updated to {$price->formatted_price} per {$price->billing_interval}."
        );
    }

    /**
     * One price, in the shape the modal reads.
     *
     * @return array<string, mixed>|null
     */
    private function pricePayload(?CoursePrice $price): ?array
    {
        if (! $price) {
            return null;
        }

        return [
            'interval' => $price->billing_interval,
            // The form works in whole currency, the column in cents.
            'price' => round($price->price / 100, 2),
            'formatted_price' => $price->formatted_price,
            'formatted_payable' => $price->formatted_payable,
            'promo_code' => $price->promo_code,
            'promo_type' => $price->promo_type,
            'promo_value' => $price->promo_type === 'amount' && $price->promo_value !== null
                ? round($price->promo_value / 100, 2)
                : $price->promo_value,
            'promo_expires_at' => $price->promo_expires_at?->format('Y-m-d\TH:i'),
            'promo_label' => $price->promo_label,
            'live_promo' => $price->hasLivePromo(),
        ];
    }

    /**
     * The course's configuration page — currently which of its chapters are
     * public and which are private.
     */
    public function configuration(Course $course)
    {
        return view('courses.configuration', [
            'course' => $course,
            'chapters' => $this->courses->chaptersFor($course),
        ]);
    }

    public function updateConfiguration(UpdateCourseConfigurationRequest $request, Course $course)
    {
        $changed = $this->courses->saveChapterVisibility(
            $course,
            $request->validated()['chapters'] ?? []
        );

        return $this->respond($request, $course->fresh(), match (true) {
            $changed === 1 => '1 chapter updated.',
            $changed > 1 => "{$changed} chapters updated.",
            default => 'No changes to save.',
        });
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
