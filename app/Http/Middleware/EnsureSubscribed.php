<?php

namespace App\Http\Middleware;

use App\Models\Chapter;
use App\Models\Course;
use App\Services\StripeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps paid chapters to paying readers.
 *
 * The rule was written out by hand in two controllers, which meant every new
 * route under a chapter had to remember it. It lives here now, so a route is
 * either covered or it is not — visible in the route file rather than buried in
 * an action.
 *
 * Public chapters stay open: they are the free preview that the subscription
 * page is selling against. Pass `subscribed:strict` to require a subscription
 * for a course whatever its chapters say.
 */
class EnsureSubscribed
{
    public function __construct(private readonly StripeService $stripe)
    {
    }

    public function handle(Request $request, Closure $next, ?string $mode = null): Response
    {
        $course = $this->courseFrom($request);

        // Nothing course-shaped in the URL: not this middleware's business.
        if (! $course) {
            return $next($request);
        }

        $chapter = $this->chapterFrom($request, $course);

        if ($chapter) {
            abort_if($chapter->course_id !== $course->id, 404);
        }

        $subscribed = $this->stripe->subscribedTo($request->user(), $course);

        if ($subscribed) {
            return $next($request);
        }

        // A public chapter is readable without paying, unless the route asked
        // for the whole course to be gated.
        if ($mode !== 'strict' && $chapter && $chapter->visibility === 'public') {
            return $next($request);
        }

        if ($mode !== 'strict' && ! $chapter) {
            return $next($request);
        }

        return $this->paywall($request, $course, $chapter);
    }

    /**
     * Where an unsubscribed reader is sent: the page that explains what a
     * subscription includes, for the chapter they were trying to open.
     */
    private function paywall(Request $request, Course $course, ?Chapter $chapter): Response
    {
        $url = $chapter
            ? route('public.course.chapter.subscribe', [$course, $chapter])
            : route('public.subscribe.plans', $course);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'A subscription is needed to open this.',
                'url' => $url,
            ], 403);
        }

        return redirect()->to($url);
    }

    /**
     * The course from the URL, however the route names it.
     */
    private function courseFrom(Request $request): ?Course
    {
        $value = $request->route('courseId') ?? $request->route('course');

        if ($value instanceof Course) {
            return $value;
        }

        return $value ? Course::where('uuid', $value)->first() : null;
    }

    private function chapterFrom(Request $request, Course $course): ?Chapter
    {
        $value = $request->route('chapterId') ?? $request->route('chapter');

        if ($value instanceof Chapter) {
            return $value;
        }

        return $value ? Chapter::where('uuid', $value)->first() : null;
    }
}
