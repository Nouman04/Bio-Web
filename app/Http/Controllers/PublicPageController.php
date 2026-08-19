<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The public-facing marketing site: home, about, and the support and legal
 * pages linked from the footer. Everything here is open to visitors.
 */
class PublicPageController extends Controller
{
    /**
     * Static pages, keyed by route name so each one needs no method of its own.
     */
    private const PAGES = [
        'about' => 'about-us',
        'contact' => 'contact-us',
        'faq' => 'faq',
        'disclaimer' => 'disclaimer',
        'privacy' => 'privacy-policy',
        'terms' => 'terms-and-condition',
    ];

    /**
     * The landing page. Its "Our Courses" section shows the five newest
     * courses, newest first.
     */
    public function home()
    {
        return view('public.home', [
            'courses' => Course::query()
                ->with('category:id,title')
                ->withCount('chapters')
                ->latest('id')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * The course gallery: every course, searchable by title and filterable by
     * one or more instructors, a page at a time.
     */
    public function courses(Request $request)
    {
        // The picker posts uuids, so the filter matches the creator's uuid.
        $instructorUuids = array_filter((array) $request->input('instructors', []));

        $courses = Course::query()
            ->with('category:id,title', 'creator:id,name')
            ->withCount('chapters')
            ->when(
                $request->input('search'),
                fn ($query, $title) => $query->where('title', 'like', "%{$title}%")
            )
            ->when($instructorUuids, fn ($query) => $query->whereHas(
                'creator',
                fn ($creator) => $creator->whereIn('uuid', $instructorUuids)
            ))
            ->latest('id')
            ->paginate(9)
            // Keeps the filters on the page links.
            ->withQueryString();

        return view('public.courses', [
            'courses' => $courses,
            'filters' => [
                'search' => $request->input('search', ''),
                // Pre-selected instructors need their names for Tom Select.
                'instructors' => User::whereIn('uuid', $instructorUuids)->get(['id', 'uuid', 'name']),
            ],
        ]);
    }

    /**
     * Type-ahead source for the gallery's instructor picker. Only people who
     * actually have a course are offered.
     */
    public function instructors(Request $request): JsonResponse
    {
        return response()->json(
            User::whereIn('id', Course::select('created_by'))
                ->when($request->input('q'), fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
                ->orderBy('name')
                ->limit(20)
                ->get(['id', 'uuid', 'name'])
                // The picker's values land in the query string, so they are uuids.
                ->map(fn (User $user) => ['id' => $user->uuid, 'name' => $user->name])
        );
    }

    /**
     * Renders one of the static pages above.
     */
    public function page(string $page)
    {
        abort_if(! isset(self::PAGES[$page]), 404);

        return view('public.' . self::PAGES[$page]);
    }
}
