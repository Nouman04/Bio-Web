<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\QuizUserAttempt;
use App\Models\SavedContent;
use App\Models\User;
use App\Models\UserModuleProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * What the student dashboard shows.
 *
 * Everything here is read from what the student has actually done — the module
 * progress rows, their attempts and their subscriptions. Nothing is invented.
 */
class StudentDashboardService
{
    /**
     * Where a student's progress row points, so the dashboard can link back to
     * the thing they were last looking at.
     */
    private const ROUTES = [
        'video' => ['student.chapters.videos.show', 'videoId'],
        'note' => ['student.chapters.notes.show', 'noteId'],
        'diagram' => ['student.chapters.diagrams.show', 'diagramId'],
        'guide' => ['student.chapters.guides.show', 'guideId'],
        'summary' => ['student.chapters.summaries.show', 'summaryId'],
        'quiz' => ['student.chapters.quizzes.show', 'quizId'],
    ];

    private const LABELS = [
        'video' => 'Video lesson',
        'note' => 'Study note',
        'diagram' => 'Diagram',
        'guide' => 'Guide',
        'summary' => 'Summary',
        'quiz' => 'Quiz',
    ];

    private const ICONS = [
        'video' => 'play_circle',
        'note' => 'description',
        'diagram' => 'schema',
        'guide' => 'menu_book',
        'summary' => 'summarize',
        'quiz' => 'quiz',
    ];

    public function __construct(private readonly ProgressService $progress)
    {
    }

    /**
     * The courses this student subscribes to, each with how far through it they
     * are, best first.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function courses(User $student): Collection
    {
        $uuids = $student->subscriptions
            ->filter(fn ($subscription) => $subscription->valid())
            ->map(fn ($subscription) => Str::after($subscription->type, 'course_'))
            ->filter()
            ->unique();

        if ($uuids->isEmpty()) {
            return collect();
        }

        $courses = Course::whereIn('uuid', $uuids)
            ->withCount('chapters')
            ->with('category:id,title')
            ->orderBy('title')
            ->get();

        // One grouped query for the set rather than one per course.
        $progress = $this->progress->courseProgressFor($student, $courses);

        return $courses->map(fn (Course $course) => [
            'course' => $course,
            'progress' => $progress[$course->id] ?? ['progress' => 0.0, 'completed_weight' => 0, 'total_weight' => 0],
        ])->sortByDesc(fn ($row) => $row['progress']['progress'])->values();
    }

    /**
     * Everything across every subscribed course, as one figure.
     *
     * @param  Collection<int, array<string, mixed>>  $courses
     * @return array{progress:float, completed_weight:int, total_weight:int}
     */
    public function overall(Collection $courses): array
    {
        $completed = (int) $courses->sum(fn ($row) => $row['progress']['completed_weight']);
        $total = (int) $courses->sum(fn ($row) => $row['progress']['total_weight']);

        return [
            'progress' => $total > 0 ? round($completed / $total * 100, 1) : 0.0,
            'completed_weight' => $completed,
            'total_weight' => $total,
        ];
    }

    /**
     * The last thing the student opened, so the dashboard can offer to carry
     * on from there.
     *
     * Preference goes to something unfinished; a finished module is still worth
     * offering as a starting point when there is nothing else.
     *
     * @return array<string, mixed>|null
     */
    public function resume(User $student): ?array
    {
        // Content that has since been deleted leaves its progress row behind,
        // so the newest row is not always something there is still a page for.
        // Walk back until one resolves rather than offering a dead link.
        $rows = UserModuleProgress::query()
            ->where('user_id', $student->id)
            ->orderByRaw('is_completed asc')
            ->latest('updated_at')
            ->limit(10)
            ->get();

        foreach ($rows as $row) {
            $module = CourseModule::active()->with('chapter.course')->find($row->course_module_id);
            $chapter = $module?->chapter;
            $course = $chapter?->course;
            $record = $module?->moduleable;

            if ($module && $chapter && $course && $record) {
                return $this->resumeCard($student, $row, $module, $chapter, $course, $record);
            }
        }

        return null;
    }

    /**
     * The resume card for one resolved progress row.
     *
     * @return array<string, mixed>
     */
    private function resumeCard($student, $row, $module, $chapter, $course, $record): array
    {
        return [
            'type' => $module->type,
            'label' => self::LABELS[$module->type] ?? 'Lesson',
            'icon' => self::ICONS[$module->type] ?? 'play_circle',
            'title' => $record->title ?? self::LABELS[$module->type] ?? 'Lesson',
            'chapter' => $chapter->title,
            'course' => $course->title,
            'course_progress' => $this->progress->courseProgress($student, $course),
            'percent' => (int) $row->progress,
            'completed' => (bool) $row->is_completed,
            'touched' => $row->updated_at,
            'url' => $this->urlFor($module, $chapter->uuid, $course->uuid, $record?->uuid),
        ];
    }

    /**
     * The things asking for the student's attention, newest concern first.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function attention(User $student): Collection
    {
        $items = collect();

        // A paper still open: the clock may well be running.
        QuizUserAttempt::where('user_id', $student->id)
            ->whereStatus('in_progress')
            ->with('quiz')
            ->latest('id')
            ->take(3)
            ->get()
            ->each(function (QuizUserAttempt $attempt) use ($items) {
                $items->push([
                    'icon' => 'timer',
                    'tone' => 'error',
                    'title' => $attempt->quiz?->title ?? 'Quiz',
                    // An attempt whose clock has already run out is not still
                    // open, whatever the status column says — saying "4 hours
                    // ago to hand in" would be nonsense.
                    'note' => match (true) {
                        $attempt->expires_at === null => 'Still open — no time limit',
                        $attempt->hasExpired() => 'Time ran out ' . $attempt->expires_at->diffForHumans(),
                        default => 'Still open — ' . gmdate($attempt->secondsRemaining() >= 3600 ? 'Gh im' : 'im ss', $attempt->secondsRemaining()) . ' left',
                    },
                    'url' => route('student.quizzes'),
                ]);
            });

        // Handed in and waiting on the instructor.
        QuizUserAttempt::where('user_id', $student->id)
            ->whereStatus('pending_review')
            ->with('quiz')
            ->latest('submitted_at')
            ->take(3)
            ->get()
            ->each(function (QuizUserAttempt $attempt) use ($items) {
                $items->push([
                    'icon' => 'hourglass_top',
                    'tone' => 'secondary',
                    'title' => $attempt->quiz?->title ?? 'Quiz',
                    'note' => 'With your instructor for marking',
                    'url' => route('student.quizzes.report', $attempt->uuid),
                ]);
            });

        // Marked since they last looked.
        QuizUserAttempt::where('user_id', $student->id)
            ->whereStatus('graded')
            ->with('quiz')
            ->latest('graded_at')
            ->take(3)
            ->get()
            ->each(function (QuizUserAttempt $attempt) use ($items) {
                $items->push([
                    'icon' => $attempt->passed ? 'check_circle' : 'cancel',
                    'tone' => $attempt->passed ? 'tertiary' : 'error',
                    'title' => $attempt->quiz?->title ?? 'Quiz',
                    'note' => sprintf(
                        'Marked %s — %s/%s',
                        $attempt->graded_at?->diffForHumans() ?? 'recently',
                        rtrim(rtrim((string) $attempt->earned_marks, '0'), '.'),
                        rtrim(rtrim((string) $attempt->total_marks, '0'), '.')
                    ),
                    'url' => route('student.quizzes.report', $attempt->uuid),
                ]);
            });

        return $items->take(6);
    }

    /**
     * The numbers along the top.
     *
     * @param  Collection<int, array<string, mixed>>  $courses
     * @return array<string, int>
     */
    public function stats(User $student, Collection $courses): array
    {
        $attempts = QuizUserAttempt::where('user_id', $student->id)->get();

        return [
            'courses' => $courses->count(),
            'chapters' => (int) $courses->sum(fn ($row) => $row['course']->chapters_count),
            'quizzes_passed' => $attempts->where('passed', true)->unique('quiz_id')->count(),
            'quizzes_sat' => $attempts->whereIn('status', ['submitted', 'graded', 'pending_review'])->count(),
            'saved' => SavedContent::where('user_id', $student->id)->count(),
        ];
    }

    /**
     * Where a module lives in the student portal.
     */
    private function urlFor(CourseModule $module, string $chapterUuid, string $courseUuid, ?string $recordUuid): string
    {
        [$route, $key] = self::ROUTES[$module->type] ?? [null, null];

        if (! $route || ! $recordUuid) {
            // The record is gone; the chapter is still somewhere to land.
            return route('student.chapters.show', ['courseId' => $courseUuid, 'chapterId' => $chapterUuid]);
        }

        return route($route, [
            'courseId' => $courseUuid,
            'chapterId' => $chapterUuid,
            $key => $recordUuid,
        ]);
    }
}
