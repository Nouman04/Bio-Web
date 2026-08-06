<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Display a listing of topics.
     */
    public function index(Request $request)
    {
        // Simple collection matching topics list.html
        $topics = collect([
            ['id' => 1, 'chapter' => 'Ch 1. Fundamentals', 'name' => 'Introduction to Core Concepts', 'questions' => 42],
            ['id' => 2, 'chapter' => 'Ch 1. Fundamentals', 'name' => 'Historical Context & Evolution', 'questions' => 18],
            ['id' => 3, 'chapter' => 'Ch 2. Advanced', 'name' => 'Predictive Modeling Basics', 'questions' => 56],
            ['id' => 4, 'chapter' => 'Ch 2. Advanced', 'name' => 'Data Normalization Strategies', 'questions' => 31]
        ]);

        // Filter by Title
        if ($title = $request->input('title')) {
            $topics = $topics->filter(function ($t) use ($title) {
                return stripos($t['name'], $title) !== false;
            });
        }

        // Filter by Chapter
        if ($chapter = $request->input('chapter')) {
            $topics = $topics->filter(function ($t) use ($chapter) {
                return $t['chapter'] === $chapter;
            });
        }

        return view('topics.index', [
            'topics' => $topics,
            'filters' => [
                'title' => $title ?? '',
                'chapter' => $chapter ?? '',
            ]
        ]);
    }

    /**
     * Display the question assignment page for a specific topic.
     */
    public function assign($id)
    {
        // Lookup topic details or default mock values
        $topicName = ($id == 3) ? 'Predictive Modeling Basics' : (($id == 4) ? 'Data Normalization Strategies' : 'Introduction to Core Concepts');
        
        $questions = collect([
            ['id' => 1, 'text' => 'Explain the fundamental difference between definite and indefinite integrals with practical examples.', 'type' => 'Theory', 'difficulty' => 'Hard'],
            ['id' => 2, 'text' => 'Which of the following describes the area under a curve?', 'type' => 'MCQ', 'difficulty' => 'Easy'],
            ['id' => 3, 'text' => 'Evaluate the integral of f(x) = 3x^2 from x=0 to x=2.', 'type' => 'MCQ', 'difficulty' => 'Medium'],
            ['id' => 4, 'text' => 'State the Fundamental Theorem of Calculus.', 'type' => 'Theory', 'difficulty' => 'Medium']
        ]);

        return view('topics.assign', [
            'topicId' => $id,
            'topicName' => $topicName,
            'questions' => $questions
        ]);
    }
}
