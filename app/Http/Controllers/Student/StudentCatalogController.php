<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Services\StripeService;
use Illuminate\Http\Request;

class StudentCatalogController extends Controller
{
    /**
     * How many courses the catalog opens on. Beyond these the reader searches
     * rather than scrolling — the whole library is reachable, just not listed.
     */
    private const LATEST = 10;

    /**
     * How the grid may be ordered, mapped to the column and direction each
     * means. Keeping it to a list stops a query string choosing any column.
     */
    private const SORTS = [
        'newest' => ['id', 'desc'],
        'title' => ['title', 'asc'],
        'chapters' => ['chapters_count', 'desc'],
    ];

    public function __construct(private readonly StripeService $stripe)
    {
    }

    /**
     * What the student has not subscribed to yet.
     *
     * With no search or filter this is the ten newest courses. Searching or
     * picking a category looks across everything they could still subscribe to,
     * paginated.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $category = $request->input('category');
        $sort = array_key_exists((string) $request->input('sort'), self::SORTS)
            ? $request->input('sort')
            : 'newest';

        [$column, $direction] = self::SORTS[$sort];

        // Anything already paid for lives in the hub, not the catalog.
        $query = Course::query()
            ->whereNotIn('uuid', $this->stripe->subscribedCourseUuids($request->user()))
            ->with(['category:id,title', 'creator:id,name', 'plan'])
            ->withCount('chapters')
            ->when($search, fn ($q) => $q->where(fn ($inner) => $inner
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->when($category, fn ($q) => $q->whereHas(
                'category',
                fn ($inner) => $inner->where('title', $category)
            ))
            ->orderBy($column, $direction);

        $browsing = $search === '' && ! $category;

        return view('student.catalog.index', [
            // Browsing shows the newest few; searching pages through the rest.
            'courses' => $browsing
                ? $query->take(self::LATEST)->get()
                : $query->paginate(12)->withQueryString(),
            'browsing' => $browsing,
            'latest' => self::LATEST,
            // Only categories that actually have a course behind them.
            'categories' => Category::whereHas('courses')->orderBy('title')->pluck('title'),
            'search' => $search,
            'category' => $category,
            'sort' => $sort,
        ]);
    }
}
