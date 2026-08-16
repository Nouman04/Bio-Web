<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GuideController extends Controller
{
    /**
     * Display a listing of the guides.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        // Dummy data for presentation
        $guides = collect([
            [
                'id' => 1,
                'title' => 'Mastering Differential Equations',
                'description' => 'A comprehensive guide covering first and second order differential equations, Laplace transforms, and series solutions with practical examples.',
                'course' => 'Advanced Mathematics',
                'author' => 'Dr. Sarah Chen',
                'status' => 'Published',
                'status_color' => 'bg-tertiary-container/20 text-tertiary font-medium',
                'views' => 1245,
                'rating' => 4.8,
                'last_updated' => 'Oct 24, 2023',
                'chapter' => '1',
                'topic' => 'math',
                'type' => 'theory'
            ],
            [
                'id' => 2,
                'title' => 'Introduction to React Hooks',
                'description' => 'Learn how to use useState, useEffect, useContext, and create custom hooks to build robust functional components in React.',
                'course' => 'Frontend Web Development',
                'author' => 'Alex Rivera',
                'status' => 'Draft',
                'status_color' => 'bg-secondary-container/20 text-secondary font-medium',
                'views' => 0,
                'rating' => 0.0,
                'last_updated' => 'Oct 25, 2023',
                'chapter' => '2',
                'topic' => 'sci',
                'type' => 'practical'
            ],
        ]);

        // Filter by Chapter
        if ($chapter = $request->input('chapter')) {
            $guides = $guides->filter(function ($g) use ($chapter) {
                return $g['chapter'] === $chapter;
            });
        }

        // Filter by Topic
        if ($topic = $request->input('topic')) {
            $guides = $guides->filter(function ($g) use ($topic) {
                return $g['topic'] === $topic;
            });
        }

        // Filter by Type
        if ($type = $request->input('type')) {
            $guides = $guides->filter(function ($g) use ($type) {
                return $g['type'] === $type;
            });
        }

        return view('guides.index', compact('guides', 'filters'));
    }

    /**
     * Show the form for creating a new guide.
     */
    public function create()
    {
        return view('guides.create');
    }

    /**
     * Store a newly created guide in storage.
     */
    public function store(Request $request)
    {
        // Fall back to the title when the slug field arrives empty (e.g. JS disabled).
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('title')),
        ]);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('guides', 'slug')],
        ]);

        // Placeholder for storing guides
        return redirect()->route('guides')->with('success', 'Guide created successfully.');
    }

    /**
     * Show the form for editing the specified guide.
     */
    public function edit($id)
    {
        // In a real app, you would fetch the guide by $id
        $guide = [
            'id' => $id,
            'title' => 'Mastering Differential Equations',
            'slug' => 'mastering-differential-equations',
            'chapter' => 'ch1',
            'topic' => 't1',
            'type' => 'theory',
            'content' => 'A comprehensive guide covering first and second order differential equations, Laplace transforms, and series solutions with practical examples.',
        ];

        return view('guides.edit', compact('guide'));
    }

    /**
     * Update the specified guide in storage.
     */
    public function update(Request $request, $id)
    {
        // Fall back to the title when the slug field arrives empty (e.g. JS disabled).
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('title')),
        ]);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('guides', 'slug')->ignore($id)],
        ]);

        // Placeholder for updating guides
        return redirect()->route('guides')->with('success', 'Guide updated successfully.');
    }
}
