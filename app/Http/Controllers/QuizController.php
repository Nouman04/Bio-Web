<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Display a listing of the quizzes.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $quizzes = collect([
            [
                'id' => 1,
                'title' => 'Biology 101 - Midterm Quiz',
                'meta' => '45 Questions • 60 mins • Passing: 70%',
                'chapter' => 'Chapter 1 to 5',
                'date_created' => 'Oct 24, 2023',
                'status' => 'Published',
                'status_color' => 'bg-tertiary-container/20 text-tertiary',
                'responses' => 124
            ],
            [
                'id' => 2,
                'title' => 'Genetics Basics Assessment',
                'meta' => '20 Questions • 30 mins • Passing: 60%',
                'chapter' => 'Chapter 3: Genetics',
                'date_created' => 'Oct 20, 2023',
                'status' => 'Draft',
                'status_color' => 'bg-surface-variant text-on-surface-variant',
                'responses' => 0
            ],
            [
                'id' => 3,
                'title' => 'Cell Structure Pop Quiz',
                'meta' => '10 Questions • 15 mins • Passing: 50%',
                'chapter' => 'Chapter 2: Cell Structure',
                'date_created' => 'Oct 18, 2023',
                'status' => 'Closed',
                'status_color' => 'bg-error/10 text-error',
                'responses' => 89
            ]
        ]);

        // Filter by Status
        if ($status = $request->input('status')) {
            $quizzes = $quizzes->filter(function ($q) use ($status) {
                return $q['status'] === $status;
            });
        }

        return view('quizzes.index', compact('quizzes', 'filters'));
    }

    /**
     * Show the form for creating a new quiz.
     */
    public function create()
    {
        return view('quizzes.create');
    }

    /**
     * Store a newly created quiz in storage.
     */
    public function store(Request $request)
    {
        // Placeholder for storing quiz
        return redirect()->route('quizzes')->with('success', 'Quiz created successfully.');
    }

    /**
     * Show the form for editing the specified quiz.
     */
    public function edit($id)
    {
        // In a real app, you would fetch the quiz by $id
        return view('quizzes.edit', compact('id'));
    }

    /**
     * Update the specified quiz in storage.
     */
    public function update(Request $request, $id)
    {
        // Placeholder for updating quiz
        return redirect()->route('quizzes')->with('success', 'Quiz updated successfully.');
    }
}
