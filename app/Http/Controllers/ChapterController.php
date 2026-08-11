<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChapterController extends Controller
{
    /**
     * Course titles keyed by id — mirrors CourseController's mock data so
     * chapters can be scoped to a course without a real courses table yet.
     */
    private function courses(): array
    {
        return [
            1 => 'Advanced React Patterns',
            2 => 'Digital Marketing 101',
            3 => 'UI/UX Principles',
        ];
    }

    /**
     * Chapters mock data, grouped by course_id.
     */
    private function allChapters(): \Illuminate\Support\Collection
    {
        return collect([
            ['id' => 1, 'course_id' => 1, 'num' => 1, 'title' => 'Introduction to Hooks', 'desc' => 'Understanding useState, useEffect, and the rules of hooks in modern React development.', 'status' => 'Published'],
            ['id' => 2, 'course_id' => 1, 'num' => 2, 'title' => 'Context API Deep Dive', 'desc' => 'Managing global state without prop drilling using React Context and custom providers.', 'status' => 'Published'],
            ['id' => 3, 'course_id' => 1, 'num' => 3, 'title' => 'Performance Optimization', 'desc' => 'Memoization, code splitting, and profiling techniques for production React apps.', 'status' => 'Draft'],
            ['id' => 4, 'course_id' => 2, 'num' => 1, 'title' => 'SEO Fundamentals', 'desc' => 'On-page and off-page optimization strategies to improve organic search visibility.', 'status' => 'Published'],
            ['id' => 5, 'course_id' => 2, 'num' => 2, 'title' => 'Social Media Strategy', 'desc' => 'Building and executing a content calendar across major social platforms.', 'status' => 'Published'],
            ['id' => 6, 'course_id' => 3, 'num' => 1, 'title' => 'Wireframing Fundamentals', 'desc' => 'Translating user needs into low-fidelity structural layouts before applying visual design.', 'status' => 'Published'],
            ['id' => 7, 'course_id' => 3, 'num' => 2, 'title' => 'Prototyping & Testing', 'desc' => 'Building interactive prototypes and running usability tests to validate design decisions.', 'status' => 'Draft'],
        ]);
    }

    /**
     * Display the chapters belonging to a single course.
     */
    public function index(Request $request, int $course)
    {
        $courses = $this->courses();
        abort_if(! isset($courses[$course]), 404);

        $chapters = $this->allChapters()->where('course_id', $course)->values();

        // Filter by Title
        if ($title = $request->input('title')) {
            $chapters = $chapters->filter(fn ($c) => stripos($c['title'], $title) !== false)->values();
        }

        // Filter by Status
        if ($status = $request->input('status')) {
            $chapters = $chapters->filter(fn ($c) => $c['status'] === $status)->values();
        }

        $allForCourse = $this->allChapters()->where('course_id', $course);

        return view('chapters.index', [
            'courseId' => $course,
            'courseTitle' => $courses[$course],
            'chapters' => $chapters,
            'totalCount' => $allForCourse->count(),
            'draftsCount' => $allForCourse->where('status', 'Draft')->count(),
            'filters' => [
                'title' => $title ?? '',
                'status' => $status ?? '',
            ],
        ]);
    }

    /**
     * Display the management dashboard hub for a single chapter.
     */
    public function dashboard(int $course, int $chapter)
    {
        $courses = $this->courses();
        abort_if(! isset($courses[$course]), 404);

        $chapterData = $this->allChapters()->firstWhere('id', $chapter);
        abort_if(! $chapterData || $chapterData['course_id'] !== $course, 404);

        return view('chapters.dashboard', [
            'courseId' => $course,
            'courseTitle' => $courses[$course],
            'chapter' => $chapterData,
        ]);
    }
}
