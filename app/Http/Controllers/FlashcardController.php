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
        $filters = $request->all();

        // Dummy data for presentation
        $flashcards = collect([
            ['id' => 1, 'title' => 'Physics 101 Basics', 'chapter' => 'Physics 101', 'topic' => 'Kinematics', 'cards_count' => 45, 'updated_at' => 'Oct 24, 2023'],
            ['id' => 2, 'title' => 'Organic Chemistry Nomenclature', 'chapter' => 'Organic Chemistry', 'topic' => 'Alkanes', 'cards_count' => 120, 'updated_at' => 'Oct 22, 2023'],
            ['id' => 3, 'title' => 'World History II: Key Figures', 'chapter' => 'World History II', 'topic' => 'Cold War', 'cards_count' => 65, 'updated_at' => 'Oct 20, 2023'],
            ['id' => 4, 'title' => 'Intro to Psychology: Encoding', 'chapter' => 'Intro to Psychology', 'topic' => 'Memory', 'cards_count' => 30, 'updated_at' => 'Oct 18, 2023'],
        ]);

        // Filter by Title
        if ($title = $request->input('title')) {
            $flashcards = $flashcards->filter(function ($f) use ($title) {
                return stripos($f['title'], $title) !== false;
            });
        }

        return view('flashcards.index', compact('flashcards', 'filters'));
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

    /**
     * Show the form for editing the specified flashcard set.
     */
    public function edit($id)
    {
        // In a real app, you would fetch the flashcard set by $id
        $flashcard = [
            'id' => $id,
            'title' => 'Biology 101: Cellular Structures',
            'chapter' => 'Chapter 3: The Cell',
            'topic' => 'Cell Membrane',
            'cards' => [
                [
                    'type' => 'Multiple Choice',
                    'question' => 'What is the primary function of the mitochondria in a eukaryotic cell?',
                    'answer' => 'It generates most of the cell\'s ATP through oxidative phosphorylation.',
                ],
                [
                    'type' => 'True/False',
                    'question' => 'Plant cells contain both chloroplasts and mitochondria. (True/False)',
                    'answer' => 'True — plant cells need mitochondria to respire even though they also photosynthesise.',
                ],
            ],
        ];

        return view('flashcards.edit', compact('flashcard'));
    }

    /**
     * Update the specified flashcard set in storage.
     */
    public function update(Request $request, $id)
    {
        // Placeholder for updating flashcards
        return redirect()->route('flashcards')->with('success', 'Flashcard set updated successfully.');
    }
}
