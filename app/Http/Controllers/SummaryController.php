<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SummaryController extends Controller
{
    /**
     * Display a listing of the summaries.
     */
    public function index(Request $request)
    {
        // Dummy data for presentation
        $summaries = collect([
            [
                'id' => 1,
                'title' => 'Newtonian Mechanics Summary',
                'chapter' => 'Physics 101',
                'topic' => 'Mechanics',
                'topic_color' => 'bg-primary/10 text-primary',
            ],
            [
                'id' => 2,
                'title' => 'Integration Techniques Summary',
                'chapter' => 'Calculus Fundamentals',
                'topic' => 'Integration',
                'topic_color' => 'bg-tertiary/10 text-tertiary',
            ],
        ]);

        return view('summaries.index', compact('summaries'));
    }

    /**
     * Store a newly created summary in storage.
     */
    public function store(Request $request)
    {
        // Placeholder for storing summaries
        return redirect()->route('summaries')->with('success', 'Summary added successfully.');
    }
}
