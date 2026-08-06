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
        $filters = $request->all();

        // Dummy data for presentation
        $notes = collect([
            [
                'id' => 1,
                'title' => 'Newton\'s Laws Summary',
                'course' => 'Physics 101',
                'course_tag_color' => 'bg-primary/10 text-primary',
                'excerpt' => 'A quick overview of the three laws of motion, including formulas and real-world examples discussed in week 2 lecture.',
                'date' => 'Oct 12, 2023',
                'attachments' => 2,
                'chapter' => 'Ch1',
                'topic' => 'Basics'
            ],
            [
                'id' => 2,
                'title' => 'Cellular Respiration',
                'course' => 'Biology Fundamentals',
                'course_tag_color' => 'bg-tertiary/10 text-tertiary',
                'excerpt' => 'Detailed diagram breakdown of glycolysis, Krebs cycle, and electron transport chain. Includes PDF textbook chapter.',
                'date' => 'Oct 15, 2023',
                'attachments' => 1,
                'chapter' => 'Ch2',
                'topic' => 'Advanced'
            ],
        ]);

        // Filter by Chapter
        if ($chapter = $request->input('chapter')) {
            $notes = $notes->filter(function ($n) use ($chapter) {
                return $n['chapter'] === $chapter;
            });
        }

        // Filter by Topic
        if ($topic = $request->input('topic')) {
            $notes = $notes->filter(function ($n) use ($topic) {
                return $n['topic'] === $topic;
            });
        }

        // Filter by Search
        if ($search = $request->input('search')) {
            $notes = $notes->filter(function ($n) use ($search) {
                return stripos($n['title'], $search) !== false;
            });
        }

        return view('notes.index', compact('notes', 'filters'));
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
