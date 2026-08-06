<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuideController extends Controller
{
    /**
     * Display a listing of the guides.
     */
    public function index(Request $request)
    {
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
                'last_updated' => 'Oct 24, 2023'
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
                'last_updated' => 'Oct 25, 2023'
            ],
        ]);

        return view('guides.index', compact('guides'));
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
        // Placeholder for storing guides
        return redirect()->route('guides')->with('success', 'Guide created successfully.');
    }
}
