<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Flashcard;
use App\Models\QuestionBank;
use App\Models\Quiz;
use App\Models\Worksheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * What the picker can be building for, keyed by the `exclude_type` the UI
     * sends. Mirrors the `assessments` morph.
     */
    private const ASSESSMENTABLES = [
        'flashcard' => Flashcard::class,
        'quiz' => Quiz::class,
        'worksheet' => Worksheet::class,
    ];

    /**
     * Type-ahead source for the question widget's Tom Select field.
     * Returns the questions matching `q`, newest first.
     */
    public function search(Request $request): JsonResponse
    {
        $questions = QuestionBank::query()
            ->when($request->input('q'), fn ($query, $term) => $query->where('question', 'like', "%{$term}%"))
            // Hide questions already attached to the assessment being built, so
            // the picker never offers a duplicate.
            ->when(
                $request->input('exclude_type') && $request->input('exclude_id'),
                function ($query) use ($request) {
                    $class = self::ASSESSMENTABLES[$request->input('exclude_type')] ?? null;
                    if (! $class) {
                        return;
                    }

                    $query->whereNotIn('id', Assessment::query()
                        ->where('assessmentable_type', $class)
                        ->where('assessmentable_id', $request->input('exclude_id'))
                        ->pluck('question_id'));
                }
            )
            ->latest('id')
            ->limit(20)
            ->get(['id', 'question', 'difficulty_level']);

        return response()->json(
            $questions->map(fn (QuestionBank $question) => [
                'id' => $question->id,
                'text' => $question->question,
                'meta' => $question->difficulty_level,
            ])
        );
    }

    /**
     * Display the question bank listing.
     */
    public function index(Request $request)
    {
        $questions = collect([
            ['id' => 1, 'text' => 'What is the time complexity of searching for an element in a balanced Binary Search Tree (BST)?', 'type' => 'MCQ', 'chapter' => 'Trees', 'topic' => 'BST Basics', 'difficulty' => 'Medium'],
            ['id' => 2, 'text' => 'Explain the concept of polymorphic dispatch in object-oriented programming with a real-world example.', 'type' => 'Theory', 'chapter' => 'OOP Concepts', 'topic' => 'Polymorphism', 'difficulty' => 'Hard'],
            ['id' => 3, 'text' => 'Which of the following data structures operates on a Last-In, First-Out (LIFO) principle?', 'type' => 'MCQ', 'chapter' => 'Linear Data Structs', 'topic' => 'Stacks', 'difficulty' => 'Easy'],
            ['id' => 4, 'text' => 'Describe the difference between TCP and UDP protocols and give a use case for each.', 'type' => 'Theory', 'chapter' => 'Networking', 'topic' => 'Protocols', 'difficulty' => 'Medium'],
            ['id' => 5, 'text' => 'What is the output of the following Python code snippet involving list comprehensions?', 'type' => 'MCQ', 'chapter' => 'Python', 'topic' => 'List Comprehensions', 'difficulty' => 'Easy'],
        ]);

        // Apply filters
        if ($type = $request->input('type')) {
            $questions = $questions->filter(fn($q) => $q['type'] === $type);
        }
        if ($difficulty = $request->input('difficulty')) {
            $questions = $questions->filter(fn($q) => $q['difficulty'] === $difficulty);
        }

        return view('questions.index', [
            'questions' => $questions,
            'filters' => [
                'type' => $request->input('type'),
                'difficulty' => $request->input('difficulty'),
            ],
        ]);
    }

    /**
     * Show the bulk question creation form.
     */
    public function create()
    {
        $chapters = collect([
            ['id' => 1, 'name' => 'Chapter 1: Cell Biology'],
            ['id' => 2, 'name' => 'Chapter 2: Genetics'],
            ['id' => 3, 'name' => 'Chapter 3: Evolution'],
        ]);

        return view('questions.create', compact('chapters'));
    }

    /**
     * Store newly created questions (bulk).
     */
    public function store(Request $request)
    {
        // Placeholder: validate & persist questions
        return redirect()->route('questions')->with('success', 'Questions added successfully!');
    }
}
