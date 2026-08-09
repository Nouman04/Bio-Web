<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentCatalogController extends Controller
{
    /**
     * Show the full course catalog available for enrollment.
     */
    public function index(Request $request)
    {
        // TODO: Replace with real DB query
        $catalog = collect([
            ['id' => 1, 'title' => 'Machine Learning Fundamentals', 'category' => 'Data Science',    'duration' => '8 weeks',  'level' => 'Intermediate'],
            ['id' => 2, 'title' => 'Cloud Computing Essentials',    'category' => 'DevOps',           'duration' => '6 weeks',  'level' => 'Beginner'],
            ['id' => 3, 'title' => 'Cybersecurity Basics',          'category' => 'Security',         'duration' => '4 weeks',  'level' => 'Beginner'],
            ['id' => 4, 'title' => 'React Advanced Patterns',       'category' => 'Frontend',         'duration' => '5 weeks',  'level' => 'Advanced'],
            ['id' => 5, 'title' => 'Business Analytics',            'category' => 'Business',         'duration' => '6 weeks',  'level' => 'Intermediate'],
            ['id' => 6, 'title' => 'UX Research Methods',           'category' => 'Design',           'duration' => '3 weeks',  'level' => 'Beginner'],
        ]);

        if ($search = $request->input('search')) {
            $catalog = $catalog->filter(fn($c) => stripos($c['title'], $search) !== false
                || stripos($c['category'], $search) !== false);
        }

        return view('student.catalog.index', compact('catalog'));
    }
}
