<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FlashcardController extends Controller
{
    /**
     * Display a listing of the flashcard sets.
     */
    public function index(Request $request)
    {
        // Dummy data for presentation
        $flashcards = collect([
            ['id' => 1, 'title' => 'Physics 101 Basics', 'chapter' => 'Physics 101', 'topic' => 'Kinematics', 'cards_count' => 45, 'updated_at' => 'Oct 24, 2023'],
            ['id' => 2, 'title' => 'Organic Chemistry Nomenclature', 'chapter' => 'Organic Chemistry', 'topic' => 'Alkanes', 'cards_count' => 120, 'updated_at' => 'Oct 22, 2023'],
            ['id' => 3, 'title' => 'World History II: Key Figures', 'chapter' => 'World History II', 'topic' => 'Cold War', 'cards_count' => 65, 'updated_at' => 'Oct 20, 2023'],
            ['id' => 4, 'title' => 'Intro to Psychology: Encoding', 'chapter' => 'Intro to Psychology', 'topic' => 'Memory', 'cards_count' => 30, 'updated_at' => 'Oct 18, 2023'],
        ]);

        return view('flashcards.index', compact('flashcards'));
    }

    /**
     * Show the form for creating a new flashcard set.
     */
    public function create()
    {
        return view('flashcards.create');
    }

    /**
     * Store a newly created flashcard set in storage.
     */
    public function store(Request $request)
    {
        // Placeholder for storing flashcards
        return redirect()->route('flashcards')->with('success', 'Flashcard set created successfully.');
    }
}
