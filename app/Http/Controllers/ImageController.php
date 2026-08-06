<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    /**
     * Display a listing of the images.
     */
    public function index(Request $request)
    {
        // Dummy data for presentation
        $images = collect([
            [
                'id' => 1,
                'title' => 'Mitosis Diagram V2',
                'meta' => 'PNG • 1.2 MB',
                'chapter' => 'Chapter 2: Cell Structure',
                'topic' => 'Mitosis',
                'topic_color' => 'bg-primary/10 text-primary',
                'url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDKOhvTraqPgk8GIbwlEpUSV8vzhVmrT_stkimu5CxWu0JurXR0KfI4JmEZdQeMkiGwSfNgheRhG6Cgo3FEafS43-Hm_xoDd2yFhe_KCFdmWSgGxiRcH8nBbyCn_AnYaAcv4wMnAG6ZSlrK4SESQdtbKF04sLOxVhGc__9xMEw771R90fPCuj4cIEQ8WF6uMo0HtO3PyW3-bzoxoYCbBivgJ_3wIkMD8XcMUX2ExhVGl88UmOnrAEFN',
                'date_added' => 'Oct 24, 2023',
                'has_image' => true
            ],
            [
                'id' => 2,
                'title' => 'Lab Students Hero',
                'meta' => 'JPG • 3.5 MB',
                'chapter' => 'Chapter 1: Biology Basics',
                'topic' => 'Introduction',
                'topic_color' => 'bg-secondary/10 text-secondary',
                'url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBgb6B_7pHjHc326HAq4QDm-LY7RnFXFcW5K6nkACLhqam0hfrP8aw6bYM0U36x78jT69BIBxNV536GX4KvF_do5jEpzQ93yEfJQ922qOXMnDKZAGawhlaEDIh-UZcDH_CF15sMMW-OO4AcXw9ZYmkW1bYqcTihb89R_fOpp1gv_4LJlAirtnqxgARC1NRDOR9wfeGiUxaOwGLUS-vrbQ5U78WY56abT34rQVhTempUs5qquCZXsb0V',
                'date_added' => 'Oct 22, 2023',
                'has_image' => true
            ],
            [
                'id' => 3,
                'title' => 'DNA_Helix_Animation.gif',
                'meta' => 'GIF • 850 KB',
                'chapter' => 'Chapter 3: Genetics',
                'topic' => 'DNA Structure',
                'topic_color' => 'bg-tertiary-container/20 text-tertiary',
                'url' => '',
                'date_added' => 'Oct 20, 2023',
                'has_image' => false
            ],
        ]);

        return view('images.index', compact('images'));
    }

    /**
     * Show the form for creating a new image.
     */
    public function create()
    {
        return view('images.create');
    }

    /**
     * Store a newly created image in storage.
     */
    public function store(Request $request)
    {
        // Placeholder for storing images
        return redirect()->route('images')->with('success', 'Image added successfully.');
    }
}
