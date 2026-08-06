<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display a listing of the study notes.
     */
    public function index(Request $request)
    {
        // Dummy data for presentation
        $notes = collect([
            [
                'id' => 1,
                'title' => 'Newton\'s Laws Summary',
                'course' => 'Physics 101',
                'course_tag_color' => 'bg-primary/10 text-primary',
                'excerpt' => 'A quick overview of the three laws of motion, including formulas and real-world examples discussed in week 2 lecture.',
                'date' => 'Oct 12, 2023',
                'attachments' => 2
            ],
            [
                'id' => 2,
                'title' => 'Cellular Respiration',
                'course' => 'Biology Fundamentals',
                'course_tag_color' => 'bg-tertiary/10 text-tertiary',
                'excerpt' => 'Detailed diagram breakdown of glycolysis, Krebs cycle, and electron transport chain. Includes PDF textbook chapter.',
                'date' => 'Oct 15, 2023',
                'attachments' => 1
            ],
        ]);

        return view('notes.index', compact('notes'));
    }

    /**
     * Store a newly created note in storage.
     */
    public function store(Request $request)
    {
        // Placeholder for storing notes
        return redirect()->route('notes')->with('success', 'Note added successfully.');
    }
}
