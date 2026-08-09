<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentCoursesController extends Controller
{
    /**
     * List enrolled courses for the student.
     */
    public function index()
    {
        // TODO: Replace with real DB query (e.g. auth()->user()->enrolledCourses)
        $courses = collect([
            ['id' => 1, 'title' => 'Advanced UI Design',    'progress' => 72, 'instructor' => 'Dr. Amara Diallo', 'thumb' => null],
            ['id' => 2, 'title' => 'Data Structures',       'progress' => 45, 'instructor' => 'Prof. Marcus Chen', 'thumb' => null],
            ['id' => 3, 'title' => 'Web Dev Bootcamp',      'progress' => 90, 'instructor' => 'Ahmed Khalid',      'thumb' => null],
        ]);

        return view('student.courses.index', compact('courses'));
    }

    /**
     * Show a single course detail.
     */
    public function show($id)
    {
        return view('student.courses.show', compact('id'));
    }
}
