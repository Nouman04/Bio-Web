<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

/**
 * The admin overview.
 *
 * This decides what the request asked for and what to send back;
 * DashboardService decides what the numbers are.
 */
class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        return view('dashboard.index', [
            'totals' => $this->dashboard->totals(),
            'attention' => $this->dashboard->attention($user),
            'revenue' => $this->dashboard->revenue(),
            'recent' => $this->dashboard->recent(),

            // Everything the charts draw, in one bag so the view can hand it
            // straight to Chart.js.
            'charts' => [
                'library' => $this->dashboard->libraryMix(),
                'chapters' => $this->dashboard->questionsPerChapter(),
                'papers' => $this->dashboard->papersByYear(),
                'outcomes' => $this->dashboard->quizOutcomes($user),
                'activity' => $this->dashboard->activity(),
            ],
        ]);
    }
}
