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
        $filters = $request->all();

        // Dummy data for presentation
        $summaries = collect([
            [
                'id' => 1,
                'title' => 'Newtonian Mechanics Summary',
                'chapter' => 'Physics 101',
                'chapter_key' => 'Ch1',
                'topic' => 'Mechanics',
                'topic_key' => 'Basics',
                'topic_color' => 'bg-primary/10 text-primary',
                'course' => 'CS101'
            ],
            [
                'id' => 2,
                'title' => 'Integration Techniques Summary',
                'chapter' => 'Calculus Fundamentals',
                'chapter_key' => 'Ch2',
                'topic' => 'Integration',
                'topic_key' => 'Advanced',
                'topic_color' => 'bg-tertiary/10 text-tertiary',
                'course' => 'PHYS101'
            ],
        ]);

        // Filter by Course
        if ($course = $request->input('course')) {
            $summaries = $summaries->filter(function ($s) use ($course) {
                return $s['course'] === $course;
            });
        }

        // Filter by Title
        if ($title = $request->input('title')) {
            $summaries = $summaries->filter(function ($s) use ($title) {
                return stripos($s['title'], $title) !== false;
            });
        }

        // Filter by Chapter
        if ($chapter = $request->input('chapter')) {
            $summaries = $summaries->filter(function ($s) use ($chapter) {
                return $s['chapter_key'] === $chapter;
            });
        }

        // Filter by Topic
        if ($topic = $request->input('topic')) {
            $summaries = $summaries->filter(function ($s) use ($topic) {
                return $s['topic_key'] === $topic;
            });
        }

        return view('summaries.index', compact('summaries', 'filters'));
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
