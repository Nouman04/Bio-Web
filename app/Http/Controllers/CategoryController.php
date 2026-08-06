<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request)
    {
        // Simple collection matching categories list.html
        $categories = collect([
            ['id' => 1, 'title' => 'Web Development', 'parent' => null, 'slug' => 'web-development', 'courses' => 124, 'icon' => 'code'],
            ['id' => 2, 'title' => 'Frontend Basics', 'parent' => 'Web Development', 'slug' => 'frontend-basics', 'courses' => 45, 'icon' => 'subdirectory_arrow_right', 'isChild' => true],
            ['id' => 3, 'title' => 'Design & UX', 'parent' => null, 'slug' => 'design-ux', 'courses' => 89, 'icon' => 'brush'],
            ['id' => 4, 'title' => 'Figma Mastery', 'parent' => 'Design & UX', 'slug' => 'figma-mastery', 'courses' => 22, 'icon' => 'subdirectory_arrow_right', 'isChild' => true],
            ['id' => 5, 'title' => 'Data Science', 'parent' => null, 'slug' => 'data-science', 'courses' => 67, 'icon' => 'bar_chart']
        ]);

        // Filter by search query
        if ($search = $request->input('search')) {
            $categories = $categories->filter(function ($c) use ($search) {
                return stripos($c['title'], $search) !== false || stripos($c['slug'], $search) !== false;
            });
        }

        // Apply Sorting
        $sortBy = $request->input('sort', 'title');
        $sortDir = $request->input('direction', 'asc');

        if ($sortDir === 'desc') {
            $categories = $categories->sortByDesc($sortBy);
        } else {
            $categories = $categories->sortBy($sortBy);
        }

        return view('categories.index', [
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'sort' => $sortBy,
                'direction' => $sortDir,
            ]
        ]);
    }
}
