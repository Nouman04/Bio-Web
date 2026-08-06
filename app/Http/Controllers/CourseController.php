<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     */
    public function index(Request $request)
    {
        // Simple collection matching courses list.html
        $courses = collect([
            ['id' => 1, 'title' => 'Advanced React Patterns', 'category' => 'Development', 'instructor' => 'Sarah Jenkins', 'chapters' => 12, 'status' => 'Published', 'dateMsg' => 'Last updated 2 days ago', 'icon' => 'code'],
            ['id' => 2, 'title' => 'Digital Marketing 101', 'category' => 'Business', 'instructor' => 'Marcus Reed', 'chapters' => 8, 'status' => 'Published', 'dateMsg' => 'Last updated 1 week ago', 'icon' => 'trending_up'],
            ['id' => 3, 'title' => 'UI/UX Principles', 'category' => 'Design', 'instructor' => 'Elena Costa', 'chapters' => 15, 'status' => 'Draft', 'dateMsg' => 'Draft - Not published', 'icon' => 'brush']
        ]);

        // Filter by Search Query
        if ($search = $request->input('search')) {
            $courses = $courses->filter(function ($c) use ($search) {
                return stripos($c['title'], $search) !== false || stripos($c['instructor'], $search) !== false;
            });
        }

        // Filter by Category
        if ($category = $request->input('category')) {
            $courses = $courses->filter(function ($c) use ($category) {
                return $c['category'] === $category;
            });
        }

        // Filter by Status
        if ($status = $request->input('status')) {
            $courses = $courses->filter(function ($c) use ($status) {
                return $c['status'] === $status;
            });
        }

        return view('courses.index', [
            'courses' => $courses,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'status' => $status,
            ]
        ]);
    }
}
