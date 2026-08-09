<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class StudentResourcesController extends Controller
{
    /**
     * Show all study resources (guides, flashcards, videos, notes, diagrams).
     */
    public function index()
    {
        // TODO: Replace with real DB queries
        $resources = [
            'videos'     => collect([
                ['id' => 1, 'title' => 'Intro to Arrays',          'duration' => '12:30'],
                ['id' => 2, 'title' => 'CSS Grid Masterclass',     'duration' => '28:15'],
            ]),
            'flashcards' => collect([
                ['id' => 1, 'title' => 'SQL Flashcards',           'cards' => 24],
                ['id' => 2, 'title' => 'Networking Terminology',   'cards' => 40],
            ]),
            'notes'      => collect([
                ['id' => 1, 'title' => 'Lecture Notes – Week 3',   'date' => '2024-03-10'],
                ['id' => 2, 'title' => 'Algorithm Cheat Sheet',    'date' => '2024-04-01'],
            ]),
            'guides'     => collect([
                ['id' => 1, 'title' => 'Git & GitHub Handbook',    'pages' => 18],
                ['id' => 2, 'title' => 'Docker Quick Guide',       'pages' => 12],
            ]),
        ];

        return view('student.resources.index', compact('resources'));
    }
}
