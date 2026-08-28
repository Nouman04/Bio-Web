<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\ProgressService;
use App\Services\StripeService;
use Illuminate\Http\Request;

class StudentCoursesController extends Controller
{
    public function __construct(
        private readonly ProgressService $progress,
        private readonly StripeService $stripe,
    ) {
    }

    /**
     * The student's course hub: the courses they subscribe to.
     *
     * Anything they have not paid for belongs in the catalog, not here — so an
     * empty hub points them there rather than showing courses they cannot open.
     */
    public function index(Request $request)
    {
        $subscribed = $this->stripe->subscribedCourseUuids($request->user());

        $courses = Course::query()
            ->whereIn('uuid', $subscribed)
            ->with(['category:id,title', 'creator:id,name', 'plan'])
            ->withCount('chapters')
            ->when(
                $request->input('category'),
                fn ($query, $title) => $query->whereHas('category', fn ($q) => $q->where('title', $title))
            )
            ->latest('id')
            ->get();

        // Free courses are open without paying, so they sit alongside the paid
        // ones rather than in the catalog.
        $trial = Course::query()
            ->whereDoesntHave('plan', fn ($query) => $query->whereNotNull('stripe_price_id'))
            ->whereNotIn('uuid', $subscribed)
            ->with(['category:id,title', 'creator:id,name'])
            ->withCount('chapters')
            ->latest('id')
            ->get();

        return view('student.courses.index', [
            'courses' => $courses,
            'trial' => $trial,
            'categories' => $courses->pluck('category.title')->filter()->unique()->sort()->values(),
            'category' => $request->input('category'),
            // Keyed by course id, so a card reads its own bar without a query.
            'progress' => $this->progress->courseProgressFor(
                $request->user(),
                $courses->concat($trial)
            ),
        ]);
    }

    /**
     * A course opens on its chapter list, which is the real page — there is no
     * separate course detail screen.
     */
    public function show(Course $course)
    {
        return redirect()->route('student.chapters', ['courseId' => $course->uuid]);
    }
}
