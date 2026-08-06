<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VideoController extends Controller
{
    /**
     * Display a listing of the videos.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $videos = collect([
            [
                'id' => 1,
                'title' => 'Introduction to Cell Structure',
                'meta' => '12:45 • MP4 • 1080p',
                'chapter' => 'Chapter 1: Biology Basics',
                'topic' => 'Cell Structure',
                'topic_color' => 'bg-primary/10 text-primary',
                'url' => 'https://example.com/video1',
                'date_added' => 'Oct 24, 2023',
                'date' => '2023-10-24',
                'thumbnail' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDKOhvTraqPgk8GIbwlEpUSV8vzhVmrT_stkimu5CxWu0JurXR0KfI4JmEZdQeMkiGwSfNgheRhG6Cgo3FEafS43-Hm_xoDd2yFhe_KCFdmWSgGxiRcH8nBbyCn_AnYaAcv4wMnAG6ZSlrK4SESQdtbKF04sLOxVhGc__9xMEw771R90fPCuj4cIEQ8WF6uMo0HtO3PyW3-bzoxoYCbBivgJ_3wIkMD8XcMUX2ExhVGl88UmOnrAEFN'
            ],
            [
                'id' => 2,
                'title' => 'DNA Replication Explained',
                'meta' => '18:20 • WebM • 720p',
                'chapter' => 'Chapter 3: Genetics',
                'topic' => 'DNA Structure',
                'topic_color' => 'bg-secondary/10 text-secondary',
                'url' => 'https://example.com/video2',
                'date_added' => 'Oct 22, 2023',
                'date' => '2023-10-22',
                'thumbnail' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBgb6B_7pHjHc326HAq4QDm-LY7RnFXFcW5K6nkACLhqam0hfrP8aw6bYM0U36x78jT69BIBxNV536GX4KvF_do5jEpzQ93yEfJQ922qOXMnDKZAGawhlaEDIh-UZcDH_CF15sMMW-OO4AcXw9ZYmkW1bYqcTihb89R_fOpp1gv_4LJlAirtnqxgARC1NRDOR9wfeGiUxaOwGLUS-vrbQ5U78WY56abT34rQVhTempUs5qquCZXsb0V'
            ]
        ]);

        // Filter by Date Range
        if ($dateFrom = $request->input('date_from')) {
            $videos = $videos->filter(function ($v) use ($dateFrom) {
                return $v['date'] >= $dateFrom;
            });
        }

        if ($dateTo = $request->input('date_to')) {
            $videos = $videos->filter(function ($v) use ($dateTo) {
                return $v['date'] <= $dateTo;
            });
        }

        return view('videos.index', compact('videos', 'filters'));
    }

    /**
     * Show the form for creating a new video lesson.
     */
    public function create()
    {
        return view('videos.create');
    }

    /**
     * Store a newly created video in storage.
     */
    public function store(Request $request)
    {
        // Placeholder for storing videos
        return redirect()->route('videos')->with('success', 'Video lesson added successfully.');
    }
}
