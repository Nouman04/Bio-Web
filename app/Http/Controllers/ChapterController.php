<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChapterController extends Controller
{
    /**
     * Display a listing of chapters.
     */
    public function index(Request $request)
    {
        // Simple collection matching chapters list.html
        $chapters = collect([
            ['id' => 1, 'num' => 1, 'title' => 'Introduction to Variables', 'desc' => 'Understanding the basics of memory allocation and data types in modern programming languages.', 'course' => 'Computer Science 101', 'status' => 'Published', 'badgeColor' => 'bg-primary-fixed/30 text-on-primary-fixed'],
            ['id' => 2, 'num' => 2, 'title' => 'Control Structures & Loops', 'desc' => 'Mastering if/else statements, for loops, and while loops to control program flow effectively.', 'course' => 'Computer Science 101', 'status' => 'Published', 'badgeColor' => 'bg-primary-fixed/30 text-on-primary-fixed'],
            ['id' => 3, 'num' => 1, 'title' => 'Cellular Structures', 'desc' => 'A deep dive into eukaryotic and prokaryotic cells, organelles, and their specific functions.', 'course' => 'Advanced Biology', 'status' => 'Draft', 'badgeColor' => 'bg-secondary-fixed/50 text-on-secondary-fixed'],
            ['id' => 4, 'num' => 3, 'title' => 'Wireframing Fundamentals', 'desc' => 'Translating user needs into low-fidelity structural layouts before applying visual design.', 'course' => 'UX/UI Design Principles', 'status' => 'Published', 'badgeColor' => 'bg-tertiary-fixed/30 text-on-tertiary-fixed']
        ]);

        // Filter by Course
        if ($course = $request->input('course')) {
            $chapters = $chapters->filter(function ($c) use ($course) {
                return $c['course'] === $course;
            });
        }

        // Calculations
        $totalCount = $chapters->count();
        $draftsCount = $chapters->where('status', 'Draft')->count();

        return view('chapters.index', [
            'chapters' => $chapters,
            'totalCount' => $totalCount,
            'draftsCount' => $draftsCount,
            'filters' => [
                'course' => $course,
            ]
        ]);
    }
}
