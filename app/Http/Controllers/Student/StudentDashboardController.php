<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentDashboardService;
use Illuminate\Http\Request;

/**
 * The student's landing page: where they left off, how far through each course
 * they are, and what is waiting on them.
 */
class StudentDashboardController extends Controller
{
    public function __construct(private readonly StudentDashboardService $dashboard)
    {
    }

    public function index(Request $request)
    {
        $student = $request->user();
        $courses = $this->dashboard->courses($student);

        return view('student.dashboard', [
            'student' => $student,
            'courses' => $courses,
            'overall' => $this->dashboard->overall($courses),
            'resume' => $this->dashboard->resume($student),
            'attention' => $this->dashboard->attention($student),
            'stats' => $this->dashboard->stats($student, $courses),
        ]);
    }
}
