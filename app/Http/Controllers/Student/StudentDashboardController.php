<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class StudentDashboardController extends Controller
{
    /**
     * Show the student dashboard.
     */
    public function index()
    {
        return view('student.dashboard');
    }
}
