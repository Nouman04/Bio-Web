<?php

namespace App\View\Composers;

use App\Models\QuizUserAttempt;
use App\Models\SavedContent;
use App\Services\StudentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The counts on the sidebar badges.
 *
 * Kept here rather than in the partials so the views stay markup, and so the
 * queries run once per request in one place. Each is a single COUNT, and
 * nothing runs for a guest.
 */
class SidebarComposer
{
    public function __construct(private readonly StudentService $students)
    {
    }

    public function compose(View $view): void
    {
        $user = Auth::user();

        if (! $user) {
            $view->with('sidebarCounts', []);

            return;
        }

        $view->with('sidebarCounts', $user->isStaff()
            ? $this->staff($user)
            : $this->student($user));
    }

    /**
     * What is waiting on a member of staff.
     *
     * @return array<string, int>
     */
    private function staff($user): array
    {
        $awaiting = QuizUserAttempt::awaitingReview()
            ->whereIn('quiz_id', $this->students->quizIdsFor($user))
            ->count();

        return [
            'review' => $awaiting,
            // The roster badge is the same papers, counted so the reader knows
            // where to go for them.
            'students' => $awaiting,
        ];
    }

    /**
     * What is waiting on a student.
     *
     * @return array<string, int>
     */
    private function student($user): array
    {
        return [
            'quizzes' => QuizUserAttempt::where('user_id', $user->id)
                ->awaitingReview()
                ->count(),
            'resources' => SavedContent::where('user_id', $user->id)->count(),
        ];
    }
}
